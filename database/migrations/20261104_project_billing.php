<?php
declare(strict_types=1);

return static function (PDO $db): void {
    $db->exec(file_get_contents(dirname(__DIR__) . '/tv4-billing.sql'));
};
