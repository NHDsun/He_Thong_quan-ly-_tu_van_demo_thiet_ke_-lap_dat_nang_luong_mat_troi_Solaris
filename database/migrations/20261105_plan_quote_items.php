<?php
declare(strict_types=1);

return static function (PDO $db): void {
    $db->exec(file_get_contents(dirname(__DIR__) . '/plan-quote-items.sql'));
    $q = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='installation_items' AND COLUMN_NAME='product_name'");
    if (!$q->fetchColumn()) {
        $db->exec('ALTER TABLE installation_items ADD COLUMN product_name VARCHAR(255) NULL');
    }
    $db->exec('UPDATE installation_items i JOIN products p ON p.id=i.product_id SET i.product_name=p.name WHERE i.product_name IS NULL');
};
