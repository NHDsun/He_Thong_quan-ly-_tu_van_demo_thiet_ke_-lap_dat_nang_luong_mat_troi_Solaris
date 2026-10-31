<?php
/**
 * =========================================================================
 * SOLARIS ENERGY SYSTEM - DATABASE CONFIGURATION & CONNECTION (PDO & MySQLi)
 * =========================================================================
 * Quản lý kết nối Cơ sở dữ liệu MySQL cho toàn bộ hệ thống Solaris và các bài tập.
 * Hỗ trợ chế độ kết nối PDO an toàn chống SQL Injection và MySQLi cho bài tập thực hành.
 */

namespace App\Config;

use PDO;
use PDOException;
use mysqli;

class Database
{
    private static ?PDO $pdo = null;
    private static ?mysqli $mysqli = null;
    private static ?string $connectionError = null;
    private static bool $pdoAttempted = false;
    private static bool $mysqliAttempted = false;

    // Cấu hình mặc định môi trường XAMPP
    private static string $host = '127.0.0.1';
    private static int $port = 3306;
    private static string $dbName = 'solaris_db';
    private static string $fallbackDbName = 'quanly_ban_sua';
    private static string $user = 'root';
    private static string $password = '';
    private static string $charset = 'utf8mb4';

    /**
     * Khởi tạo và trả về đối tượng kết nối PDO (Singleton)
     */
    public static function getPdo(): ?PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        if (self::$pdoAttempted) {
            return null;
        }
        self::$pdoAttempted = true;

        if (!self::isPortOpen()) {
            self::$connectionError = "Không thể kết nối đến MySQL Server tại " . self::$host . ":" . self::$port . " (Dịch vụ MySQL chưa được khởi động trong XAMPP Control Panel).";
            return null;
        }

        $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbName . ";charset=" . self::$charset;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2,
        ];

        try {
            self::$pdo = new PDO($dsn, self::$user, self::$password, $options);
            self::$connectionError = null;
            return self::$pdo;
        } catch (PDOException $e) {
            // Thử kết nối fallback tới CSDL quanly_ban_sua nếu CSDL solaris_db chưa được tạo
            try {
                $fallbackDsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$fallbackDbName . ";charset=" . self::$charset;
                self::$pdo = new PDO($fallbackDsn, self::$user, self::$password, $options);
                self::$connectionError = null;
                return self::$pdo;
            } catch (PDOException $ex) {
                self::$connectionError = $e->getMessage();
                return null;
            }
        }
    }

    /**
     * Khởi tạo và trả về đối tượng kết nối MySQLi (Singleton)
     */
    public static function getMysqli(): ?mysqli
    {
        if (self::$mysqli !== null && @mysqli_ping(self::$mysqli)) {
            return self::$mysqli;
        }
        if (self::$mysqliAttempted) {
            return null;
        }
        self::$mysqliAttempted = true;

        if (!self::isPortOpen()) {
            self::$connectionError = "Không thể kết nối đến MySQL Server (Dịch vụ chưa bật).";
            return null;
        }

        mysqli_report(MYSQLI_REPORT_OFF);

        // Thử kết nối với solaris_db trước
        $conn = @mysqli_connect(self::$host, self::$user, self::$password, self::$dbName, self::$port);
        if (!$conn) {
            // Fallback thử quanly_ban_sua hoặc ql_ban_sua
            $conn = @mysqli_connect(self::$host, self::$user, self::$password, self::$fallbackDbName, self::$port);
            if (!$conn) {
                $conn = @mysqli_connect(self::$host, self::$user, self::$password, 'ql_ban_sua', self::$port);
            }
        }

        if ($conn) {
            @mysqli_set_charset($conn, 'utf8');
            self::$mysqli = $conn;
            self::$connectionError = null;
            return self::$mysqli;
        }

        self::$connectionError = mysqli_connect_error();
        return null;
    }

    /**
     * Kiểm tra nhanh cổng kết nối MySQL (Fail fast khi MySQL chưa bật)
     */
    public static function isPortOpen(): bool
    {
        $connection = @fsockopen(self::$host, self::$port, $errno, $errstr, 0.15);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }

    /**
     * Kiểm tra trạng thái kết nối MySQL
     */
    public static function isConnected(): bool
    {
        return self::getPdo() !== null || self::getMysqli() !== null;
    }

    /**
     * Lấy thông báo lỗi kết nối nếu có
     */
    public static function getLastError(): ?string
    {
        return self::$connectionError;
    }

    /**
     * Trả về tên database hiện tại
     */
    public static function getDbName(): string
    {
        return self::$dbName;
    }
}
