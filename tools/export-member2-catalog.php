<?php
declare(strict_types=1);

/**
 * One-time export of the approved local TV2 catalog into an additive SQL migration.
 * Run from the repository root: php tools/export-member2-catalog.php
 * Never runs an INSERT/UPDATE/DELETE against the source database.
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require_once dirname(__DIR__) . '/app/Config/Database.php';

use App\Config\Database;

$pdo = Database::connection();
$databaseName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
if ($databaseName !== 'solaris') {
    fwrite(STDERR, "Expected source database solaris; found {$databaseName}. Export cancelled.\n");
    exit(1);
}

$categories = $pdo->query('SELECT name,slug,description FROM categories ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query('SELECT p.product_code,p.name,p.capacity_watts,p.price,p.warranty_years,p.technical_specs,p.image_url,p.stock_quantity,p.is_active,p.created_at,c.slug AS category_slug FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.id')->fetchAll(PDO::FETCH_ASSOC);
if (count($categories) !== 7 || count($products) !== 170) {
    fwrite(STDERR, 'Expected exactly 7 categories and 170 products. Export cancelled; review the changed database first.' . PHP_EOL);
    exit(1);
}

$codes = [];
$assetRoot = dirname(__DIR__) . '/assets/products/';
foreach ($products as &$product) {
    $code = (string) $product['product_code'];
    if (isset($codes[$code])) {
        fwrite(STDERR, "Duplicate product code {$code}. Export cancelled.\n");
        exit(1);
    }
    $codes[$code] = true;
    if (!empty($product['image_url'])) {
        $filename = basename((string) $product['image_url']);
        if (!is_file($assetRoot . $filename)) {
            $jpg = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
            if (!is_file($assetRoot . $jpg)) {
                fwrite(STDERR, "Missing image for {$code}: {$filename}. Export cancelled.\n");
                exit(1);
            }
            $filename = $jpg;
        }
        $product['image_url'] = $filename;
    }
}
unset($product);

$quote = static fn (?string $value): string => $value === null ? 'NULL' : $pdo->quote($value);
$number = static fn ($value): string => $value === null ? 'NULL' : (string) $value;

$sql = [
    '-- SOLARIS: add the 170 approved TV2 products to the existing 26-product seed.',
    '-- Additive and repeatable: no DELETE, REPLACE, explicit IDs or updates to existing rows.',
    '-- Import on a backed-up database whose products and categories tables already exist.',
    '-- Product images are in the tracked assets/products directory of this repository.',
    'SET NAMES utf8mb4;',
    'START TRANSACTION;',
];

foreach ($categories as $category) {
    $slug = $quote((string) $category['slug']);
    $sql[] = 'INSERT INTO categories (name,slug,description) '
        . 'SELECT ' . $quote((string) $category['name']) . ',' . $slug . ',' . $quote($category['description'])
        . ' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug=' . $slug . ');';
}

$sql[] = '';
$sql[] = '-- Preserve target AUTO_INCREMENT IDs so existing quote and project references remain unchanged.';
foreach ($products as $product) {
    $code = $quote((string) $product['product_code']);
    $values = [
        $code,
        $quote((string) $product['name']),
        $number($product['capacity_watts']),
        $number($product['price']),
        $number($product['warranty_years']),
        $quote($product['technical_specs']),
        $quote($product['image_url']),
        $number($product['stock_quantity']),
        $number($product['is_active']),
        $quote((string) $product['created_at']),
    ];
    $slug = $quote((string) $product['category_slug']);
    $sql[] = 'INSERT INTO products (category_id,product_code,name,capacity_watts,price,warranty_years,technical_specs,image_url,stock_quantity,is_active,created_at) '
        . 'SELECT c.id,' . implode(',', $values) . ' FROM categories c WHERE c.slug=' . $slug
        . ' AND NOT EXISTS (SELECT 1 FROM products p WHERE p.product_code=' . $code . ');';
}
$sql[] = 'COMMIT;';
$sql[] = '';

$path = dirname(__DIR__) . '/database/solaris_catalog_additions.sql';
$contents = implode("\n", $sql);
if (is_file($path) && file_get_contents($path) !== $contents) {
    fwrite(STDERR, "Export file already exists with different contents: {$path}. Review it before replacing.\n");
    exit(1);
}
if (!is_file($path) && file_put_contents($path, $contents) === false) {
    fwrite(STDERR, "Could not create {$path}.\n");
    exit(1);
}
echo "Catalog ready: 7 categories, 170 products. No source database rows changed.\n";
