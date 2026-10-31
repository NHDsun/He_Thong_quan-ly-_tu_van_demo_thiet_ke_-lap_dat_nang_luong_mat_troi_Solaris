<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/Hooks/UsePaginationState.php';
require dirname(__DIR__) . '/app/Hooks/UseFileUpload.php';
require dirname(__DIR__) . '/app/Hooks/UseInstallationWorkflow.php';
require dirname(__DIR__) . '/app/Modules/Member4_Installation/View.php';

use App\Hooks\UseInstallationWorkflow as Workflow;
use App\Hooks\UsePaginationState;
use App\Modules\Member4_Installation\View;

date_default_timezone_set('Asia/Ho_Chi_Minh');
$server = new PDO('mysql:host=' . (getenv('SOLARIS_DB_HOST') ?: '127.0.0.1') . ';port=' . (getenv('SOLARIS_DB_PORT') ?: '3306') . ';charset=utf8mb4', getenv('SOLARIS_DB_USER') ?: 'root', getenv('SOLARIS_DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$database = 'solaris_tv4_test_' . bin2hex(random_bytes(6));
$count = 0;
$check = static function (bool $condition, string $message) use (&$count): void {
    if (!$condition) { throw new RuntimeException('FAIL: ' . $message); }
    $count++;
    echo "PASS: {$message}\n";
};
$reject = static function (callable $call, string $message) use ($check): void {
    try { $call(); } catch (RuntimeException $error) { $check(true, $message); return; }
    $check(false, $message);
};
try {
    $schema = file_get_contents(dirname(__DIR__) . '/database/tv4-installation.sql');
    $schema = str_replace(['CREATE DATABASE IF NOT EXISTS solaris ', 'USE solaris;'], ["CREATE DATABASE IF NOT EXISTS {$database} ", "USE {$database};"], $schema);
    $server->exec($schema);
    $w = new Workflow($server);
    foreach ([[1,'admin'],[2,'technician'],[3,'technician'],[4,'technician'],[5,'customer']] as [$id,$role]) {
        $w->run('INSERT INTO users (id,username,password_hash,full_name,email,phone,role) VALUES (?,?,?,?,?,?,?)', [$id, 'test' . $id, password_hash('testing', PASSWORD_DEFAULT), 'User ' . $id, $id . '@example.test', '0900000000', $role]);
    }
    $admin = ['id'=>1,'role'=>'admin']; $tech = ['id'=>2,'role'=>'technician'];
    $w->run("INSERT INTO solar_quotes (id,user_id,customer_name,customer_phone,address,monthly_electric_bill,roof_area_sqm,estimated_system_kwp,panel_count,estimated_total_cost,payback_years,status) VALUES (1,5,'Khách','090','Địa chỉ',3000000,100,7,14,100000000,4,'approved')");
    $id = $w->createFromApprovedQuote(1, 1);
    $check($w->createFromApprovedQuote(1,1) === $id, 'Báo giá sinh duy nhất một dự án');
    $check(count($w->detail($id,$admin)['stages']) === 6, 'Khởi tạo đủ sáu giai đoạn');
    $reject(fn() => $w->createFromApprovedQuote(1,2), 'Kỹ thuật viên không tạo dự án từ báo giá');
    $reject(fn() => $w->project($id,$tech), 'Chặn kỹ thuật viên chưa được phân công');
    $reject(fn() => $w->act($admin,$id,'assign',['start_date'=>'2026-11-02','end_date'=>'2026-11-03','technicians'=>[2]]), 'Từ chối kế hoạch thiếu sáu ngày làm việc');
    $w->act($admin,$id,'assign',['start_date'=>'2026-11-02','end_date'=>'2026-11-20','technicians'=>[2,3],'hours_per_day'=>4]);
    $check(count($w->detail($id,$tech)['crew']) === 2, 'Phân công nhiều kỹ thuật viên');
    $reject(fn() => $w->act($tech,$id,'assign',[]), 'Kỹ thuật viên không có quyền phân công');
    $a = $w->availability('2026-11-02','2026-11-20');
    $busy = array_values(array_filter($a['technicians'],fn($t)=>(int)$t['id']===2))[0];
    $check($busy['free_percent'] === 50 && $busy['project_count'] === 1, 'Tính đồng thời mức rảnh và số dự án');
    $reject(fn() => $w->act($tech,$id,'submit_stage',[]), 'Bắt buộc ảnh minh chứng trước khi gửi duyệt');
    $reject(fn() => $w->act($admin,$id,'approve_stage',[]), 'Không duyệt giai đoạn chưa gửi');
    $stages = $w->detail($id,$admin)['stages']; $first = $stages[0];
    $w->act($tech,$id,'postpone',['proposed_start'=>'2026-11-03','proposed_end'=>'2026-11-05','reason'=>'Mưa']);
    $reject(fn() => $w->act($tech,$id,'postpone',['proposed_start'=>'2026-11-03','proposed_end'=>'2026-11-05','reason'=>'Mưa']), 'Chặn yêu cầu hoãn trùng đang chờ');
    $request = $w->detail($id,$admin)['requests'][0];
    $w->act($admin,$id,'approve_request',['request_id'=>$request['id']]);
    $shifted = $w->detail($id,$admin);
    $check($shifted['stages'][1]['planned_start'] === '2026-11-06' && $shifted['project']['planned_end_date'] === '2026-11-22', 'Duyệt hoãn dời cả lịch giai đoạn sau và ngày kết thúc dự án');
    $w->act($tech,$id,'replacement',['old_technician_id'=>3,'proposed_technician_id'=>4,'reason'=>'Đổi đội']);
    $request = $w->detail($id,$admin)['requests'][0];
    $w->act($admin,$id,'approve_request',['request_id'=>$request['id']]);
    $reject(fn() => $w->project($id,['id'=>3,'role'=>'technician']), 'Người bị thay mất quyền truy cập ngay');
    $check((bool)$w->project($id,['id'=>4,'role'=>'technician']), 'Người thay thế có quyền truy cập');
    $w->act($tech,$id,'postpone',['proposed_start'=>'2026-11-04','proposed_end'=>'2026-11-06','reason'=>'Mưa lần hai']);
    $w->run("INSERT INTO project_documents (project_id,stage_id,document_type,file_name,file_hash,file_path,file_size_kb,file_type,uploaded_by) VALUES (?,?,'stage_photo','test.png',?,'assets/uploads/installation/test.png',1,'image/png',2)",[$id,$first['id'],str_repeat('a',64)]);
    $reject(fn() => $w->act($tech,$id,'submit_stage',[]), 'Cảnh báo hoãn bắt buộc xác nhận tại máy chủ');
    $w->act($tech,$id,'submit_stage',['acknowledge_postpone'=>'1']);
    $pending = $w->detail($id,$admin)['requests'][0];
    $reject(fn() => $w->act($admin,$id,'approve_request',['request_id'=>$pending['id']]), 'Chặn duyệt hoãn khi đang chờ duyệt hoàn thành');
    $reject(fn() => $w->act($admin,$id,'reject_stage',[]), 'Từ chối giai đoạn bắt buộc lý do');
    $w->act($admin,$id,'reject_stage',['review_note'=>'Cần bổ sung']);
    $check($w->detail($id,$admin)['stages'][0]['status']==='revision_required', 'Trả giai đoạn về bổ sung');
    $w->act($tech,$id,'submit_stage',['acknowledge_postpone'=>'1']);
    $w->act($admin,$id,'approve_stage',[]);
    $check(!$w->detail($id,$admin)['requests'], 'Duyệt hoàn thành tự đóng yêu cầu hoãn đang chờ');
    $reject(fn() => $w->act($admin,$id,'approve_request',['request_id'=>$pending['id']]), 'Chặn duyệt lại yêu cầu đã đóng');
    for ($i=1;$i<6;$i++) {
        $detail=$w->detail($id,$admin); $s=$detail['stages'][$i];
        $check($s['status']==='in_progress', 'Chỉ mở lần lượt bước '.($i+1));
        $w->run("INSERT INTO project_documents (project_id,stage_id,document_type,file_name,file_hash,file_path,file_size_kb,file_type,uploaded_by) VALUES (?,?,'stage_photo','test.png',?,'assets/uploads/installation/test.png',1,'image/png',2)",[$id,$s['id'],str_repeat('b',64)]);
        $w->act($tech,$id,'submit_stage',[]); $w->act($admin,$id,'approve_stage',[]);
    }
    $check($w->detail($id,$admin)['project']['status']==='completed', 'Duyệt bước sáu hoàn tất dự án');
    $check(!$w->detail($id,$admin)['crew'], 'Giải phóng lịch nhân viên khi hoàn thành');
    $reject(fn() => $w->act($admin,$id,'approve_stage',[]), 'Không sửa dự án đã hoàn tất');
    $check(UsePaginationState::make(21,2)['offset']===20, 'Phân trang hơn 20 bản ghi');
    $reject(fn() => Workflow::date('2026-02-31'), 'Từ chối ngày không tồn tại');
    ob_start(); View::component('button','<script>', ['type'=>'submit']); $html=ob_get_clean();
    $check(str_contains($html,'type="submit"') && str_contains($html,'&lt;script&gt;') && !str_contains($html,'@props'), 'Component PHP hiển thị đúng và escape dữ liệu');
    $check($w->detail($id,$admin)['logPager']['pages']>1 && count($w->detail($id,$admin)['logs'])===20, 'Nhật ký thực tế phân trang khi quá 20 dòng');
    for ($i=2;$i<=23;$i++) {
        $w->run("INSERT INTO solar_quotes (id,user_id,customer_name,customer_phone,address,monthly_electric_bill,roof_area_sqm,estimated_system_kwp,panel_count,estimated_total_cost,payback_years,status) VALUES (?,5,'Khách','090','Địa chỉ',3000000,100,7,14,100000000,4,'approved')",[$i]);
        $w->createFromApprovedQuote($i,1);
        $w->run("INSERT INTO users (username,password_hash,full_name,email,phone,role) VALUES (?,? ,?,?,'090','technician')",['extra'.$i,'unused','Extra '.$i,'extra'.$i.'@example.test']);
    }
    $listing=$w->listing($admin,'new',2);
    $check($listing['pager']['total']===22 && count($listing['projects'])===2, 'Danh sách 22 dự án trả đúng trang hai');
    $check(count($w->availability('2026-11-02','2026-11-20',2)['technicians'])===5, 'Danh sách 25 kỹ thuật viên trả đúng trang hai');
    echo "{$count} kiểm tra thành công.\n";
} finally {
    if (preg_match('/^solaris_tv4_test_[a-f0-9]{12}$/D',$database)) {
        $server->exec("DROP DATABASE IF EXISTS `{$database}`");
    }
}
