<?php
declare(strict_types=1);

return static function (PDO $db): void {
    $add = static function (string $table, string $column, string $definition) use ($db): void {
        $q = $db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
        $q->execute([$table, $column]);
        if (!(int) $q->fetchColumn()) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    };
    foreach (['solar_quotes', 'projects'] as $table) {
        $add($table, 'legacy_status', 'VARCHAR(30) NULL');
        $add($table, 'reviewed_by', 'INT NULL');
        $add($table, 'reviewed_at', 'DATETIME NULL');
        $add($table, 'review_note', 'TEXT NULL');
        $db->exec("UPDATE `$table` SET legacy_status=status WHERE legacy_status IS NULL");
    }
    $add('solar_quotes', 'source', "ENUM('bill','plan') NOT NULL DEFAULT 'bill'");
    $add('solar_quotes', 'request_key', 'CHAR(64) NULL UNIQUE');
    $add('projects', 'cancelled_by', 'INT NULL');
    $add('projects', 'cancelled_at', 'DATETIME NULL');
    $add('projects', 'cancel_reason', 'TEXT NULL');
    $db->exec("ALTER TABLE solar_quotes MODIFY status VARCHAR(30) NULL DEFAULT 'draft'");
    $db->exec("UPDATE solar_quotes SET status='pending_approval' WHERE status IS NULL OR status NOT IN ('draft','pending_approval','approved','rejected')");
    $db->exec("ALTER TABLE solar_quotes MODIFY status ENUM('draft','pending_approval','approved','rejected') NOT NULL DEFAULT 'draft'");
    $db->exec("ALTER TABLE projects MODIFY status VARCHAR(30) NOT NULL DEFAULT 'pending_approval'");
    // Bảo toàn dự án đã có công việc, kể cả bản ghi cũ mang nhãn new/approved.
    $db->exec("UPDATE projects p SET status='in_progress' WHERE status NOT IN ('scheduled','in_progress','completed','cancelled','rejected') AND (
        EXISTS (SELECT 1 FROM project_assignments a WHERE a.project_id=p.id AND a.ended_at IS NULL)
        OR EXISTS (SELECT 1 FROM project_documents d WHERE d.project_id=p.id)
        OR EXISTS (SELECT 1 FROM project_stages s WHERE s.project_id=p.id AND (s.started_at IS NOT NULL OR s.status IN ('approved','pending_approval','revision_required'))))");
    $db->exec("UPDATE projects SET status='new' WHERE status='approved'");
    $db->exec("UPDATE projects SET status='pending_approval' WHERE status NOT IN ('pending_approval','new','scheduled','in_progress','completed','cancelled','rejected')");
    $db->exec("ALTER TABLE projects MODIFY status ENUM('pending_approval','rejected','new','scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending_approval', MODIFY current_stage ENUM('survey','design','procurement','installation','grid_connection','handover','cancelled') NULL DEFAULT NULL");
    $db->exec("UPDATE projects SET current_stage=NULL,technician_id=NULL WHERE status IN ('pending_approval','new','rejected')");
    $db->exec("UPDATE project_stages s JOIN projects p ON p.id=s.project_id SET s.status='locked',s.started_at=NULL WHERE p.status IN ('pending_approval','new','rejected')");
    $db->exec("UPDATE projects SET current_stage=NULL WHERE current_stage='cancelled'");
    $db->exec("ALTER TABLE projects MODIFY current_stage ENUM('survey','design','procurement','installation','grid_connection','handover') NULL DEFAULT NULL");
    $db->exec("ALTER TABLE project_stage_logs MODIFY stage ENUM('survey','design','procurement','installation','grid_connection','handover') NULL DEFAULT NULL");
    $db->exec("CREATE TABLE IF NOT EXISTS solar_quote_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,quote_id INT NOT NULL,actor_id INT NULL,
        action VARCHAR(50) NOT NULL,from_status VARCHAR(30) NULL,to_status VARCHAR(30) NOT NULL,
        note TEXT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_quote_logs (quote_id,id),
        FOREIGN KEY (quote_id) REFERENCES solar_quotes(id) ON DELETE CASCADE,
        FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $stages = ['survey','design','procurement','installation','grid_connection','handover'];
    foreach ($stages as $i => $stage) {
        $q = $db->prepare("INSERT INTO project_stages (project_id,stage,position,status) SELECT p.id,?,?, 'locked' FROM projects p WHERE NOT EXISTS (SELECT 1 FROM project_stages s WHERE s.project_id=p.id AND s.stage=?)");
        $q->execute([$stage, $i + 1, $stage]);
    }
};
