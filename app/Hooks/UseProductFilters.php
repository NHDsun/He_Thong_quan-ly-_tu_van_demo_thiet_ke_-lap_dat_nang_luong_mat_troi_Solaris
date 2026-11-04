<?php
declare(strict_types=1);

namespace App\Hooks;

final class UseProductFilters
{
    public const PRICE_STEPS = [
        '' => 100000,
        'tam-pin' => 100000,
        'inverter' => 500000,
        'pin-luu-tru' => 1000000,
        'tu-dien' => 50000,
        'khung-gia' => 10000,
        'day-cap' => 1000,
        'dong-ho' => 50000,
    ];

    public static function priceStep(string $category): int
    {
        return self::PRICE_STEPS[$category] ?? self::PRICE_STEPS[''];
    }

    public const SORTS = [
        'newest' => 'Mới nhất',
        'price_asc' => 'Giá tăng dần',
        'price_desc' => 'Giá giảm dần',
        'warranty_desc' => 'Bảo hành dài nhất',
    ];
    public const ORDERS = [
        'newest' => 'p.created_at DESC, p.id DESC',
        'price_asc' => 'p.price ASC, p.id DESC',
        'price_desc' => 'p.price DESC, p.id DESC',
        'warranty_desc' => 'p.warranty_years DESC, p.id DESC',
    ];
    public const RANGES = [
        'tam-pin' => ['min_capacity', 'max_capacity', 'Công suất', 'W'],
        'inverter' => ['min_power_kw', 'max_power_kw', 'Công suất', 'kW'],
        'pin-luu-tru' => ['min_energy', 'max_energy', 'Dung lượng', 'kWh'],
    ];
    public const NUMBERS = ['min_price', 'max_price', 'min_capacity', 'max_capacity', 'min_power_kw', 'max_power_kw', 'min_energy', 'max_energy', 'min_warranty'];

    public static function text(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    public static function read(array $input): array
    {
        $filters = [];
        foreach (['q', 'category', 'brand', 'system_type'] as $key) $filters[$key] = self::text($input[$key] ?? '');
        $sort = self::text($input['sort'] ?? 'newest');
        $filters['sort'] = isset(self::SORTS[$sort]) ? $sort : 'newest';
        $activeRange = self::RANGES[$filters['category']] ?? [];
        $errors = [];
        foreach (self::NUMBERS as $key) {
            $value = self::text($input[$key] ?? '');
            $enabled = in_array($key, ['min_price', 'max_price', 'min_warranty'], true) || in_array($key, array_slice($activeRange, 0, 2), true);
            $valid = is_numeric($value) && is_finite((float) $value) && (float) $value >= 0;
            $filters[$key] = $enabled && $valid ? (float) $value : null;
            if ($enabled && $value !== '' && !$valid) $errors[] = 'Giá, công suất, dung lượng và bảo hành phải là số không âm.';
        }
        if ($filters['category'] !== 'inverter') $filters['system_type'] = '';
        foreach ([['min_price', 'max_price', 'giá'], ['min_capacity', 'max_capacity', 'công suất'], ['min_power_kw', 'max_power_kw', 'công suất'], ['min_energy', 'max_energy', 'dung lượng']] as [$min, $max, $label]) {
            if ($filters[$min] !== null && $filters[$max] !== null && $filters[$min] > $filters[$max]) $errors[] = 'Khoảng ' . $label . ': giá trị từ phải nhỏ hơn hoặc bằng giá trị đến.';
        }
        return ['filters' => $filters, 'filterErrors' => array_values(array_unique($errors))];
    }

    public static function url(array $filters, array $changes = []): string
    {
        $values = array_merge($filters, $changes);
        $state = self::read($values)['filters'];
        $state['p'] = max(1, (int) self::text($values['p'] ?? 1));
        if (isset($values['product'])) $state['product'] = max(0, (int) self::text($values['product']));
        return 'index.php?' . http_build_query(['page' => 'products'] + array_filter($state, static fn ($value): bool => $value !== null && $value !== ''));
    }

    public static function capacityLabel(array $product): string
    {
        $specs = UseProductSpecifications::decode($product['technical_specs'] ?? null);
        $category = $product['category_slug'] ?? '';
        $number = static fn (float $value): string => rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
        if ($category === 'pin-luu-tru') {
            $energy = $specs['energy_kwh'] ?? null;
            return is_numeric($energy) ? $number((float) $energy) . ' kWh' : 'Dung lượng đang cập nhật';
        }
        if ($category === 'inverter') return !empty($product['capacity_watts']) ? $number((float) $product['capacity_watts'] / 1000) . ' kW' : 'Công suất đang cập nhật';
        if ($category === 'tam-pin') return !empty($product['capacity_watts']) ? $number((float) $product['capacity_watts']) . ' W' : 'Công suất đang cập nhật';
        return 'Thiết bị phụ trợ';
    }

    public static function filterProducts(array $products, array $filters): array
    {
        $products = array_values(array_filter($products, static function (array $product) use ($filters): bool {
            $needle = mb_strtolower($filters['q']);
            $specs = UseProductSpecifications::decode($product['technical_specs'] ?? null);
            if ($needle !== '' && !str_contains(mb_strtolower($product['name']), $needle) && !str_contains(mb_strtolower($product['product_code']), $needle)) return false;
            if ($filters['category'] !== '' && $product['category_slug'] !== $filters['category']) return false;
            foreach (['brand' => 'brand', 'system_type' => 'type'] as $key => $spec) if ($filters[$key] !== '' && ($specs[$spec] ?? '') !== $filters[$key]) return false;
            foreach (['min_price' => [$product['price'], true], 'max_price' => [$product['price'], false], 'min_warranty' => [$product['warranty_years'], true], 'min_capacity' => [$product['capacity_watts'], true], 'max_capacity' => [$product['capacity_watts'], false], 'min_power_kw' => [isset($product['capacity_watts']) ? $product['capacity_watts'] / 1000 : null, true], 'max_power_kw' => [isset($product['capacity_watts']) ? $product['capacity_watts'] / 1000 : null, false], 'min_energy' => [$specs['energy_kwh'] ?? null, true], 'max_energy' => [$specs['energy_kwh'] ?? null, false]] as $key => [$value, $minimum]) {
                if ($filters[$key] !== null && (!is_numeric($value) || ($minimum ? $value < $filters[$key] : $value > $filters[$key]))) return false;
            }
            return true;
        }));
        usort($products, static function (array $a, array $b) use ($filters): int {
            $order = match ($filters['sort']) {
                'price_asc' => $a['price'] <=> $b['price'],
                'price_desc' => $b['price'] <=> $a['price'],
                'warranty_desc' => $b['warranty_years'] <=> $a['warranty_years'],
                default => ($b['created_at'] ?? '') <=> ($a['created_at'] ?? ''),
            };
            return $order ?: $b['id'] <=> $a['id'];
        });
        return $products;
    }
}
