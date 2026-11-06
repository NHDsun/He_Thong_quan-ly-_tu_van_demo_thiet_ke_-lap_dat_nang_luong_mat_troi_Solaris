<?php
declare(strict_types=1);

/** Run the additive SQL twice against session-only temporary tables. */
require_once dirname(__DIR__) . '/app/Config/Database.php';

use App\Config\Database;

$pdo = Database::connection();
$sqlFile = dirname(__DIR__) . '/database/solaris_catalog_additions.sql';
if (!is_file($sqlFile)) throw new RuntimeException('Catalog migration is missing.');

// Temporary tables shadow the real tables only in this connection and vanish on exit.
$pdo->exec('CREATE TEMPORARY TABLE categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL UNIQUE, description TEXT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
$pdo->exec('CREATE TEMPORARY TABLE products (id INT AUTO_INCREMENT PRIMARY KEY, category_id INT NOT NULL, product_code VARCHAR(50) NOT NULL UNIQUE, name VARCHAR(255) NOT NULL, capacity_watts INT NULL, price DECIMAL(15,2) NOT NULL, warranty_years INT DEFAULT 5, technical_specs JSON NULL, image_url VARCHAR(255) NULL, stock_quantity INT DEFAULT 0, is_active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

foreach (['tam-pin-mat-troi', 'bien-tan-inverter', 'pin-luu-tru-lithium', 'phu-kien-lap-dat'] as $index => $slug) {
    $statement = $pdo->prepare('INSERT INTO categories (id,name,slug) VALUES (?,?,?)');
    $statement->execute([$index + 1, 'Seed ' . ($index + 1), $slug]);
}
for ($id = 1; $id <= 26; $id++) {
    $statement = $pdo->prepare('INSERT INTO products (id,category_id,product_code,name,price,is_active) VALUES (?,?,?,?,?,1)');
    $statement->execute([$id, 1, sprintf('SOL-SEED-%03d', $id), 'Seed product ' . $id, 1]);
}

$apply = static function () use ($pdo, $sqlFile): void {
    foreach (file($sqlFile, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '--')) continue;
        $pdo->exec($line);
    }
};
$check = static function (bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
};

$apply();
$check((int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() === 196, 'First import should yield 196 products');
$check((int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn() === 195, 'Hidden product state should be preserved');
$check((int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() === 11, 'Seven new category slugs should be added');
$check((int) $pdo->query("SELECT COUNT(*) FROM products WHERE id<=26 AND product_code LIKE 'SOL-SEED-%'")->fetchColumn() === 26, 'Existing IDs and seed products must remain unchanged');
$check((int) $pdo->query("SELECT COUNT(*) FROM products WHERE product_code='JAM72D40-LB-575' AND image_url='jam72d40-lb-575.jpg'")->fetchColumn() === 1, 'Existing JPG fallback should be exported as a real image path');

$apply();
$check((int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() === 196, 'Second import must not duplicate products');
$check((int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() === 11, 'Second import must not duplicate categories');

echo "PASS: additive catalog migration yields 196 products and is repeatable; persistent tables untouched.\n";
