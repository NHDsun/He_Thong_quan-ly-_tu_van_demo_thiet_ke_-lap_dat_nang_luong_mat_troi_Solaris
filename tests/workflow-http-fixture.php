<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/app/Config/Database.php';
$config = App\Config\Database::getConfig();
$name = $config['dbname'];
if (!preg_match('/^solaris_workflow_http_[a-f0-9]{10}$/D', $name)) {
    throw new RuntimeException('Only a generated HTTP test database is allowed.');
}
$db = new PDO("mysql:host={$config['host']};port={$config['port']};charset=utf8mb4", $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
if (($argv[1] ?? '') === 'drop') {
    $db->exec("DROP DATABASE IF EXISTS `$name`");
    exit;
}
if (($argv[1] ?? '') === 'init') {
    $db->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $db->exec("USE `$name`");
    $schema = preg_replace('/^(CREATE DATABASE[^;]+;|USE `solaris`;)/m', '', file_get_contents(dirname(__DIR__) . '/database/solaris_schema.sql'));
    $db->exec($schema);
    $q = $db->prepare('INSERT INTO users (id,username,password_hash,full_name,email,phone,role) VALUES (?,?,?,?,?,?,?)');
    foreach ([1 => 'admin', 2 => 'customer', 3 => 'technician', 4 => 'customer'] as $id => $role) {
        $q->execute([$id, 'http' . $id, password_hash('WorkflowTest!2026', PASSWORD_DEFAULT), 'HTTP Test ' . $id, "http$id@example.com", '0901234567', $role]);
    }
    $db->exec("INSERT INTO categories (id,name,slug) VALUES (1,'Tấm pin','tam-pin'),(2,'Inverter','inverter')");
    $db->exec("INSERT INTO products (id,category_id,product_code,name,capacity_watts,price,stock_quantity) VALUES (1,1,'TEST-PANEL','Tấm pin kiểm thử',550,2000000,100),(2,2,'TEST-INVERTER','Inverter kiểm thử',5000,10000000,100)");
    exit;
}
$db->exec("USE `$name`");
if (($argv[1] ?? '') === 'change_catalog') { $db->exec("UPDATE products SET name='Catalog changed',price=1"); exit; }
if (($argv[1] ?? '') === 'state') {
    echo json_encode(['quoteItems' => $db->query('SELECT quote_id,product_id,product_name,quantity,unit_price FROM solar_quote_items ORDER BY id')->fetchAll(), 'initialItems' => $db->query('SELECT project_id,product_id,product_name,quantity,unit_price FROM installation_items ORDER BY id')->fetchAll(), 'today' => $db->query('SELECT CURDATE()')->fetchColumn(), 'stages' => $db->query('SELECT id,project_id,stage,status,planned_start,started_at,proposed_next_start FROM project_stages ORDER BY id')->fetchAll(), 'quotes' => $db->query('SELECT id,user_id,source,status FROM solar_quotes ORDER BY id')->fetchAll(), 'projects' => $db->query('SELECT id,quote_id,status,current_stage,started_at FROM projects ORDER BY id')->fetchAll(), 'documents' => $db->query('SELECT file_path FROM project_documents')->fetchAll(PDO::FETCH_COLUMN), 'payments' => $db->query('SELECT project_id,amount FROM project_payments ORDER BY id')->fetchAll(), 'materials' => $db->query('SELECT project_id,quantity,unit_price FROM project_extra_materials ORDER BY id')->fetchAll()], JSON_THROW_ON_ERROR);
}
