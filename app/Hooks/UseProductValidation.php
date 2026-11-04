<?php
declare(strict_types=1);

namespace App\Hooks;

final class UseProductValidation
{
    public static function check(array $input): array
    {
        $text = static fn (string $key): string => is_scalar($input[$key] ?? null) ? trim((string) $input[$key]) : '';
        $values = ['name' => $text('name'), 'product_code' => $text('product_code'), 'price' => $text('price'), 'warranty_years' => $text('warranty_years'), 'category_id' => $text('category_id')];
        $errors = [];
        foreach (['name' => ['Tên sản phẩm', 255], 'product_code' => ['Mã sản phẩm', 50]] as $key => [$label, $limit]) {
            if ($values[$key] === '' || mb_strlen($values[$key]) > $limit) $errors[$key] = $label . ' phải có từ 1 đến ' . $limit . ' ký tự.';
        }
        if (!preg_match('/^\d+(\.\d{1,2})?$/D', $values['price']) || (float) $values['price'] > 9999999999999.99) $errors['price'] = 'Giá phải là số không âm, tối đa 2 chữ số thập phân và không vượt giới hạn lưu trữ.';
        if (!preg_match('/^\d+$/D', $values['warranty_years']) || (float) $values['warranty_years'] > 100) $errors['warranty_years'] = 'Bảo hành phải là số năm nguyên từ 0 đến 100.';
        if (!preg_match('/^[1-9]\d*$/D', $values['category_id']) || (float) $values['category_id'] > 2147483647) $errors['category_id'] = 'Vui lòng chọn danh mục hợp lệ.';
        return ['values' => $values, 'errors' => $errors];
    }
}
