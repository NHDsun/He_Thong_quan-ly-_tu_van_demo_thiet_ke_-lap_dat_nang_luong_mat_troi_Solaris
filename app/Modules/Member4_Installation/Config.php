<?php
declare(strict_types=1);

namespace App\Modules\Member4_Installation;

use App\Config\Database;
use PDO;

final class Config
{
    public static function database(): PDO
    {
        require_once dirname(__DIR__, 2) . '/Config/Database.php';
        return Database::getConnection();
    }
}
