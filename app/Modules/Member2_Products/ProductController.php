<?php
declare(strict_types=1);

namespace App\Modules\Member2_Products;

use App\Hooks\UsePaginationState;
use App\Config\Database;
use PDO;
use Throwable;

final class ProductController
{
    public function __construct(private ?PDO $pdo = null) {}

    public function isAdmin(): bool
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId < 1) return false;
        try {
            $query = ($this->pdo ?? $this->database())->prepare('SELECT role FROM users WHERE id=? LIMIT 1');
            $query->execute([$userId]);
            return $query->fetchColumn() === 'admin';
        } catch (Throwable) { return false; }
    }

    public function adminProductAction(string $action): array
    {
        if (!$this->isAdmin()) return ['ok' => false, 'message' => 'Bạn không có quyền quản trị sản phẩm.'];
        $pdo = $this->pdo ?? $this->database();
        try {
            if ($action === 'admin_delete') {
                $id = max(0, (int) ($_POST['product_id'] ?? 0));
                $query = $pdo->prepare('UPDATE products SET is_active=0 WHERE id=?');
                $query->execute([$id]);
                return ['ok' => true, 'message' => 'Đã ẩn sản phẩm khỏi catalog.'];
            }
            if (!in_array($action, ['admin_create', 'admin_update'], true)) return ['ok' => false, 'message' => 'Thao tác không hợp lệ.'];
            $name = trim((string) ($_POST['name'] ?? ''));
            $code = trim((string) ($_POST['product_code'] ?? ''));
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $price = max(0, (float) ($_POST['price'] ?? 0));
            $capacity = ($_POST['capacity_watts'] ?? '') === '' ? null : max(0, (int) $_POST['capacity_watts']);
            $warranty = max(0, (int) ($_POST['warranty_years'] ?? 0));
            $image = basename(trim((string) ($_POST['image_url'] ?? '')));
            $specs = trim((string) ($_POST['technical_specs'] ?? '{}'));
            if ($name === '' || $code === '' || $categoryId < 1 || !is_array(json_decode($specs, true))) return ['ok' => false, 'message' => 'Vui lòng nhập đủ thông tin và JSON thông số hợp lệ.'];
            if ($action === 'admin_create') {
                $query = $pdo->prepare('INSERT INTO products (category_id,product_code,name,capacity_watts,price,warranty_years,technical_specs,image_url,stock_quantity,is_active) VALUES (?,?,?,?,?,?,?, ?,0,1)');
                $query->execute([$categoryId, $code, $name, $capacity, $price, $warranty, $specs, $image ?: null]);
                return ['ok' => true, 'message' => 'Đã thêm sản phẩm.'];
            }
            $id = max(0, (int) ($_POST['product_id'] ?? 0));
            $query = $pdo->prepare('UPDATE products SET category_id=?,product_code=?,name=?,capacity_watts=?,price=?,warranty_years=?,technical_specs=?,image_url=? WHERE id=?');
            $query->execute([$categoryId, $code, $name, $capacity, $price, $warranty, $specs, $image ?: null, $id]);
            return ['ok' => true, 'message' => 'Đã cập nhật sản phẩm.'];
        } catch (Throwable $error) { return ['ok' => false, 'message' => 'Không thể lưu sản phẩm: ' . $error->getMessage()]; }
    }

    public static function cartItems(): array
    {
        return $_SESSION['solar_cart'] ?? [];
    }

    public static function cartCount(): int
    {
        return array_sum(array_map(static fn (array $item): int => (int) $item['quantity'], self::cartItems()));
    }

    public static function demoItems(): array
    {
        $cart = self::cartItems();
        $selected = array_map('intval', (array) ($_SESSION['solar_demo_selection'] ?? array_keys($cart)));
        return array_values(array_filter($cart, static fn (array $item): bool => in_array((int) $item['id'], $selected, true)));
    }

    public function handleCart(string $action): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['solar_cart'] ??= [];
        $id = max(0, (int) ($_POST['product_id'] ?? 0));
        if ($action === 'remove') { unset($_SESSION['solar_cart'][$id]); return; }
        if ($action === 'clear') { $_SESSION['solar_cart'] = []; return; }
        if ($action === 'demo_selected') {
            $_SESSION['solar_demo_selection'] = array_values(array_filter(array_map('intval', (array) ($_POST['selected'] ?? []))));
            return;
        }
        if ($action === 'update') {
            foreach ((array) ($_POST['quantity'] ?? []) as $productId => $quantity) {
                $productId = (int) $productId; $quantity = max(1, min(99, (int) $quantity));
                if (isset($_SESSION['solar_cart'][$productId])) $_SESSION['solar_cart'][$productId]['quantity'] = $quantity;
            }
            return;
        }
        if ($action !== 'add' || $id < 1) return;
        $product = $this->findProduct($id);
        if (!$product) return;
        $_SESSION['solar_cart'][$id] = [
            'id' => (int) $product['id'], 'product_code' => $product['product_code'],
            'name' => $product['name'], 'category_name' => $product['category_name'],
            'capacity_watts' => $product['capacity_watts'], 'price' => (float) $product['price'],
            'image_url' => $product['image_url'], 'quantity' => (int) ($_SESSION['solar_cart'][$id]['quantity'] ?? 0) + 1,
        ];
    }

    private function findProduct(int $id): ?array
    {
        try {
            $query = ($this->pdo ?? $this->database())->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.is_active=1');
            $query->execute([$id]); return $query->fetch() ?: null;
        } catch (Throwable) { return null; }
    }

    public function index(): array
    {
        $filters = [
            'search' => trim((string) ($_GET['q'] ?? '')),
            'category' => trim((string) ($_GET['category'] ?? '')),
            'brand' => trim((string) ($_GET['brand'] ?? '')),
            'system_type' => trim((string) ($_GET['system_type'] ?? '')),
            'min_price' => $this->positiveNumber($_GET['min_price'] ?? null),
            'max_price' => $this->positiveNumber($_GET['max_price'] ?? null),
            'min_capacity' => $this->positiveNumber($_GET['min_capacity'] ?? null),
            'max_capacity' => $this->positiveNumber($_GET['max_capacity'] ?? null),
            'min_warranty' => $this->positiveNumber($_GET['min_warranty'] ?? null),
        ];
        $page = max(1, (int) ($_GET['p'] ?? 1));
        try {
            $pdo = $this->pdo ?? $this->database();
            $categories = $pdo->query('SELECT id, name, slug, description FROM categories ORDER BY name')->fetchAll();
            $brands = $pdo->query("SELECT DISTINCT JSON_UNQUOTE(JSON_EXTRACT(technical_specs, '$.brand')) AS value FROM products WHERE is_active=1 AND JSON_UNQUOTE(JSON_EXTRACT(technical_specs, '$.brand')) IS NOT NULL ORDER BY value")->fetchAll(PDO::FETCH_COLUMN);
            $systemTypes = $pdo->query("SELECT DISTINCT JSON_UNQUOTE(JSON_EXTRACT(technical_specs, '$.type')) AS value FROM products WHERE is_active=1 AND JSON_UNQUOTE(JSON_EXTRACT(technical_specs, '$.type')) IS NOT NULL ORDER BY value")->fetchAll(PDO::FETCH_COLUMN);
            $filterOptions = ['brands' => $brands, 'system_types' => $systemTypes];
            $where = ['p.is_active = 1']; $params = [];
            if ($filters['search'] !== '') {
                $where[] = '(p.name LIKE :search_name OR p.product_code LIKE :search_code)';
                $searchValue = '%' . $filters['search'] . '%';
                $params['search_name'] = $searchValue;
                $params['search_code'] = $searchValue;
            }
            if ($filters['category'] !== '') { $where[] = 'c.slug = :category'; $params['category'] = $filters['category']; }
            if ($filters['brand'] !== '') { $where[] = "JSON_UNQUOTE(JSON_EXTRACT(p.technical_specs, '$.brand')) = :brand"; $params['brand'] = $filters['brand']; }
            if ($filters['system_type'] !== '') { $where[] = "JSON_UNQUOTE(JSON_EXTRACT(p.technical_specs, '$.type')) = :system_type"; $params['system_type'] = $filters['system_type']; }
            if ($filters['min_price'] !== null) { $where[] = 'p.price >= :min_price'; $params['min_price'] = $filters['min_price']; }
            if ($filters['max_price'] !== null) { $where[] = 'p.price <= :max_price'; $params['max_price'] = $filters['max_price']; }
            if ($filters['min_capacity'] !== null) { $where[] = 'p.capacity_watts >= :min_capacity'; $params['min_capacity'] = $filters['min_capacity']; }
            if ($filters['max_capacity'] !== null) { $where[] = 'p.capacity_watts <= :max_capacity'; $params['max_capacity'] = $filters['max_capacity']; }
            if ($filters['min_warranty'] !== null) { $where[] = 'p.warranty_years >= :min_warranty'; $params['min_warranty'] = $filters['min_warranty']; }
            $condition = implode(' AND ', $where);
            $count = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE {$condition}"); $count->execute($params);
            $pager = UsePaginationState::make((int) $count->fetchColumn(), $page, 12);
            $query = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE {$condition} ORDER BY p.created_at DESC, p.id DESC LIMIT :limit OFFSET :offset");
            foreach ($params as $key => $value) { $query->bindValue(':' . $key, $value); }
            $query->bindValue(':limit', $pager['limit'], PDO::PARAM_INT); $query->bindValue(':offset', $pager['offset'], PDO::PARAM_INT); $query->execute();
            $products = $query->fetchAll(); $error = null; $usingFallback = false;
        } catch (Throwable) {
            $categories = self::fallbackCategories(); $fallback = self::fallbackProducts(); $products = self::filterFallback($fallback, $filters); $pager = UsePaginationState::make(count($products), $page, 12); $products = array_slice($products, $pager['offset'], $pager['limit']); $filterOptions = ['brands' => array_values(array_unique(array_filter(array_map(static fn (array $item): ?string => json_decode((string) $item['technical_specs'], true)['brand'] ?? null, $fallback)))), 'system_types' => array_values(array_unique(array_filter(array_map(static fn (array $item): ?string => json_decode((string) $item['technical_specs'], true)['type'] ?? null, $fallback))))];
            $error = 'Chưa kết nối được CSDL sản phẩm; đang hiển thị dữ liệu minh họa.'; $usingFallback = true;
        }
        return compact('categories', 'products', 'filters', 'pager', 'filterOptions', 'error', 'usingFallback');
    }

    public function detail(int $id): array
    {
        if ($id < 1) return ['product' => null, 'error' => 'Sản phẩm không hợp lệ.'];
        try { $pdo = $this->pdo ?? $this->database(); $categories = $pdo->query('SELECT id,name,slug FROM categories ORDER BY name')->fetchAll(); $query = $pdo->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = :id AND p.is_active = 1'); $query->execute(['id' => $id]); return ['product' => $query->fetch() ?: null, 'categories' => $categories, 'error' => null]; }
        catch (Throwable) { $product = self::fallbackProducts()[$id - 1] ?? null; return ['product' => $product, 'categories' => self::fallbackCategories(), 'error' => $product ? 'Dữ liệu minh họa.' : 'Không tìm thấy sản phẩm.']; }
    }

    private function database(): PDO { require_once dirname(__DIR__, 2) . '/Config/Database.php'; return Database::connection(); }
    private function positiveNumber(mixed $value): ?float { return is_numeric($value) && (float) $value >= 0 ? (float) $value : null; }
    private static function filterFallback(array $products, array $filters): array { return array_values(array_filter($products, static function (array $product) use ($filters): bool { $needle = mb_strtolower($filters['search']); $specs = json_decode((string) ($product['technical_specs'] ?? '{}'), true) ?: []; return ($needle === '' || str_contains(mb_strtolower($product['name']), $needle) || str_contains(mb_strtolower($product['product_code']), $needle)) && ($filters['category'] === '' || $product['category_slug'] === $filters['category']) && ($filters['brand'] === '' || ($specs['brand'] ?? '') === $filters['brand']) && ($filters['system_type'] === '' || ($specs['type'] ?? '') === $filters['system_type']) && ($filters['min_price'] === null || $product['price'] >= $filters['min_price']) && ($filters['max_price'] === null || $product['price'] <= $filters['max_price']) && ($filters['min_capacity'] === null || (float) ($product['capacity_watts'] ?? 0) >= $filters['min_capacity']) && ($filters['max_capacity'] === null || (float) ($product['capacity_watts'] ?? 0) <= $filters['max_capacity']) && ($filters['min_warranty'] === null || $product['warranty_years'] >= $filters['min_warranty']); })); }
    private static function fallbackCategories(): array { return [['id'=>1,'name'=>'Tấm pin năng lượng mặt trời','slug'=>'tam-pin','description'=>'Chuyển hóa bức xạ mặt trời thành điện DC.'],['id'=>2,'name'=>'Bộ biến tần Inverter','slug'=>'inverter','description'=>'Chuyển đổi điện DC thành AC ổn định.'],['id'=>3,'name'=>'Tủ điện và thiết bị bảo vệ','slug'=>'tu-dien','description'=>'Bảo vệ hệ thống khỏi quá tải và sét lan truyền.'],['id'=>4,'name'=>'Hệ thống khung giá','slug'=>'khung-gia','description'=>'Khung nhôm, kẹp pin và phụ kiện lắp đặt.'],['id'=>5,'name'=>'Dây cáp điện và phụ kiện','slug'=>'day-cap','description'=>'Cáp DC, đầu nối MC4 và phụ kiện đấu nối.'],['id'=>6,'name'=>'Đồng hồ đo điện năng','slug'=>'dong-ho','description'=>'Đo điện hai chiều cho hệ thống hòa lưới.'],['id'=>7,'name'=>'Pin lưu trữ / Ắc quy Solar','slug'=>'pin-luu-tru','description'=>'Lưu điện cho hệ độc lập và hệ Hybrid.']]; }
    private static function fallbackProducts(): array { $items = [['name'=>'Solar Panel Mono 550W Tier-1','category_name'=>'Tấm pin năng lượng mặt trời','category_slug'=>'tam-pin','capacity_watts'=>550,'price'=>2850000,'warranty_years'=>12,'product_code'=>'SP-550-MONO','technical_specs'=>'{"efficiency":"21.3%"}'],['name'=>'Hybrid Inverter 5kW IP65','category_name'=>'Bộ biến tần Inverter','category_slug'=>'inverter','capacity_watts'=>5000,'price'=>24500000,'warranty_years'=>5,'product_code'=>'INV-HYB-5K','technical_specs'=>'{"phase":"1 pha"}'],['name'=>'Tủ điện bảo vệ AC/DC Solar','category_name'=>'Tủ điện và thiết bị bảo vệ','category_slug'=>'tu-dien','capacity_watts'=>null,'price'=>3200000,'warranty_years'=>2,'product_code'=>'BOX-ACDC-01','technical_specs'=>'{"protection":"MCB + SPD"}'],['name'=>'Bộ khung nhôm mái tôn 5kWp','category_name'=>'Hệ thống khung giá','category_slug'=>'khung-gia','capacity_watts'=>null,'price'=>7800000,'warranty_years'=>10,'product_code'=>'MNT-ROOF-5K','technical_specs'=>'{"material":"Aluminium"}'],['name'=>'Cáp DC Solar 6mm² + MC4','category_name'=>'Dây cáp điện và phụ kiện','category_slug'=>'day-cap','capacity_watts'=>null,'price'=>1850000,'warranty_years'=>2,'product_code'=>'CAB-DC-6MM','technical_specs'=>'{"length":"100m"}'],['name'=>'Công tơ điện hai chiều 3 pha','category_name'=>'Đồng hồ đo điện năng','category_slug'=>'dong-ho','capacity_watts'=>null,'price'=>4600000,'warranty_years'=>3,'product_code'=>'METER-3P-2W','technical_specs'=>'{"communication":"RS485"}'],['name'=>'Pin lưu trữ LiFePO4 10kWh','category_name'=>'Pin lưu trữ / Ắc quy Solar','category_slug'=>'pin-luu-tru','capacity_watts'=>10000,'price'=>52000000,'warranty_years'=>10,'product_code'=>'BAT-LFP-10K','technical_specs'=>'{"cycles":6000}']]; foreach ($items as $index => &$item) { $item['id']=$index+1; $item['image_url']=null; $item['stock_quantity']=10; $item['is_active']=1; } return $items; }
}
