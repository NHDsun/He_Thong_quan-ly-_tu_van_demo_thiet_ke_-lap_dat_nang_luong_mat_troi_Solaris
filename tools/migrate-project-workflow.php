<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/app/Config/Database.php';
$db = App\Config\Database::getConnection();
$db->exec("CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(100) PRIMARY KEY,applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$pending = [];
foreach (['20261104_project_workflow', '20261104_project_billing', '20261104_stage_schedule', '20261105_plan_quote_items'] as $version) {
    $q = $db->prepare('SELECT version FROM schema_migrations WHERE version=?');
    $q->execute([$version]);
    if (!$q->fetchColumn()) {
        $pending[] = $version;
    }
}
if (!$pending) {
    echo "All project migrations already applied.\n";
    exit;
}
$backup = ['database' => $db->query('SELECT DATABASE()')->fetchColumn(), 'tables' => []];
foreach (['solar_quotes','solar_quote_items','solar_quote_logs','projects','installation_items','project_stages','project_assignments','project_stage_logs','project_documents','project_requests','project_extra_materials','project_payments'] as $table) {
    $q = $db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
    $q->execute([$table]);
    if ($q->fetchColumn()) {
        $backup['tables'][$table] = ['schema' => $db->query("SHOW CREATE TABLE `$table`")->fetch()['Create Table'], 'rows' => $db->query("SELECT * FROM `$table`")->fetchAll()];
    }
}
$directory = sys_get_temp_dir() . '/solaris-workflow-backups';
if (!is_dir($directory) && !mkdir($directory, 0700, true)) {
    throw new RuntimeException('Cannot create backup directory.');
}
$path = $directory . '/' . $backup['database'] . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.json';
if (file_put_contents($path, json_encode($backup, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
    throw new RuntimeException('Cannot write backup.');
}
echo "Backup: $path\n";
foreach ($pending as $version) {
    $migrate = require dirname(__DIR__) . '/database/migrations/' . $version . '.php';
    $migrate($db);
    $db->prepare('INSERT INTO schema_migrations (version) VALUES (?)')->execute([$version]);
    echo "Applied: $version\n";
}
