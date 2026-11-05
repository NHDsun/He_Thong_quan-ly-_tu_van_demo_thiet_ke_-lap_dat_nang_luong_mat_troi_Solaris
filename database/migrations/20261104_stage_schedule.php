<?php
declare(strict_types=1);

return static function (PDO $db): void {
    foreach (['projects' => ['started_at', 'DATETIME NULL'], 'project_stages' => ['proposed_next_start', 'DATE NULL']] as $table => [$column, $definition]) {
        $q = $db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
        $q->execute([$table, $column]);
        if (!$q->fetchColumn()) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }
    $db->exec("ALTER TABLE projects MODIFY status ENUM('pending_approval','rejected','new','scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending_approval'");
    $db->exec("ALTER TABLE project_stages MODIFY status ENUM('locked','scheduled','in_progress','pending_approval','revision_required','approved') NOT NULL DEFAULT 'locked'");
    // Chỉ dùng thời điểm đã được ghi nhận; không tự tạo giờ bắt đầu cho dữ liệu cũ.
    $db->exec("UPDATE projects p JOIN (SELECT project_id,MIN(started_at) first_start FROM project_stages WHERE started_at IS NOT NULL GROUP BY project_id) s ON s.project_id=p.id SET p.started_at=s.first_start WHERE p.started_at IS NULL AND p.status IN ('in_progress','completed')");
};
