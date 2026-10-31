<?php
declare(strict_types=1);

namespace App\Modules\Member4_Installation;

use PDO;

final class Config
{
    public static function database(): PDO
    {
        $local = __DIR__ . '/config.local.php';
        $settings = is_file($local) ? require $local : [];
        $host = getenv('SOLARIS_DB_HOST') ?: ($settings['host'] ?? '127.0.0.1');
        $port = getenv('SOLARIS_DB_PORT') ?: ($settings['port'] ?? '3306');
        $name = getenv('SOLARIS_DB_NAME') ?: ($settings['database'] ?? 'solaris');
        $user = getenv('SOLARIS_DB_USER') ?: ($settings['username'] ?? 'root');
        $password = getenv('SOLARIS_DB_PASSWORD');
        return new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user,
            $password !== false ? $password : ($settings['password'] ?? ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
    }
}
