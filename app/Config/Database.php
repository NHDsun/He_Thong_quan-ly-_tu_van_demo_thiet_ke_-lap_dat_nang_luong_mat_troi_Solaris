<?php
declare(strict_types=1);

namespace App\Config;

use PDO;

final class Database
{
    public static function connection(): PDO
    {
        $host = getenv('SOLARIS_DB_HOST') ?: '127.0.0.1';
        $port = getenv('SOLARIS_DB_PORT') ?: '3306';
        $name = getenv('SOLARIS_DB_NAME') ?: 'solaris';
        $user = getenv('SOLARIS_DB_USER') ?: 'root';
        $password = getenv('SOLARIS_DB_PASSWORD');

        return new PDO(
            "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
            $user,
            $password === false ? '' : $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
