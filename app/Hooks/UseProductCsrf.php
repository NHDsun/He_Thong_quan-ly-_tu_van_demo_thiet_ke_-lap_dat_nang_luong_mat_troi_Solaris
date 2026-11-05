<?php
declare(strict_types=1);

namespace App\Hooks;

final class UseProductCsrf
{
    public static function token(): string
    {
        if (!isset($_SESSION['tv2_csrf_token']) || !is_string($_SESSION['tv2_csrf_token'])) $_SESSION['tv2_csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['tv2_csrf_token'];
    }

    public static function valid(mixed $token): bool
    {
        return is_string($token) && isset($_SESSION['tv2_csrf_token']) && is_string($_SESSION['tv2_csrf_token']) && hash_equals($_SESSION['tv2_csrf_token'], $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
