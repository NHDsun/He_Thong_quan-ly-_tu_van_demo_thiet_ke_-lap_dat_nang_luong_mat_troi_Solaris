<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        require_once dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    }
});

use App\Config\Database;
use App\Hooks\UseInstallationWorkflow as Workflow;
use App\Hooks\UseSolarCalculator;

$config = Database::getConfig();
$db = new PDO("mysql:host={$config['host']};port={$config['port']};charset=utf8mb4", $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
$name = 'solaris_workflow_test_' . bin2hex(random_bytes(5));
$checks = 0;
$check = static function (bool $value, string $message) use (&$checks): void {
    if (!$value) {
        throw new RuntimeException('FAIL: ' . $message);
    }
    $checks++;
};
$denied = static function (callable $fn, string $message) use ($check): void {
    try {
        $fn();
    } catch (RuntimeException $error) {
        if ($error instanceof PDOException) {
            throw $error;
        }
        $check(true, $message);
        return;
    }
    $check(false, $message);
};
try {
    $db->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $db->exec("USE `$name`");
    $schema = file_get_contents(dirname(__DIR__) . '/database/solaris_schema.sql');
    $schema = preg_replace('/^(CREATE DATABASE[^;]+;|USE `solaris`;)/m', '', $schema);
    $db->exec($schema);
    $password = password_hash('WorkflowTest!2026', PASSWORD_DEFAULT);
    $q = $db->prepare('INSERT INTO users (id,username,password_hash,full_name,email,phone,role) VALUES (?,?,?,?,?,?,?)');
    foreach ([1 => 'admin', 2 => 'customer', 3 => 'technician', 4 => 'customer', 5 => 'technician'] as $id => $role) {
        $q->execute([$id, 'test' . $id, $password, 'Workflow Test ' . $id, "test$id@example.com", '0900000000', $role]);
    }
    $w = new Workflow($db);
    $admin = ['id' => 1, 'role' => 'admin'];
    $customer = ['id' => 2, 'role' => 'customer'];
    $tech = ['id' => 3, 'role' => 'technician'];
    $data = ['customer_name' => 'Khách kiểm thử', 'customer_phone' => '0901234567', 'customer_email' => 'test@example.com', 'address' => 'Địa chỉ kiểm thử', 'notes' => 'Yêu cầu khảo sát'];
    $calc = UseSolarCalculator::calculate(3000000, 100, 'residential', 'metal_roof');
    $make = static fn (string $source = 'bill', bool $submit = true): array => $w->registerQuote(2, $data, $calc, $source, $submit, bin2hex(random_bytes(32)));
    $scalar = static function (string $sql, array $params = []) use ($db): mixed {
        $q = $db->prepare($sql);
        $q->execute($params);
        return $q->fetchColumn();
    };
    $status = static fn (int $id): string => (string) $scalar('SELECT status FROM projects WHERE id=?', [$id]);
    $today = $w->today();
    $tomorrow = (new DateTimeImmutable($today))->modify('+1 day')->format('Y-m-d');
    $yesterday = (new DateTimeImmutable($today))->modify('-1 day')->format('Y-m-d');
    $assignment = ['start_date' => $today, 'end_date' => (new DateTimeImmutable($today))->modify('+14 days')->format('Y-m-d'), 'technicians' => [3, 5], 'hours_per_day' => 4];

    $denied(fn () => $w->registerQuote(0, $data, $calc, 'bill', true, bin2hex(random_bytes(32))), 'Guest cannot register');
    $denied(fn () => $w->registerQuote(1, $data, $calc, 'bill', true, bin2hex(random_bytes(32))), 'Admin cannot impersonate customer');
    $key = bin2hex(random_bytes(32));
    $direct = $w->registerQuote(2, $data, $calc, 'bill', true, $key);
    $id = $direct['project_id'];
    $check($status($id) === 'pending_approval', 'Direct survey awaits project approval');
    $check($scalar('SELECT status FROM solar_quotes WHERE id=?', [$direct['quote_id']]) === 'draft', 'Survey estimate is not auto-approved');
    $check($scalar('SELECT current_stage FROM projects WHERE id=?', [$id]) === null, 'No active stage before assignment');
    $check((int) $scalar("SELECT COUNT(*) FROM project_stages WHERE project_id=? AND status='locked'", [$id]) === 6, 'All stages locked');
    $check((int) $scalar('SELECT COUNT(*) FROM project_assignments WHERE project_id=?', [$id]) === 0, 'No hardcoded technician assignment');
    $check($w->registerQuote(2, $data, $calc, 'bill', true, $key) === $direct, 'Repeated registration returns existing request');
    $denied(fn () => $w->act($admin, $id, 'assign', $assignment), 'Cannot assign before approval');
    $denied(fn () => $w->act($customer, $id, 'approve_project', []), 'Customer cannot approve project');
    $denied(fn () => $w->project($id, ['id' => 4, 'role' => 'customer']), 'Customer cannot view another project');
    $denied(fn () => $w->project($id, $tech), 'Unassigned technician cannot access project');
    $w->act($admin, $id, 'approve_project', ['review_note' => 'Đủ thông tin triển khai']);
    $check($status($id) === 'new', 'Approved request becomes new project');
    $check((int) $scalar('SELECT reviewed_by FROM projects WHERE id=?', [$id]) === 1, 'Approval actor saved');
    $denied(fn () => $w->act($admin, $id, 'approve_project', []), 'Repeated approval rejected');
    $denied(fn () => $w->act($admin, $id, 'assign', array_replace($assignment, ['end_date' => $yesterday])), 'Reject reversed dates');
    $denied(fn () => $w->act($admin, $id, 'assign', array_replace($assignment, ['technicians' => [3, 2]])), 'Invalid crew rolls back');
    $check((int) $scalar('SELECT COUNT(*) FROM project_assignments WHERE project_id=?', [$id]) === 0, 'Failed assignment leaves no partial crew');
    $w->act($admin, $id, 'assign', $assignment);
    $check($status($id) === 'scheduled', 'Assignment schedules project without starting');
    $check($scalar('SELECT started_at FROM projects WHERE id=?', [$id]) === null, 'Project actual start remains empty');
    $check($scalar('SELECT current_stage FROM projects WHERE id=?', [$id]) === 'survey', 'Survey is first stage');
    $check((int) $scalar("SELECT COUNT(*) FROM project_stages WHERE project_id=? AND status='scheduled'", [$id]) === 1, 'Only first stage scheduled');
    $check(count($w->listing($tech, 'scheduled', 1)['projects']) === 1, 'Assigned technician sees work');
    $denied(fn () => $w->act($admin, $id, 'assign', $assignment), 'Cannot duplicate crew');
    $denied(fn () => $w->act($tech, $id, 'submit_stage', []), 'Evidence image required');
    foreach (array_keys(Workflow::STAGES) as $i => $stageCode) {
        $stageId = (int) $scalar('SELECT id FROM project_stages WHERE project_id=? AND stage=?', [$id, $stageCode]);
        $denied(fn () => $w->act($admin, $id, 'start_stage', ['stage_id' => $stageId]), 'Admin cannot start for technician');
        $w->act($tech, $id, 'start_stage', ['stage_id' => $stageId]);
        $denied(fn () => $w->act($tech, $id, 'start_stage', ['stage_id' => $stageId]), 'Cannot start twice');
        $check($scalar('SELECT started_at FROM project_stages WHERE id=?', [$stageId]) !== null, 'Real start recorded: ' . $stageCode);
        $denied(fn () => $w->act($tech, $id, 'submit_stage', ['next_start_date' => $today]), 'Evidence required after starting');
        $w->run("INSERT INTO project_documents (project_id,stage_id,document_type,file_name,file_hash,file_path,file_size_kb,file_type,uploaded_by) VALUES (?,?,'stage_photo','test.png',?,'tests/fixture.png',1,'image/png',3)", [$id, $stageId, hash('sha256', $stageCode)]);
        if ($i === 0) {
            $w->act($tech, $id, 'postpone', ['reason' => 'Mưa lớn', 'proposed_start' => $today, 'proposed_end' => $tomorrow]);
            $denied(fn () => $w->act($tech, $id, 'submit_stage', []), 'Pending postponement requires acknowledgement');
        }
        if ($i < 5) {
            $denied(fn () => $w->act($tech, $id, 'submit_stage', ['acknowledge_postpone' => '1']), 'Next execution date required');
            $denied(fn () => $w->act($tech, $id, 'submit_stage', ['acknowledge_postpone' => '1', 'next_start_date' => $yesterday]), 'Next execution date cannot be past');
        }
        $w->act($tech, $id, 'submit_stage', ['acknowledge_postpone' => '1', 'next_start_date' => $today]);
        $check($scalar('SELECT status FROM project_stages WHERE id=?', [$stageId]) === 'pending_approval', 'Stage submitted: ' . $stageCode);
        if ($i < 5) {
            $check($scalar('SELECT status FROM project_stages WHERE project_id=? AND position=?', [$id,$i+2]) === 'locked', 'Submitting evidence does not open next stage');
        }
        if ($i === 0) {
            $denied(fn () => $w->act($admin, $id, 'reject_stage', []), 'Revision reason required');
            $w->act($admin, $id, 'reject_stage', ['review_note' => 'Bổ sung góc chụp']);
            $check($scalar('SELECT status FROM project_stages WHERE id=?', [$stageId]) === 'revision_required', 'Stage returned for revision');
            $w->act($tech, $id, 'submit_stage', ['acknowledge_postpone' => '1', 'next_start_date' => $today]);
        }
        if ($stageCode === 'procurement') {
            $denied(fn () => $w->act($admin, $id, 'approve_stage', []), 'Procurement requires full payment');
            $balance = (new \App\Hooks\UseProjectBilling($w))->summary($w->project($id, $customer))['remaining'];
            $w->act($customer, $id, 'pay_project', ['expected_remaining' => (string) $balance, 'confirm_simulation' => '1']);
        }
        $w->act($admin, $id, 'approve_stage', []);
        $check($scalar('SELECT status FROM project_stages WHERE id=?', [$stageId]) === 'approved', 'Stage approved: ' . $stageCode);
        $denied(fn () => $w->act($admin, $id, 'approve_stage', []), 'Repeated stage approval cannot skip next stage');
    }
    $check($status($id) === 'completed', 'Handover completes project');
    $check((int) $scalar('SELECT COUNT(*) FROM project_assignments WHERE project_id=? AND ended_at IS NULL', [$id]) === 0, 'Completion releases crew');
    $check((int) $scalar("SELECT COUNT(*) FROM project_requests WHERE project_id=? AND status='pending'", [$id]) === 0, 'Completion closes pending requests');
    $denied(fn () => $w->act($admin, $id, 'cancel_project', ['review_note' => 'Không hợp lệ']), 'Completed project immutable');

    $plan = $make('plan');
    $check($plan['project_id'] === null, 'Plan creates no project before quote approval');
    $check($scalar('SELECT status FROM solar_quotes WHERE id=?', [$plan['quote_id']]) === 'pending_approval', 'Plan awaits review');
    $denied(fn () => $w->reviewQuote($plan['quote_id'], 2, true, ''), 'Customer cannot approve quote');
    $planProject = $w->reviewQuote($plan['quote_id'], 1, true, 'Cấu hình phù hợp');
    $check($status($planProject) === 'pending_approval', 'Quote approval creates request awaiting deployment approval');
    $check($w->createFromApprovedQuote($plan['quote_id'], 1) === $planProject, 'Integration cannot create duplicate project');
    $denied(fn () => $w->reviewQuote($plan['quote_id'], 1, true, ''), 'Repeated quote approval rejected');
    $w->act($admin, $planProject, 'approve_project', []);
    $w->act($admin, $planProject, 'assign', $assignment);
    $check($status($planProject) === 'scheduled', 'Plan path waits for technician start');
    $surveyId = (int) $scalar("SELECT id FROM project_stages WHERE project_id=? AND stage='survey'", [$planProject]);
    $denied(fn () => $w->act($customer, $planProject, 'start_stage', ['stage_id' => $surveyId]), 'Customer cannot start stage');
    $w->run('UPDATE project_stages SET planned_start=? WHERE id=?', [$tomorrow, $surveyId]);
    $w->act($tech, $planProject, 'start_stage', ['stage_id' => $surveyId]);
    $projectStart = $scalar('SELECT started_at FROM projects WHERE id=?', [$planProject]);
    $check($projectStart !== null && $status($planProject) === 'in_progress', 'First click starts project clock');
    $check(substr($projectStart,0,10) === $today && $scalar('SELECT planned_start FROM project_stages WHERE id=?',[$surveyId]) === $tomorrow,'Survey can start early and preserves planned date');
    $w->run("INSERT INTO project_documents (project_id,stage_id,document_type,file_name,file_hash,file_path,file_size_kb,file_type,uploaded_by) VALUES (?,?,'stage_photo','test.png',?,'tests/fixture.png',1,'image/png',3)", [$planProject,$surveyId,hash('sha256','planned-survey')]);
    $future = (new DateTimeImmutable($today))->modify('+20 days')->format('Y-m-d');
    $w->act($tech,$planProject,'submit_stage',['next_start_date'=>$future]);
    $w->run('UPDATE project_stages SET proposed_next_start=NULL WHERE id=?',[$surveyId]);
    $denied(fn () => $w->act($admin,$planProject,'approve_stage',[]),'Legacy submission without next date cannot advance');
    $w->act($admin,$planProject,'reject_stage',['review_note'=>'Bổ sung lịch bước kế tiếp']);
    $w->act($tech,$planProject,'submit_stage',['next_start_date'=>$future]);
    $w->act($admin,$planProject,'approve_stage',[]);
    $nextId=(int)$scalar("SELECT id FROM project_stages WHERE project_id=? AND stage='design'",[$planProject]);
    $check($scalar('SELECT planned_start FROM project_stages WHERE id=?',[$nextId])===$future, 'Approval uses technician proposed date');
    $check($scalar('SELECT started_at FROM project_stages WHERE id=?',[$nextId])===null, 'Approval never starts next stage clock');
    $check($scalar('SELECT planned_end_date FROM projects WHERE id=?',[$planProject])===$future, 'Approved execution date extends project estimate when needed');
    $check($scalar('SELECT MIN(planned_end) FROM project_assignments WHERE project_id=? AND ended_at IS NULL',[$planProject])===$future,'Approved execution date extends crew reservations');
    $check((int)$scalar('SELECT COUNT(*) FROM project_stages WHERE project_id=? AND position>2 AND planned_start IS NULL',[$planProject])===4, 'Future stages have no equal time allocation');
    $denied(fn () => $w->act($tech,$planProject,'start_stage',['stage_id'=>$surveyId]),'Stale start form cannot start next stage');
    $w->act($tech,$planProject,'postpone',['reason'=>'Đổi lịch thực địa','proposed_start'=>(new DateTimeImmutable($future))->modify('+2 days')->format('Y-m-d')]);
    $requestId=(int)$scalar("SELECT id FROM project_requests WHERE project_id=? AND status='pending'",[$planProject]);
    $w->act($admin,$planProject,'approve_request',['request_id'=>$requestId]);
    $check($scalar('SELECT started_at FROM projects WHERE id=?',[$planProject])===$projectStart,'Postpone preserves project actual start');
    $approvedPlan=$scalar('SELECT planned_start FROM project_stages WHERE id=?',[$nextId]);
    $w->act($tech,$planProject,'start_stage',['stage_id'=>$nextId]);
    $check(substr((string)$scalar('SELECT started_at FROM project_stages WHERE id=?',[$nextId]),0,10)===$today && $scalar('SELECT planned_start FROM project_stages WHERE id=?',[$nextId])===$approvedPlan,'Later stage can start early without changing approved plan');
    $check($scalar('SELECT started_at FROM projects WHERE id=?',[$planProject])===$projectStart,'Starting next stage never resets project actual start');
    $denied(fn () => $w->act($tech, $planProject, 'replacement', ['reason' => 'Đổi lịch', 'old_technician_id' => 3, 'proposed_technician_id' => 5]), 'Replacement must be outside crew');
    $denied(fn () => $w->act($admin, $planProject, 'cancel_project', []), 'Cancellation requires reason');
    $w->act($admin, $planProject, 'cancel_project', ['review_note' => 'Khách đề nghị dừng']);
    $check($status($planProject) === 'cancelled', 'Cancellation saved');
    $check((int) $scalar('SELECT COUNT(*) FROM project_assignments WHERE project_id=? AND ended_at IS NULL', [$planProject]) === 0, 'Cancellation releases crew');
    $check($scalar('SELECT current_stage FROM projects WHERE id=?', [$planProject]) === 'design', 'Cancellation preserves last stage');
    $reject = $make();
    $denied(fn () => $w->act($admin, $reject['project_id'], 'reject_project', []), 'Project rejection requires reason');
    $w->act($admin, $reject['project_id'], 'reject_project', ['review_note' => 'Ngoài khu vực hỗ trợ']);
    $check($status($reject['project_id']) === 'rejected', 'Project rejected');
    $check($w->detail($reject['project_id'], $customer)['project']['review_note'] === 'Ngoài khu vực hỗ trợ', 'Customer sees rejection reason');
    $denied(fn () => $w->act($admin, $reject['project_id'], 'assign', $assignment), 'Rejected project immutable');
    $rejectedQuote = $make('plan');
    $denied(fn () => $w->reviewQuote($rejectedQuote['quote_id'], 1, false, ''), 'Quote rejection requires reason');
    $w->reviewQuote($rejectedQuote['quote_id'], 1, false, 'Chọn lại inverter');
    $check($scalar('SELECT status FROM solar_quotes WHERE id=?', [$rejectedQuote['quote_id']]) === 'rejected', 'Quote rejected without creating project');
    foreach (['bill', 'plan'] as $source) {
        $draft = $make($source, false);
        $check($draft['project_id'] === null, 'Draft creates no project');
        $denied(fn () => $w->submitQuote($draft['quote_id'], 4), 'Cannot submit another customer draft');
        $w->submitQuote($draft['quote_id'], 2);
        $check($source === 'plan' ? $scalar('SELECT status FROM solar_quotes WHERE id=?', [$draft['quote_id']]) === 'pending_approval' : (int) $scalar('SELECT COUNT(*) FROM projects WHERE quote_id=?', [$draft['quote_id']]) === 1, 'Draft submission follows stored source');
    }
    $legacyEmpty = $make();
    $legacyApproved = $make();
    $legacyActive = $make();
    $w->act($admin, $legacyActive['project_id'], 'approve_project', []);
    $w->act($admin, $legacyActive['project_id'], 'assign', $assignment);
    $db->exec("ALTER TABLE projects MODIFY status VARCHAR(30) NOT NULL");
    $w->run("UPDATE projects SET status='' WHERE id=?", [$legacyEmpty['project_id']]);
    $w->run("UPDATE projects SET status='approved' WHERE id IN (?,?)", [$legacyApproved['project_id'], $legacyActive['project_id']]);
    $db->exec("ALTER TABLE solar_quotes MODIFY status VARCHAR(30) NOT NULL");
    $w->run("UPDATE solar_quotes SET status='contacted' WHERE id=?", [$legacyEmpty['quote_id']]);
    $before = $scalar('SELECT COUNT(*) FROM project_stage_logs');
    $migrate = require dirname(__DIR__) . '/database/migrations/20261104_project_workflow.php';
    $migrate($db);
    $migrate($db);
    $check($status($id) === 'completed' && $status($planProject) === 'cancelled', 'Migration preserves terminal states');
    $check($scalar('SELECT COUNT(*) FROM project_stage_logs') === $before, 'Migration preserves history');
    $check($status($legacyEmpty['project_id']) === 'pending_approval', 'Repair empty legacy project status');
    $check($status($legacyApproved['project_id']) === 'new', 'Unstarted approved legacy project awaits assignment');
    $check($status($legacyActive['project_id']) === 'in_progress', 'Legacy project with crew remains in progress');
    $check($scalar('SELECT legacy_status FROM solar_quotes WHERE id=?', [$legacyEmpty['quote_id']]) === 'contacted', 'Preserve legacy contact status');
    $check($scalar('SELECT status FROM solar_quotes WHERE id=?', [$legacyEmpty['quote_id']]) === 'pending_approval', 'Normalize legacy quote status without auto-approval');
    $clockBefore=$scalar('SELECT started_at FROM projects WHERE id=?',[$id]);
    $stageDatesBefore=$w->rows('SELECT id,planned_start,planned_end,started_at FROM project_stages ORDER BY id');
    $scheduleMigration=require dirname(__DIR__).'/database/migrations/20261104_stage_schedule.php';
    $scheduleMigration($db);
    $scheduleMigration($db);
    $check($scalar('SELECT started_at FROM projects WHERE id=?',[$id])===$clockBefore,'Schedule migration preserves project actual clock');
    $check($w->rows('SELECT id,planned_start,planned_end,started_at FROM project_stages ORDER BY id')===$stageDatesBefore,'Schedule migration preserves historical stage dates');
    echo "PASS: $checks assertions; isolated database $name\n";
} finally {
    if (preg_match('/^solaris_workflow_test_[a-f0-9]{10}$/D', $name)) {
        $db->exec("DROP DATABASE `$name`");
    }
}
