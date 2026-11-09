<?php
declare(strict_types=1);

namespace App\Modules\Member2_Products;

use App\Hooks\UsePaginationState;
use App\Hooks\UseProductFilters;
use App\Hooks\UseProductSpecifications;
use App\Hooks\UseProductImageUpload;
use App\Hooks\UseProductCsrf;
use App\Hooks\UseProductValidation;
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

    public function create(): array
    {
        if (!$this->isAdmin()) return ['mode' => 'create', 'allowed' => false, 'error' => 'Chỉ quản trị viên được thêm sản phẩm.'];
        try {
            $categories = ($this->pdo ?? $this->database())->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();
            return ['mode' => 'create', 'allowed' => true, 'categories' => $categories, 'error' => $categories ? null : 'Chưa có danh mục sản phẩm.'];
        } catch (Throwable) {
            return ['mode' => 'create', 'allowed' => true, 'categories' => [], 'error' => 'Không thể tải danh mục sản phẩm. Vui lòng thử lại.'];
        }
    }

    public function adminProductAction(string $action): array
    {
        if (!$this->isAdmin()) return ['ok' => false, 'message' => 'Bạn không có quyền quản trị sản phẩm.'];
        if (!UseProductCsrf::valid($_POST['csrf_token'] ?? null)) return ['ok' => false, 'status' => 403, 'message' => 'Phiên biểu mẫu đã hết hạn. Vui lòng tải lại trang và thử lại.'];
        $pdo = $this->pdo ?? $this->database();
        $uploadedImage = null;
        try {
            if (in_array($action, ['admin_delete', 'admin_restore'], true)) {
                $id = max(0, (int) ($_POST['product_id'] ?? 0));
                $exists = $pdo->prepare('SELECT id FROM products WHERE id=?'); $exists->execute([$id]);
                if (!$exists->fetchColumn()) return ['ok' => false, 'message' => 'Không tìm thấy sản phẩm.'];
                $query = $pdo->prepare('UPDATE products SET is_active=? WHERE id=?');
                $query->execute([$action === 'admin_restore' ? 1 : 0, $id]);
                return ['ok' => true, 'message' => $action === 'admin_restore' ? 'Đã khôi phục sản phẩm vào catalog.' : 'Đã ẩn sản phẩm khỏi catalog.'];
            }
            if (!in_array($action, ['admin_create', 'admin_update'], true)) return ['ok' => false, 'message' => 'Thao tác không hợp lệ.'];
            ['values' => $values, 'errors' => $errors] = UseProductValidation::check($_POST);
            if ($errors) return ['ok' => false, 'message' => 'Vui lòng kiểm tra các trường được đánh dấu.', 'fieldErrors' => $errors];
            $name = $values['name']; $code = $values['product_code'];
            $categoryId = (int) $values['category_id'];
            $price = $values['price'];
            $capacity = null;
            $warranty = (int) $values['warranty_years'];
            $image = basename(trim((string) ($_POST['image_url'] ?? '')));
            if ($name === '' || $code === '' || $categoryId < 1) return ['ok' => false, 'message' => 'Vui lòng nhập đủ tên, mã và danh mục sản phẩm.'];
            $duplicate = $pdo->prepare('SELECT id FROM products WHERE product_code=? AND id<>? LIMIT 1');
            $duplicate->execute([$code, $action === 'admin_update' ? (int) ($_POST['product_id'] ?? 0) : 0]);
            if ($duplicate->fetchColumn()) return ['ok' => false, 'message' => 'Mã sản phẩm đã tồn tại.', 'fieldErrors' => ['product_code' => 'Mã sản phẩm đã tồn tại. Vui lòng dùng mã khác.']];
            $categoryQuery = $pdo->prepare('SELECT slug FROM categories WHERE id=?');
            $categoryQuery->execute([$categoryId]);
            $category = $categoryQuery->fetchColumn();
            if (!is_string($category)) return ['ok' => false, 'message' => 'Danh mục sản phẩm không hợp lệ.'];
            $existingSpecs = [];
            if ($action === 'admin_update') {
                $current = $pdo->prepare('SELECT technical_specs, capacity_watts, category_id, image_url FROM products WHERE id=?');
                $current->execute([(int) ($_POST['product_id'] ?? 0)]);
                $storedSpecs = $current->fetch();
                if (!$storedSpecs) return ['ok' => false, 'message' => 'Không tìm thấy sản phẩm cần cập nhật.'];
                $existingSpecs = UseProductSpecifications::decode($storedSpecs['technical_specs']);
                if (!array_key_exists('image_url', $_POST)) $image = basename((string) ($storedSpecs['image_url'] ?? ''));
            }
            $specs = UseProductSpecifications::fromInput($_POST, $existingSpecs, $category);
            $savedSpecs = UseProductSpecifications::decode($specs);
            [$capacityKey, $multiplier] = match ($category) {
                'tam-pin' => ['power_w', 1],
                'inverter' => ['rated_kw', 1000],
                'pin-luu-tru' => ['energy_kwh', 1000],
                default => [null, 1],
            };
            if ($capacityKey !== null && !array_key_exists($capacityKey, $_POST['specs']) && isset($storedSpecs) && (int) $storedSpecs['category_id'] === $categoryId) {
                $capacity = $storedSpecs['capacity_watts'];
            }
            if ($capacityKey !== null && isset($savedSpecs[$capacityKey])) {
                $watts = (float) $savedSpecs[$capacityKey] * $multiplier;
                if (!is_finite($watts) || $watts > 2147483647) return ['ok' => false, 'message' => 'Công suất / dung lượng vượt giới hạn lưu trữ.'];
                $capacity = (int) round($watts);
            }
            if ($capacityKey !== null && ($capacity === null || $capacity <= 0)) return ['ok' => false, 'message' => 'Vui lòng nhập công suất / dung lượng lớn hơn 0 cho danh mục đã chọn.'];
            if (isset($_FILES['product_image']) && ($_FILES['product_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $uploadedImage = UseProductImageUpload::store($_FILES['product_image']);
                $image = $uploadedImage;
            }
            if ($action === 'admin_create') {
                $query = $pdo->prepare('INSERT INTO products (category_id,product_code,name,capacity_watts,price,warranty_years,technical_specs,image_url,stock_quantity,is_active) VALUES (?,?,?,?,?,?,?, ?,0,1)');
                $query->execute([$categoryId, $code, $name, $capacity, $price, $warranty, $specs, $image ?: null]);
                return ['ok' => true, 'message' => 'Đã thêm sản phẩm.', 'product_id' => (int) $pdo->lastInsertId()];
            }
            $id = max(0, (int) ($_POST['product_id'] ?? 0));
            $query = $pdo->prepare('UPDATE products SET category_id=?,product_code=?,name=?,capacity_watts=?,price=?,warranty_years=?,technical_specs=?,image_url=? WHERE id=?');
            $query->execute([$categoryId, $code, $name, $capacity, $price, $warranty, $specs, $image ?: null, $id]);
            return ['ok' => true, 'message' => 'Đã cập nhật sản phẩm.'];
        } catch (\InvalidArgumentException $error) {
            return ['ok' => false, 'message' => $error->getMessage()];
        } catch (Throwable $error) {
            if ($uploadedImage !== null) UseProductImageUpload::discard($uploadedImage);
            error_log('TV2 product save: ' . $error->getMessage());
            $errorText = mb_strtolower($error->getMessage());
            if ($error instanceof \PDOException && in_array((string) $error->getCode(), ['23000', '23505'], true) && str_contains($errorText, 'product_code') && (str_contains($errorText, 'unique') || str_contains($errorText, 'duplicate'))) return ['ok' => false, 'message' => 'Mã sản phẩm đã tồn tại.', 'fieldErrors' => ['product_code' => 'Mã sản phẩm đã tồn tại.']];
            if ($error instanceof \RuntimeException && !$error instanceof \PDOException) return ['ok' => false, 'message' => $error->getMessage()];
            return ['ok' => false, 'message' => 'Không thể lưu sản phẩm. Vui lòng thử lại sau.'];
        }
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
        if (!isset($_SESSION['solar_demo_selection'])) {
            return array_values($cart);
        }
        $selected = array_map('intval', (array) $_SESSION['solar_demo_selection']);
        foreach (array_keys($cart) as $cartId) {
            if (!in_array((int) $cartId, $selected, true)) {
                $selected[] = (int) $cartId;
            }
        }
        $_SESSION['solar_demo_selection'] = $selected;
        return array_values(array_filter($cart, static fn (array $item): bool => in_array((int) $item['id'], $selected, true)));
    }

    public function handleCart(string $action): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!UseProductCsrf::valid($_POST['csrf_token'] ?? null)) return ['ok' => false, 'status' => 403, 'message' => 'Phiên thao tác đã hết hạn. Vui lòng tải lại trang.'];
        $_SESSION['solar_cart'] ??= [];
        $id = max(0, (int) ($_POST['product_id'] ?? 0));
        if ($action === 'remove') {
            unset($_SESSION['solar_cart'][$id]);
            if (isset($_SESSION['solar_demo_selection'])) $_SESSION['solar_demo_selection'] = array_values(array_diff($_SESSION['solar_demo_selection'], [$id]));
            return ['ok' => true, 'message' => 'Đã xóa thiết bị khỏi kế hoạch.'];
        }
        if ($action === 'clear') {
            $_SESSION['solar_cart'] = [];
            unset($_SESSION['solar_demo_selection']);
            return ['ok' => true, 'message' => 'Đã xóa toàn bộ sản phẩm trong kế hoạch.'];
        }
        if (in_array($action, ['demo_selected', 'update'], true)) {
            $selected = $_POST['selected'] ?? [];
            if ($action === 'demo_selected') {
                if (!is_array($selected) || !$selected) return ['ok' => false, 'message' => 'Vui lòng chọn ít nhất một thiết bị để đồng bộ sang Demo.'];
                foreach ($selected as $selectedId) {
                    if (!is_scalar($selectedId) || !ctype_digit((string) $selectedId) || !isset($_SESSION['solar_cart'][(int) $selectedId])) return ['ok' => false, 'message' => 'Danh sách thiết bị đã chọn không hợp lệ. Vui lòng tải lại kế hoạch.'];
                }
            }
            $quantities = $_POST['quantity'] ?? null;
            if (!is_array($quantities)) return ['ok' => false, 'message' => 'Vui lòng nhập số lượng thiết bị.'];
            $updated = $_SESSION['solar_cart'];
            foreach ($quantities as $productId => $quantity) {
                if (!ctype_digit((string) $productId) || !isset($updated[(int) $productId]) || !is_scalar($quantity) || !preg_match('/^[1-9]\d?$/D', (string) $quantity)) return ['ok' => false, 'message' => 'Số lượng của mỗi thiết bị phải là số nguyên từ 1 đến 99.'];
                $updated[(int) $productId]['quantity'] = (int) $quantity;
            }
            if ($action === 'demo_selected') {
                foreach ($selected as $selectedId) if (!array_key_exists((int) $selectedId, $quantities)) return ['ok' => false, 'message' => 'Thiếu số lượng của thiết bị đã chọn.'];
            }
            $_SESSION['solar_cart'] = $updated;
            if ($action === 'demo_selected') $_SESSION['solar_demo_selection'] = array_values(array_unique(array_map('intval', $selected)));
            return ['ok' => true, 'message' => $action === 'update' ? 'Đã cập nhật số lượng thiết bị.' : 'Đã đồng bộ thiết bị và số lượng mới sang Demo.'];
        }
        if ($action !== 'add' || $id < 1) return ['ok' => false, 'message' => 'Thao tác không hợp lệ.'];
        $product = $this->findProduct($id);
        if (!$product) return ['ok' => false, 'message' => 'Sản phẩm không còn khả dụng.'];
        if ((int) ($_SESSION['solar_cart'][$id]['quantity'] ?? 0) >= 99) return ['ok' => false, 'message' => 'Mỗi loại thiết bị được chọn tối đa 99 món.'];
        $_SESSION['solar_cart'][$id] = [
            'id' => (int) $product['id'],
            'product_code' => $product['product_code'],
            'name' => $product['name'],
            'category_id' => (int) ($product['category_id'] ?? 0),
            'category_name' => $product['category_name'],
            'category_slug' => $product['category_slug'] ?? '',
            'capacity_watts' => $product['capacity_watts'],
            'price' => (float) $product['price'],
            'warranty_years' => (int) ($product['warranty_years'] ?? 5),
            'technical_specs' => $product['technical_specs'] ?? '{}',
            'image_url' => $product['image_url'],
            'quantity' => (int) ($_SESSION['solar_cart'][$id]['quantity'] ?? 0) + 1,
        ];
        if (isset($_SESSION['solar_demo_selection']) && !in_array($id, $_SESSION['solar_demo_selection'], true)) {
            $_SESSION['solar_demo_selection'][] = $id;
        }
        return ['ok' => true, 'message' => 'Đã thêm thiết bị vào kế hoạch.', 'count' => self::cartCount()];
    }

    private function findProduct(int $id): ?array
    {
        try {
            $query = ($this->pdo ?? $this->database())->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.is_active=1');
            $query->execute([$id]); return $query->fetch() ?: null;
        } catch (Throwable) { return null; }
    }

    public function index(): array
    {
        ['filters' => $filters, 'filterErrors' => $filterErrors] = UseProductFilters::read($_GET);
        $page = max(1, (int) UseProductFilters::text($_GET['p'] ?? 1));
        try {
            $pdo = $this->pdo ?? $this->database();
            $categories = $pdo->query('SELECT id, name, slug, description FROM categories ORDER BY name')->fetchAll();
            $brandRows = $pdo->query("SELECT c.slug AS category_slug, JSON_UNQUOTE(JSON_EXTRACT(p.technical_specs, '$.brand')) AS brand FROM products p JOIN categories c ON c.id=p.category_id WHERE p.is_active=1")->fetchAll();
            $brandMap = self::brandMap($brandRows);
            $brands = $brandMap[$filters['category']] ?? [];
            $systemTypes = $pdo->query("SELECT DISTINCT JSON_UNQUOTE(JSON_EXTRACT(p.technical_specs, '$.type')) AS value FROM products p JOIN categories c ON c.id=p.category_id WHERE p.is_active=1 AND c.slug='inverter' AND JSON_UNQUOTE(JSON_EXTRACT(p.technical_specs, '$.type')) IS NOT NULL ORDER BY value")->fetchAll(PDO::FETCH_COLUMN);
            $filterOptions = ['brands' => $brands, 'brands_by_category' => $brandMap, 'system_types' => $systemTypes];
            $where = ['p.is_active = 1']; $params = [];
            if ($filterErrors) $where[] = '1 = 0';
            if ($filters['q'] !== '') {
                $where[] = '(p.name LIKE :search_name OR p.product_code LIKE :search_code)';
                $searchValue = '%' . $filters['q'] . '%';
                $params['search_name'] = $searchValue;
                $params['search_code'] = $searchValue;
            }
            if ($filters['category'] !== '') {
                $catSlug = $filters['category'];
                // Ánh xạ mềm giữa 7 nhóm thiết bị ở Kế hoạch (cart) và 4 danh mục chính trong CSDL
                if ($catSlug === 'tam-pin' || $catSlug === 'tam-pin-mat-troi') {
                    $where[] = "(c.slug = 'tam-pin-mat-troi' OR c.slug = 'tam-pin')";
                } elseif ($catSlug === 'inverter' || $catSlug === 'bien-tan-inverter') {
                    $where[] = "(c.slug = 'bien-tan-inverter' OR c.slug = 'inverter')";
                } elseif ($catSlug === 'pin-luu-tru' || $catSlug === 'pin-luu-tru-lithium') {
                    $where[] = "(c.slug = 'pin-luu-tru-lithium' OR c.slug = 'pin-luu-tru')";
                } elseif ($catSlug === 'tu-dien') {
                    $where[] = "(c.slug = 'tu-dien' OR (c.slug = 'phu-kien-lap-dat' AND (p.name LIKE '%tủ điện%' OR p.name LIKE '%đóng cắt%' OR p.product_code LIKE '%PRO%')))";
                } elseif ($catSlug === 'khung-gia') {
                    $where[] = "(c.slug = 'khung-gia' OR (c.slug = 'phu-kien-lap-dat' AND (p.name LIKE '%rail%' OR p.name LIKE '%kẹp%' OR p.name LIKE '%khung%' OR p.product_code LIKE '%MTG%')))";
                } elseif ($catSlug === 'day-cap') {
                    $where[] = "(c.slug = 'day-cap' OR (c.slug = 'phu-kien-lap-dat' AND (p.name LIKE '%cáp%' OR p.name LIKE '%mc4%' OR p.name LIKE '%dây%' OR p.product_code LIKE '%CAB%')))";
                } elseif ($catSlug === 'dong-ho') {
                    $where[] = "(c.slug = 'dong-ho' OR (c.slug = 'phu-kien-lap-dat' AND (p.name LIKE '%đồng hồ%' OR p.name LIKE '%meter%' OR p.name LIKE '%công tơ%' OR p.product_code LIKE '%MET%')))";
                } else {
                    $where[] = 'c.slug = :category';
                    $params['category'] = $catSlug;
                }
            }
            if ($filters['brand'] !== '') { $where[] = "JSON_UNQUOTE(JSON_EXTRACT(p.technical_specs, '$.brand')) = :brand"; $params['brand'] = $filters['brand']; }
            if ($filters['system_type'] !== '') { $where[] = "JSON_UNQUOTE(JSON_EXTRACT(p.technical_specs, '$.type')) = :system_type"; $params['system_type'] = $filters['system_type']; }
            if ($filters['min_price'] !== null) { $where[] = 'p.price >= :min_price'; $params['min_price'] = $filters['min_price']; }
            if ($filters['max_price'] !== null) { $where[] = 'p.price <= :max_price'; $params['max_price'] = $filters['max_price']; }
            if ($filters['min_capacity'] !== null) { $where[] = 'p.capacity_watts >= :min_capacity'; $params['min_capacity'] = $filters['min_capacity']; }
            if ($filters['max_capacity'] !== null) { $where[] = 'p.capacity_watts <= :max_capacity'; $params['max_capacity'] = $filters['max_capacity']; }
            foreach (['min_power_kw' => '>=', 'max_power_kw' => '<='] as $key => $operator) {
                if ($filters[$key] !== null) { $where[] = "p.capacity_watts {$operator} :{$key}"; $params[$key] = $filters[$key] * 1000; }
            }
            foreach (['min_energy' => '>=', 'max_energy' => '<='] as $key => $operator) {
                if ($filters[$key] !== null) {
                    $where[] = "CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(p.technical_specs, '$.energy_kwh')), 'null') AS DECIMAL(15,4)) {$operator} :{$key}";
                    $params[$key] = $filters[$key];
                }
            }
            if ($filters['min_warranty'] !== null) { $where[] = 'p.warranty_years >= :min_warranty'; $params['min_warranty'] = $filters['min_warranty']; }
            $condition = implode(' AND ', $where);
            $count = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE {$condition}"); $count->execute($params);
            $pager = UsePaginationState::make((int) $count->fetchColumn(), $page, 12);
            $order = UseProductFilters::ORDERS[$filters['sort']];
            $query = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE {$condition} ORDER BY {$order} LIMIT :limit OFFSET :offset");
            foreach ($params as $key => $value) { $query->bindValue(':' . $key, $value); }
            $query->bindValue(':limit', $pager['limit'], PDO::PARAM_INT); $query->bindValue(':offset', $pager['offset'], PDO::PARAM_INT); $query->execute();
            $products = $query->fetchAll(); $error = null; $usingFallback = false;
        } catch (Throwable) {
            $categories = self::fallbackCategories(); $fallback = self::fallbackProducts(); $products = ($filterErrors ? [] : UseProductFilters::filterProducts($fallback, $filters)); $pager = UsePaginationState::make(count($products), $page, 12); $products = array_slice($products, $pager['offset'], $pager['limit']); $filterOptions = ['brands' => array_values(array_unique(array_filter(array_map(static fn (array $item): ?string => json_decode((string) $item['technical_specs'], true)['brand'] ?? null, $fallback)))), 'system_types' => array_values(array_unique(array_filter(array_map(static fn (array $item): ?string => json_decode((string) $item['technical_specs'], true)['type'] ?? null, $fallback))))];
            $error = 'Chưa kết nối được CSDL sản phẩm; đang hiển thị dữ liệu minh họa.'; $usingFallback = true;
            $brandMap = self::brandMap(array_map(static fn (array $item): array => ['category_slug' => $item['category_slug'], 'brand' => UseProductSpecifications::decode($item['technical_specs'])['brand'] ?? null], $fallback));
            $filterOptions['brands_by_category'] = $brandMap;
            $filterOptions['brands'] = $brandMap[$filters['category']] ?? [];
        }
        return compact('categories', 'products', 'filters', 'pager', 'filterOptions', 'error', 'usingFallback', 'filterErrors');
    }

    public function detail(int $id): array
    {
        if ($id < 1) return ['product' => null, 'error' => 'Sản phẩm không hợp lệ.'];
        try { $pdo = $this->pdo ?? $this->database(); $categories = $pdo->query('SELECT id,name,slug FROM categories ORDER BY name')->fetchAll(); $active = $this->isAdmin() ? '' : ' AND p.is_active=1'; $query = $pdo->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = :id' . $active); $query->execute(['id' => $id]); return ['product' => $query->fetch() ?: null, 'categories' => $categories, 'error' => null]; }
        catch (Throwable) { $product = self::fallbackProducts()[$id - 1] ?? null; return ['product' => $product, 'categories' => self::fallbackCategories(), 'error' => $product ? 'Dữ liệu minh họa.' : 'Không tìm thấy sản phẩm.']; }
    }

    private static function brandMap(array $rows): array
    {
        $map = ['' => []];
        foreach ($rows as $row) {
            $brand = trim((string) ($row['brand'] ?? ''));
            if ($brand === '' || $brand === 'null') continue;
            $map[''][] = $brand; $map[$row['category_slug']][] = $brand;
        }
        foreach ($map as &$brands) { $brands = array_values(array_unique($brands)); sort($brands, SORT_NATURAL | SORT_FLAG_CASE); }
        return $map;
    }

    public function manage(): array
    {
        $base = ['mode' => 'manage', 'allowed' => $this->isAdmin(), 'products' => [], 'categories' => [], 'counts' => ['total' => 0, 'active' => 0, 'hidden' => 0]];
        if (!$base['allowed']) return $base + ['error' => 'Chỉ quản trị viên được quản lý sản phẩm.'];
        $filters = ['q' => UseProductFilters::text($_GET['q'] ?? ''), 'category' => UseProductFilters::text($_GET['category'] ?? ''), 'visibility' => UseProductFilters::text($_GET['visibility'] ?? 'all')];
        if (!in_array($filters['visibility'], ['all', 'active', 'hidden'], true)) $filters['visibility'] = 'all';
        try {
            $pdo = $this->pdo ?? $this->database();
            $base['categories'] = $pdo->query('SELECT id,name,slug FROM categories ORDER BY name')->fetchAll();
            $total = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
            $active = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();
            $base['counts'] = ['total' => $total, 'active' => $active, 'hidden' => $total - $active];
            $where = ['1=1']; $params = [];
            if ($filters['q'] !== '') { $where[] = '(p.name LIKE ? OR p.product_code LIKE ?)'; $params[] = '%' . $filters['q'] . '%'; $params[] = '%' . $filters['q'] . '%'; }
            if ($filters['category'] !== '') { $where[] = 'c.slug=?'; $params[] = $filters['category']; }
            if ($filters['visibility'] !== 'all') { $where[] = 'p.is_active=?'; $params[] = $filters['visibility'] === 'active' ? 1 : 0; }
            $from = ' FROM products p JOIN categories c ON c.id=p.category_id WHERE ' . implode(' AND ', $where);
            $count = $pdo->prepare('SELECT COUNT(*)' . $from); $count->execute($params);
            $pager = UsePaginationState::make((int) $count->fetchColumn(), (int) UseProductFilters::text($_GET['p'] ?? 1), 15);
            $query = $pdo->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug' . $from . ' ORDER BY p.id DESC LIMIT ? OFFSET ?');
            foreach ($params as $index => $value) $query->bindValue($index + 1, $value);
            $query->bindValue(count($params) + 1, $pager['limit'], PDO::PARAM_INT); $query->bindValue(count($params) + 2, $pager['offset'], PDO::PARAM_INT); $query->execute();
            $base['products'] = $query->fetchAll();
            return $base + compact('filters', 'pager') + ['error' => null];
        } catch (Throwable) { return $base + ['filters' => $filters, 'error' => 'Không thể tải danh sách quản trị. Vui lòng thử lại sau.']; }
    }

    public function compare(): array
    {
        $base = ['mode' => 'compare', 'products' => [], 'error' => null];
        $raw = $_GET['ids'] ?? [];
        if (!is_array($raw) || count($raw) < 2 || count($raw) > 3) return array_replace($base, ['error' => 'Chọn từ 2 đến 3 sản phẩm cùng danh mục để so sánh.']);
        foreach ($raw as $id) if (!is_scalar($id) || !ctype_digit((string) $id) || (int) $id < 1) return array_replace($base, ['error' => 'Danh sách sản phẩm không hợp lệ.']);
        $ids = array_values(array_unique(array_map('intval', $raw)));
        if (count($ids) < 2) return array_replace($base, ['error' => 'Vui lòng chọn các sản phẩm khác nhau.']);
        try {
            $query = ($this->pdo ?? $this->database())->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id=p.category_id WHERE p.is_active=1 AND p.id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
            $query->execute($ids); $products = $query->fetchAll();
            if (count($products) !== count($ids)) return array_replace($base, ['error' => 'Một sản phẩm đã được ẩn hoặc không còn tồn tại. Vui lòng chọn lại.']);
            if (count(array_unique(array_column($products, 'category_id'))) !== 1) return array_replace($base, ['error' => 'Chỉ so sánh các sản phẩm thuộc cùng một danh mục.']);
            usort($products, static fn (array $a, array $b): int => array_search((int) $a['id'], $ids, true) <=> array_search((int) $b['id'], $ids, true));
            return array_replace($base, ['products' => $products]);
        } catch (Throwable) { return array_replace($base, ['error' => 'Không thể tải sản phẩm so sánh. Vui lòng thử lại sau.']); }
    }

    public function cartDisplayItems(array $items): array
    {
        if (!$items) return [];
        try {
            $ids = array_map(static fn (array $item): int => (int) $item['id'], $items);
            $query = ($this->pdo ?? $this->database())->prepare('SELECT id,technical_specs FROM products WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
            $query->execute($ids); $metadata = $query->fetchAll(PDO::FETCH_KEY_PAIR);
            foreach ($items as &$item) {
                if (!isset($item['technical_specs'])) $item['technical_specs'] = ['pricing_unit' => UseProductSpecifications::decode($metadata[$item['id']] ?? null)['pricing_unit'] ?? ''];
            }
        } catch (Throwable) {}
        return $items;
    }

    private function database(): PDO { require_once dirname(__DIR__, 2) . '/Config/Database.php'; return Database::connection(); }
    private static function fallbackCategories(): array { return [['id'=>1,'name'=>'Tấm pin năng lượng mặt trời','slug'=>'tam-pin','description'=>'Chuyển hóa bức xạ mặt trời thành điện DC.'],['id'=>2,'name'=>'Bộ biến tần Inverter','slug'=>'inverter','description'=>'Chuyển đổi điện DC thành AC ổn định.'],['id'=>3,'name'=>'Tủ điện và thiết bị bảo vệ','slug'=>'tu-dien','description'=>'Bảo vệ hệ thống khỏi quá tải và sét lan truyền.'],['id'=>4,'name'=>'Hệ thống khung giá','slug'=>'khung-gia','description'=>'Khung nhôm, kẹp pin và phụ kiện lắp đặt.'],['id'=>5,'name'=>'Dây cáp điện và phụ kiện','slug'=>'day-cap','description'=>'Cáp DC, đầu nối MC4 và phụ kiện đấu nối.'],['id'=>6,'name'=>'Đồng hồ đo điện năng','slug'=>'dong-ho','description'=>'Đo điện hai chiều cho hệ thống hòa lưới.'],['id'=>7,'name'=>'Pin lưu trữ / Ắc quy Solar','slug'=>'pin-luu-tru','description'=>'Lưu điện cho hệ độc lập và hệ Hybrid.']]; }
    private static function fallbackProducts(): array { $items = [['name'=>'Solar Panel Mono 550W Tier-1','category_name'=>'Tấm pin năng lượng mặt trời','category_slug'=>'tam-pin','capacity_watts'=>550,'price'=>2850000,'warranty_years'=>12,'product_code'=>'SP-550-MONO','technical_specs'=>'{"efficiency":"21.3%"}'],['name'=>'Hybrid Inverter 5kW IP65','category_name'=>'Bộ biến tần Inverter','category_slug'=>'inverter','capacity_watts'=>5000,'price'=>24500000,'warranty_years'=>5,'product_code'=>'INV-HYB-5K','technical_specs'=>'{"phase":"1 pha"}'],['name'=>'Tủ điện bảo vệ AC/DC Solar','category_name'=>'Tủ điện và thiết bị bảo vệ','category_slug'=>'tu-dien','capacity_watts'=>null,'price'=>3200000,'warranty_years'=>2,'product_code'=>'BOX-ACDC-01','technical_specs'=>'{"protection":"MCB + SPD"}'],['name'=>'Bộ khung nhôm mái tôn 5kWp','category_name'=>'Hệ thống khung giá','category_slug'=>'khung-gia','capacity_watts'=>null,'price'=>7800000,'warranty_years'=>10,'product_code'=>'MNT-ROOF-5K','technical_specs'=>'{"material":"Aluminium"}'],['name'=>'Cáp DC Solar 6mm² + MC4','category_name'=>'Dây cáp điện và phụ kiện','category_slug'=>'day-cap','capacity_watts'=>null,'price'=>1850000,'warranty_years'=>2,'product_code'=>'CAB-DC-6MM','technical_specs'=>'{"length":"100m"}'],['name'=>'Công tơ điện hai chiều 3 pha','category_name'=>'Đồng hồ đo điện năng','category_slug'=>'dong-ho','capacity_watts'=>null,'price'=>4600000,'warranty_years'=>3,'product_code'=>'METER-3P-2W','technical_specs'=>'{"communication":"RS485"}'],['name'=>'Pin lưu trữ LiFePO4 10kWh','category_name'=>'Pin lưu trữ / Ắc quy Solar','category_slug'=>'pin-luu-tru','capacity_watts'=>10000,'price'=>52000000,'warranty_years'=>10,'product_code'=>'BAT-LFP-10K','technical_specs'=>'{"cycles":6000,"energy_kwh":10}']]; foreach ($items as $index => &$item) { $item['id']=$index+1; $item['image_url']=null; $item['stock_quantity']=10; $item['is_active']=1; } return $items; }
}
