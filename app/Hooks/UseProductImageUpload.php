<?php
declare(strict_types=1);

namespace App\Hooks;

use RuntimeException;

final class UseProductImageUpload
{
    public const MAX_BYTES = 5 * 1024 * 1024;

    public static function store(array $file): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_INI_SIZE) {
            throw new RuntimeException('Ảnh vượt giới hạn tải lên của máy chủ (' . ini_get('upload_max_filesize') . ').');
        }
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) throw new RuntimeException('Ảnh sản phẩm chỉ nhận JPG, JPEG hoặc PNG.');
        $inspected = UseFileUpload::inspect($file, 'survey_photo');
        if (!in_array($inspected['file_type'], ['image/jpeg', 'image/png'], true)) throw new RuntimeException('Nội dung tệp không phải ảnh JPG hoặc PNG hợp lệ.');
        $directory = dirname(__DIR__, 2) . '/assets/products';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new RuntimeException('Không thể tạo thư mục ảnh sản phẩm.');
        if (!move_uploaded_file($inspected['tmp_name'], $directory . '/' . $inspected['disk_name'])) throw new RuntimeException('Không thể lưu ảnh sản phẩm.');
        return $inspected['disk_name'];
    }

    public static function discard(string $name): void
    {
        if (!preg_match('/^[a-f0-9]{64}\.(jpg|jpeg|png)$/D', $name)) return;
        $path = dirname(__DIR__, 2) . '/assets/products/' . $name;
        if (is_file($path)) unlink($path);
    }
}
