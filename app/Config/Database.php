<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $instance = null;

    /**
     * Lấy đối tượng kết nối PDO duy nhất (Singleton).
     */
    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $config = self::getConfig();
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            self::$instance = new PDO($dsn, $config['user'], $config['pass'], $options);
            return self::$instance;
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new RuntimeException(
                'Không thể kết nối đến cơ sở dữ liệu MySQL. Vui lòng kiểm tra dịch vụ MySQL trên XAMPP và chắc chắn CSDL `' . $config['dbname'] . '` đã được tạo.',
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * Cấu hình thông số kết nối CSDL (hỗ trợ file local override hoặc biến môi trường).
     */
    public static function getConfig(): array
    {
        $localConfigFile = __DIR__ . '/database.local.php';
        $local = is_file($localConfigFile) ? require $localConfigFile : [];

        return [
            'host'    => getenv('SOLARIS_DB_HOST') ?: ($local['host'] ?? '127.0.0.1'),
            'port'    => getenv('SOLARIS_DB_PORT') ?: ($local['port'] ?? '3306'),
            'dbname'  => getenv('SOLARIS_DB_NAME') ?: ($local['dbname'] ?? 'solaris'),
            'user'    => getenv('SOLARIS_DB_USER') ?: ($local['user'] ?? 'root'),
            'pass'    => getenv('SOLARIS_DB_PASSWORD') !== false ? (string)getenv('SOLARIS_DB_PASSWORD') : ($local['pass'] ?? ''),
            'charset' => 'utf8mb4'
        ];
    }

    /**
     * Kiểm tra nhanh trạng thái kết nối CSDL.
     */
    public static function checkConnection(): bool
    {
        try {
            $pdo = self::getConnection();
            return (bool)$pdo->query('SELECT 1');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Đóng / reset kết nối (phục vụ kiểm thử).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
