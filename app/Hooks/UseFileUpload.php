<?php
declare(strict_types=1);

namespace App\Hooks;

use RuntimeException;

final class UseFileUpload
{
    public const TYPES = ['survey_photo', 'technical_drawing', 'evn_permit', 'procurement_invoice',
        'handover_report', 'installation_photo', 'testing_photo', 'stage_photo'];

    public static function inspect(array $file, string $type): array
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new RuntimeException('Loại tài liệu không hợp lệ.');
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
            throw new RuntimeException('Không nhận được file hợp lệ. Mỗi file tối đa 5MB.');
        }
        $size = filesize($file['tmp_name']);
        if ($size === false || $size === 0 || $size > 5 * 1024 * 1024) {
            throw new RuntimeException('File phải có dung lượng từ 1 byte đến 5MB.');
        }
        $name = basename(str_replace('\\', '/', (string) $file['name']));
        if (strlen($name) > 240) {
            throw new RuntimeException('Tên file quá dài.');
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $allowed = ['jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'],
            'pdf' => ['application/pdf'], 'dwg' => ['image/vnd.dwg', 'image/x-dwg', 'application/x-dwg', 'application/dwg', 'application/vnd.dwg', 'application/acad', 'application/x-acad', 'application/octet-stream']];
        if (!isset($allowed[$ext]) || !in_array($mime, $allowed[$ext], true)) {
            throw new RuntimeException('Chỉ nhận JPG, JPEG, PNG, PDF và DWG đúng định dạng.');
        }
        if (in_array($ext, ['jpg', 'jpeg', 'png'], true) && @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('Ảnh không hợp lệ.');
        }
        if ($ext === 'dwg') {
            $handle = fopen($file['tmp_name'], 'rb');
            $signature = fread($handle, 6);
            fclose($handle);
            if (!preg_match('/^AC10\d{2}$/D', $signature)) {
                throw new RuntimeException('Chữ ký DWG không hợp lệ.');
            }
        }
        return ['file_name' => $name, 'file_hash' => hash_file('sha256', $file['tmp_name']),
            'file_type' => $mime, 'file_size_kb' => (int) ceil($size / 1024),
            'disk_name' => hash('sha256', uniqid('', true) . $name) . '.' . $ext,
            'document_type' => $type, 'tmp_name' => $file['tmp_name']];
    }

    public static function store(array $file): string
    {
        $directory = dirname(__DIR__, 2) . '/assets/uploads/installation';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Không thể tạo thư mục upload.');
        }
        $path = 'assets/uploads/installation/' . $file['disk_name'];
        if (!move_uploaded_file($file['tmp_name'], dirname(__DIR__, 2) . '/' . $path)) {
            throw new RuntimeException('Không thể lưu tài liệu.');
        }
        return $path;
    }
}
