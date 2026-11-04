<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/Hooks/UsePaginationState.php';
require dirname(__DIR__) . '/app/Hooks/UseInstallationWorkflow.php';
require dirname(__DIR__) . '/app/Hooks/UseProjectBilling.php';
require dirname(__DIR__) . '/app/Modules/Member4_Installation/View.php';
require dirname(__DIR__) . '/app/Config/Database.php';

use App\Hooks\UseInstallationWorkflow as Workflow;
use App\Hooks\UseProjectBilling as Billing;

$config = \App\Config\Database::getConfig();
$db = new PDO("mysql:host={$config['host']};port={$config['port']};charset=utf8mb4", $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
$name = 'solaris_tv4_billing_' . bin2hex(random_bytes(6));
$count = 0;
$check = static function (bool $ok, string $message) use (&$count): void {
    if (!$ok) { throw new RuntimeException('FAIL: ' . $message); }
    $count++;
    echo "PASS: {$message}\n";
};
$reject = static function (callable $call, string $message) use ($check): void {
    try { $call(); } catch (RuntimeException $error) { if ($error instanceof PDOException) { throw $error; } $check(true, $message); return; }
    $check(false, $message);
};
try {
    $db->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $db->exec("USE `$name`");
    $schema = file_get_contents(dirname(__DIR__) . '/database/solaris_schema.sql');
    $db->exec(preg_replace('/^(CREATE DATABASE[^;]+;|USE `solaris`;)/m', '', $schema));
    $migration = file_get_contents(dirname(__DIR__) . '/database/tv4-billing.sql');
    $db->exec($migration);
    $db->exec($migration);
    $check(true, 'Migration chạy lại an toàn');
    $w = new Workflow($db);
    $b = new Billing($w);
    foreach ([1 => 'admin', 2 => 'technician', 3 => 'technician', 4 => 'customer', 5 => 'customer'] as $id => $role) {
        $w->run('INSERT INTO users (id,username,password_hash,full_name,email,phone,role) VALUES (?,?,?,?,?,?,?)', [$id, 'test' . $id, 'unused', 'User ' . $id, $id . '@example.test', '090', $role]);
    }
    $w->run("INSERT INTO categories (id,name,slug) VALUES (1,'Vật tư','vat-tu')");
    $w->run("INSERT INTO products (id,category_id,product_code,name,price) VALUES (1,1,'TEST','Cáp <script>',12345.67)");
    $w->run("INSERT INTO projects (id,contract_code,user_id,project_name,capacity_kwp,total_amount,installation_address,status,current_stage) VALUES (1,'TEST-001',4,'Dự án kiểm thử',5,100000,'Địa chỉ','in_progress','survey')");
    foreach (array_keys(Workflow::STAGES) as $i => $code) {
        $w->run('INSERT INTO project_stages (project_id,stage,position,status) VALUES (1,?,?,?)', [$code, $i + 1, $i === 0 ? 'in_progress' : 'locked']);
    }
    $w->run("INSERT INTO project_assignments (project_id,technician_id,planned_start,planned_end,hours_per_day,assigned_by) VALUES (1,2,'2026-11-02','2026-11-20',4,1)");
    $admin = ['id' => 1, 'role' => 'admin'];
    $tech = ['id' => 2, 'role' => 'technician'];
    $outsider = ['id' => 3, 'role' => 'technician'];
    $customer = ['id' => 4, 'role' => 'customer'];
    $other = ['id' => 5, 'role' => 'customer'];
    $material = ['product_id' => 1, 'quantity' => 2, 'material_reason' => 'Cáp dài hơn sau khảo sát'];
    foreach (['pending_approval', 'new', 'scheduled', 'rejected'] as $notStarted) {
        $w->run('UPDATE projects SET status=? WHERE id=1', [$notStarted]);
        $reject(fn () => $w->act($customer, 1, 'pay_project', ['expected_remaining' => '10000000', 'confirm_simulation' => '1']), 'Không thanh toán dự án ' . $notStarted);
        $reject(fn () => $w->act($admin, 1, 'add_material', $material), 'Không phát sinh vật tư dự án ' . $notStarted);
    }
    $w->run("UPDATE projects SET status='in_progress' WHERE id=1");
    $check($b->canAdd($w->project(1, $tech)), 'Cho phép cập nhật vật tư trong giai đoạn khảo sát');
    $w->run("UPDATE project_stages SET status='pending_approval' WHERE stage='survey'");
    $reject(fn () => $w->act($tech, 1, 'add_material', $material), 'Khóa vật tư khi khảo sát đang chờ duyệt');
    $reject(fn () => $b->invoice(1, $admin), 'Chặn in trước bước mua sắm');
    $w->run("UPDATE project_stages SET status='in_progress' WHERE stage='survey'");
    $reject(fn () => $w->act($outsider, 1, 'add_material', $material), 'Chặn kỹ thuật viên ngoài dự án');
    $reject(fn () => $w->act($customer, 1, 'add_material', $material), 'Khách hàng không thêm vật tư');
    foreach ([0, -1, '1.5', 10001] as $invalid) {
        $reject(fn () => $w->act($tech, 1, 'add_material', array_merge($material, ['quantity' => $invalid])), 'Chặn số lượng ' . $invalid);
    }
    $reject(fn () => $w->act($tech, 1, 'add_material', array_merge($material, ['material_reason' => ''])), 'Bắt buộc lý do');
    $w->act($tech, 1, 'add_material', $material + ['unit_price' => 1]);
    $p = $w->project(1, $admin);
    $check($b->summary($p)['extra'] === 2469134, 'Đơn giá lấy từ server, cộng chính xác phần thập phân');
    $check($w->rows('SELECT total_amount FROM projects WHERE id=1')[0]['total_amount'] === '100000.00', 'Giữ nguyên giá trị hợp đồng gốc');
    $w->run('UPDATE products SET price=20000 WHERE id=1');
    $check($b->summary($p)['extra'] === 2469134, 'Đơn giá phát sinh đã lưu không đổi theo danh mục');
    $pay = ['expected_remaining' => (string) $b->summary($p)['remaining'], 'confirm_simulation' => '1'];
    $reject(fn () => $w->act($other, 1, 'pay_project', $pay), 'Chặn thanh toán dự án người khác');
    $reject(fn () => $w->act($tech, 1, 'pay_project', $pay), 'Kỹ thuật viên không được thanh toán thay khách');
    $reject(fn () => $w->act($admin, 1, 'pay_project', $pay), 'Admin không được thanh toán thay khách');
    $reject(fn () => $w->act($customer, 1, 'pay_project', ['expected_remaining' => '1', 'confirm_simulation' => '1']), 'Chặn thanh toán trang cũ');
    $reject(fn () => $w->act($customer, 1, 'pay_project', ['expected_remaining' => $pay['expected_remaining']]), 'Bắt buộc xác nhận mô phỏng');
    $w->act($customer, 1, 'pay_project', $pay + ['amount' => 1]);
    $check($b->summary($p)['remaining'] === 0 && $b->summary($p)['paid'] === 12469134, 'Thanh toán đúng số dư do server tính');
    $reject(fn () => $w->act($customer, 1, 'pay_project', $pay), 'Gửi lặp không ghi thanh toán lần hai');
    $check((int) $w->rows('SELECT COUNT(*) n FROM project_payments')[0]['n'] === 1, 'Chỉ một giao dịch được ghi');
    $w->run("UPDATE project_stages SET status='approved' WHERE stage='survey'");
    $w->run("UPDATE project_stages SET status='in_progress' WHERE stage='design'");
    $w->run("UPDATE projects SET current_stage='design' WHERE id=1");
    $w->act($admin, 1, 'add_material', $material);
    $check($b->summary($p)['remaining'] === 4000000, 'Phát sinh sau thanh toán tạo đúng số dư mới');
    $w->run("UPDATE project_stages SET status='approved' WHERE stage='design'");
    $w->run("UPDATE project_stages SET status='in_progress' WHERE stage='procurement'");
    $w->run("UPDATE projects SET current_stage='procurement' WHERE id=1");
    $w->run('INSERT INTO installation_items (project_id,product_id,quantity,unit_price) VALUES (1,1,3,10000)');
    $invoice = $b->invoice(1, $customer);
    $check(count($invoice['materials']) === 2 && $invoice['summary']['total'] === 16469134, 'Hóa đơn gồm giá gốc và phát sinh, không cộng trùng');
    $check(count($invoice['baseItems']) === 1 && !$db->inTransaction(), 'Hóa đơn kèm vật tư gốc, đóng transaction đọc');
    ob_start(); require dirname(__DIR__) . '/resources/views/components/installation/invoice.php'; $html = ob_get_clean();
    $check(str_contains($html, 'Cáp &lt;script&gt;') && !str_contains($html, 'Cáp <script>') && str_contains($html, 'window.print()'), 'Bản in escape dữ liệu và có nút in');
    $reject(fn () => $b->invoice(1, $other), 'Chặn IDOR hóa đơn của khách khác');
    $reject(fn () => $b->invoice(1, $outsider), 'Chặn hóa đơn của kỹ thuật viên ngoài dự án');
    $w->run("UPDATE project_stages SET status='pending_approval' WHERE stage='procurement'");
    $reject(fn () => $w->act($admin, 1, 'add_material', $material), 'Khóa vật tư khi mua sắm đã gửi duyệt');
    $w->run("UPDATE projects SET status='completed' WHERE id=1");
    $remaining = (string) $b->summary($p)['remaining'];
    $w->act($customer, 1, 'pay_project', ['expected_remaining' => $remaining, 'confirm_simulation' => '1']);
    $check($b->summary($p)['remaining'] === 0, 'Khách vẫn có thể thanh toán khoản còn thiếu khi dự án hoàn tất');
    $w->run("UPDATE projects SET status='cancelled' WHERE id=1");
    $reject(fn () => $w->act($customer, 1, 'pay_project', $pay), 'Không thanh toán dự án đã hủy');
    $check(count($w->rows("SELECT id FROM project_stage_logs WHERE action IN ('material_added','payment_simulated')")) === 4, 'Thêm vật tư và thanh toán đều ghi nhật ký');
    for ($i = 0; $i < 21; $i++) {
        $w->run("INSERT INTO project_extra_materials (project_id,product_id,product_name,quantity,unit_price,reason,added_by) VALUES (1,1,'Cáp',1,1,'Test',1)");
    }
    $data = $b->details($w->project(1, $admin), 2);
    $check($data['pager']['pages'] === 2 && count($data['materials']) === 3, 'Phân trang vật tư khi quá 20 dòng');

    // Bước mua sắm chỉ được mở thi công khi trả đủ cả giá gốc và phát sinh.
    $w->run("INSERT INTO projects (id,contract_code,user_id,project_name,capacity_kwp,total_amount,installation_address,status,current_stage) VALUES (2,'TEST-PAYMENT-GATE',4,'Kiểm tra duyệt mua sắm',5,100,'Địa chỉ','in_progress','procurement')");
    foreach (array_keys(Workflow::STAGES) as $i => $code) {
        $w->run('INSERT INTO project_stages (project_id,stage,position,status) VALUES (2,?,?,?)', [$code, $i + 1, $i < 2 ? 'approved' : ($i === 2 ? 'in_progress' : 'locked')]);
    }
    $w->run('UPDATE project_stages SET proposed_next_start=CURDATE() WHERE project_id=2');
    $w->act($admin, 2, 'add_material', ['product_id' => 1, 'quantity' => 1, 'material_reason' => 'Bổ sung trước khi duyệt mua sắm']);
    $w->run("UPDATE project_stages SET status='pending_approval' WHERE project_id=2 AND stage='procurement'");
    $before = count($w->rows('SELECT id FROM project_stage_logs WHERE project_id=2'));
    $reject(fn () => $w->act($admin, 2, 'approve_stage', []), 'Chặn duyệt mua sắm chưa thanh toán');
    $check($w->project(2, $admin)['current_stage'] === 'procurement'
        && $w->rows("SELECT status FROM project_stages WHERE project_id=2 AND stage='procurement'")[0]['status'] === 'pending_approval'
        && $w->rows("SELECT status FROM project_stages WHERE project_id=2 AND stage='installation'")[0]['status'] === 'locked'
        && count($w->rows('SELECT id FROM project_stage_logs WHERE project_id=2')) === $before,
        'Duyệt bị chặn không thay trạng thái, mở bước sau hoặc ghi nhật ký duyệt');
    $w->act($admin, 2, 'reject_stage', ['review_note' => 'Bổ sung hồ sơ trong khi chờ thanh toán']);
    $check($w->rows("SELECT status FROM project_stages WHERE project_id=2 AND stage='procurement'")[0]['status'] === 'revision_required', 'Vẫn cho yêu cầu bổ sung khi chưa trả đủ');
    $w->run("UPDATE project_stages SET status='pending_approval' WHERE project_id=2 AND stage='procurement'");
    $w->run("INSERT INTO project_payments (project_id,paid_by,amount,payment_code) VALUES (2,4,100,'TEST-BASE-PAID')");
    $reject(fn () => $w->act($admin, 2, 'approve_stage', []), 'Trả đủ giá gốc nhưng thiếu phát sinh vẫn bị chặn');
    $w->run("INSERT INTO project_payments (project_id,paid_by,amount,payment_code) VALUES (2,4,19999.99,'TEST-PARTIAL-PAID')");
    $check($b->summary($w->project(2, $admin))['remaining'] === 1, 'Tính chính xác khoản còn thiếu 0,01 đ');
    $reject(fn () => $w->act($admin, 2, 'approve_stage', []), 'Thiếu 0,01 đ vẫn không cho duyệt');
    $w->act($customer, 2, 'pay_project', ['expected_remaining' => '1', 'confirm_simulation' => '1']);
    $w->act($admin, 2, 'approve_stage', []);
    $check($w->project(2, $admin)['current_stage'] === 'installation'
        && $w->rows("SELECT status FROM project_stages WHERE project_id=2 AND stage='procurement'")[0]['status'] === 'approved'
        && $w->rows("SELECT status FROM project_stages WHERE project_id=2 AND stage='installation'")[0]['status'] === 'scheduled',
        'Thanh toán đủ cả phát sinh mới duyệt và mở bước thi công');
    echo "{$count} kiểm tra thành công.\n";
} finally {
    if ($db->inTransaction()) { $db->rollBack(); }
    if (preg_match('/^solaris_tv4_billing_[a-f0-9]{12}$/D', $name)) {
        $db->exec("DROP DATABASE IF EXISTS `{$name}`");
    }
}
