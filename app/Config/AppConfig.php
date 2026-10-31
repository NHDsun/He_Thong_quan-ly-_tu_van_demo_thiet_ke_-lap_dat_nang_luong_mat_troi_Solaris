<?php
declare(strict_types=1);

namespace App\Config;

final class AppConfig
{
    public const APP_NAME = 'SOLARIS';
    public const APP_TAGLINE = 'Khai Phá Năng Lượng Tương Lai';
    public const APP_VERSION = '2.1.0';
    public const DEFAULT_TIMEZONE = 'Asia/Ho_Chi_Minh';

    public const UPLOAD_MAX_BYTES = 5 * 1024 * 1024; // 5MB
    public const PAGINATION_PER_PAGE = 20;

    public static function basePath(string $path = ''): string
    {
        $base = dirname(__DIR__, 2);
        return $path === '' ? $base : $base . '/' . ltrim($path, '/\\');
    }

    public static function uploadPath(string $subDir = ''): string
    {
        $dir = self::basePath('assets/uploads' . ($subDir ? '/' . ltrim($subDir, '/\\') : ''));
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }
}
