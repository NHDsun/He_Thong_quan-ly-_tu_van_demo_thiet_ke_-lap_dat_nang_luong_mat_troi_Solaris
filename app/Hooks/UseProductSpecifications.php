<?php
declare(strict_types=1);

namespace App\Hooks;

use InvalidArgumentException;

final class UseProductSpecifications
{
    public const METADATA = [
        'demo_data' => ['label' => 'Nguồn dữ liệu', 'options' => ['' => 'Chưa xác định', '1' => 'Dữ liệu demo / minh họa', '0' => 'Dữ liệu sản phẩm thực tế']],
        'price_type' => ['label' => 'Loại giá', 'options' => ['' => 'Chưa xác định', 'gia_mo_phong_vnd' => 'Giá mô phỏng (VNĐ)', 'gia_tham_khao_vnd' => 'Giá tham khảo (VNĐ)', 'gia_niem_yet_vnd' => 'Giá niêm yết (VNĐ)', 'lien_he_bao_gia' => 'Liên hệ báo giá']],
        'image_type' => ['label' => 'Loại hình ảnh', 'options' => ['' => 'Chưa xác định', 'svg_minh_hoa' => 'Ảnh SVG minh họa', 'anh_minh_hoa' => 'Ảnh minh họa', 'anh_san_pham_thuc_te' => 'Ảnh sản phẩm thực tế']],
    ];

    public static function fields(): array
    {
        return [
            'brand' => ['label' => 'Thương hiệu (Brand)', 'placeholder' => 'Ví dụ: LONGi, Solis, Deye'],
            'type' => ['label' => 'Loại thiết bị / hệ thống', 'categories' => 'inverter tu-dien', 'placeholder' => 'Hybrid / On-grid / Off-grid hoặc loại thiết bị bảo vệ'],
            'cell_type' => ['label' => 'Công nghệ tế bào pin', 'categories' => 'tam-pin', 'placeholder' => 'Ví dụ: Mono N-type'],
            'power_w' => ['label' => 'Công suất tấm pin (W)', 'categories' => 'tam-pin', 'numeric' => true],
            'rated_kw' => ['label' => 'Công suất định mức (kW)', 'categories' => 'inverter', 'numeric' => true],
            'phase' => ['label' => 'Số pha', 'categories' => 'inverter dong-ho', 'placeholder' => 'Ví dụ: 1 pha, 3 pha'],
            'battery_type' => ['label' => 'Công nghệ pin lưu trữ', 'categories' => 'pin-luu-tru', 'placeholder' => 'Ví dụ: LiFePO4'],
            'energy_kwh' => ['label' => 'Dung lượng lưu trữ (kWh)', 'categories' => 'pin-luu-tru', 'numeric' => true],
            'cycles' => ['label' => 'Số chu kỳ sạc', 'categories' => 'pin-luu-tru', 'numeric' => true],
            'specification' => ['label' => 'Quy cách thiết bị', 'categories' => 'tu-dien khung-gia day-cap dong-ho'],
            'dimension' => ['label' => 'Kích thước', 'categories' => 'tu-dien khung-gia'],
            'measure' => ['label' => 'Số đo / quy cách số lượng', 'categories' => 'khung-gia day-cap', 'numeric' => true],
            'unit' => ['label' => 'Đơn vị thông số', 'categories' => 'khung-gia day-cap', 'placeholder' => 'Ví dụ: m, bộ, mm²'],
            'pricing_unit' => ['label' => 'Đơn vị tính giá', 'placeholder' => 'Ví dụ: tấm, bộ, mét'],
            'rated' => ['label' => 'Thông số định mức', 'categories' => 'dong-ho tu-dien'],
            'material' => ['label' => 'Vật liệu', 'categories' => 'khung-gia day-cap'],
            'length' => ['label' => 'Chiều dài', 'categories' => 'khung-gia day-cap', 'placeholder' => 'Ví dụ: 100 m'],
            'protection' => ['label' => 'Thiết bị bảo vệ', 'categories' => 'tu-dien'],
            'communication' => ['label' => 'Giao tiếp kết nối', 'categories' => 'inverter dong-ho pin-luu-tru'],
            'efficiency' => ['label' => 'Hiệu suất', 'categories' => 'tam-pin inverter', 'placeholder' => 'Ví dụ: 21.3%'],
        ];
    }

    public static function decode(mixed $value): array
    {
        if (is_array($value)) return $value;
        $decoded = json_decode((string) ($value ?? '{}'), true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function appliesTo(array $field, string $category): bool
    {
        return empty($field['categories']) || in_array($category, explode(' ', $field['categories']), true);
    }

    public static function fromInput(array $input, array $existing = [], ?string $category = null): string
    {
        $specs = $existing;
        $fields = self::fields();
        if (!is_array($input['specs'] ?? null) || !is_array($input['spec_metadata'] ?? null)) {
            throw new InvalidArgumentException('Vui lòng nhập thông số bằng các trường trong biểu mẫu.');
        }
        foreach ($input['specs'] as $key => $raw) {
            if (isset(self::METADATA[$key]) || (!isset($fields[$key]) && !array_key_exists($key, $existing))) continue;
            if ($category !== null && isset($fields[$key]) && !self::appliesTo($fields[$key], $category)) continue;
            if (!is_scalar($raw) || (isset($existing[$key]) && is_array($existing[$key]))) {
                throw new InvalidArgumentException('Giá trị thông số không hợp lệ.');
            }
            $value = trim((string) $raw);
            if ($value === '') { unset($specs[$key]); continue; }
            if (mb_strlen($value) > 1000) throw new InvalidArgumentException('Thông số không được dài quá 1.000 ký tự.');
            if (!empty($fields[$key]['numeric']) || is_int($existing[$key] ?? null) || is_float($existing[$key] ?? null)) {
                if (!is_numeric($value) || !is_finite((float) $value) || (float) $value < 0) {
                    throw new InvalidArgumentException((self::label((string) $key)) . ' phải là số không âm.');
                }
                $value = (float) $value;
                if ($value <= PHP_INT_MAX && floor($value) === $value) $value = (int) $value;
            } elseif (is_bool($existing[$key] ?? null)) {
                if (!in_array($value, ['0', '1'], true)) throw new InvalidArgumentException('Giá trị Có / Không không hợp lệ.');
                $value = $value === '1';
            }
            $specs[$key] = $value;
        }
        foreach (self::METADATA as $key => $field) {
            if (!array_key_exists($key, $input['spec_metadata'])) continue;
            $value = $input['spec_metadata'][$key];
            if (!is_string($value) || (!array_key_exists($value, $field['options']) && $value !== (string) ($existing[$key] ?? ''))) {
                throw new InvalidArgumentException($field['label'] . ' không hợp lệ.');
            }
            if ($value === '') { unset($specs[$key]); continue; }
            $specs[$key] = $key === 'demo_data' ? $value === '1' : $value;
        }
        return json_encode((object) $specs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function label(string $key): string
    {
        return self::fields()[$key]['label'] ?? ucwords(str_replace('_', ' ', $key));
    }

    public static function displayValue(mixed $value): string
    {
        if (is_bool($value)) return $value ? 'Có' : 'Không';
        if ($value === null) return 'Chưa cập nhật';
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $key => $item) $parts[] = (is_string($key) ? self::label($key) . ': ' : '') . self::displayValue($item);
            return implode(' · ', $parts);
        }
        return (string) $value;
    }
}
