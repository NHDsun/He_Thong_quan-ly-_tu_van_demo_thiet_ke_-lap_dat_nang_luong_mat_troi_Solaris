<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$hash = password_hash('Solaris@2026', PASSWORD_DEFAULT);
$sql = "-- Dữ liệu mẫu tùy chọn. Chỉ import vào database thử nghiệm trống sau tv4-installation.sql.\nUSE solaris;\nSTART TRANSACTION;\n";
$users = [
    [1, 'admin.tv4', 'Nguyễn Minh Quản', 'admin'],
    [2, 'kythuat.an', 'Trần Hoàng An', 'technician'],
    [3, 'kythuat.binh', 'Lê Thanh Bình', 'technician'],
    [4, 'kythuat.chi', 'Phạm Ngọc Chi', 'technician'],
    [5, 'kythuat.dung', 'Võ Anh Dũng', 'technician'],
    [6, 'khachhang.demo', 'Nguyễn Văn Hải', 'customer'],
];
foreach ($users as [$id, $username, $name, $role]) {
    $sql .= "INSERT INTO users (id,username,password_hash,full_name,email,phone,role) VALUES ({$id},'{$username}','{$hash}','{$name}','{$username}@example.test','0900000000','{$role}');\n";
}
for ($i = 1; $i <= 3; $i++) {
    $sql .= "INSERT INTO solar_quotes (id,user_id,customer_name,customer_phone,address,monthly_electric_bill,roof_area_sqm,estimated_system_kwp,panel_count,estimated_total_cost,payback_years,status) VALUES ({$i},6,'Nguyễn Văn Hải','0900000000','Thành phố Hồ Chí Minh',3000000,100,7.7,14,110000000,4,'approved');\n";
}
$start = date('Y-m-d', strtotime('monday this week'));
$end = date('Y-m-d', strtotime($start . ' +25 days'));
$names = [1 => 'Điện mặt trời biệt thự Thảo Điền', 2 => 'Hệ thống mái nhà Bình Chánh', 3 => 'Điện mặt trời văn phòng Thủ Đức'];
foreach ($names as $id => $name) {
    $status = $id === 1 ? 'new' : 'in_progress';
    $tech = $id === 1 ? 'NULL' : '2';
    $begin = $id === 1 ? 'NULL' : "'{$start}'";
    $finish = $id === 1 ? 'NULL' : "'{$end}'";
    $sql .= "INSERT INTO projects (id,contract_code,quote_id,user_id,technician_id,project_name,capacity_kwp,total_amount,installation_address,status,start_date,planned_end_date) VALUES ({$id},'SOLAR-" . date('Y') . "-00{$id}',{$id},6,{$tech},'{$name}',7.7,110000000,'Thành phố Hồ Chí Minh','{$status}',{$begin},{$finish});\n";
    $keys = ['survey','design','procurement','installation','grid_connection','handover'];
    foreach ($keys as $position => $stage) {
        $state = $id !== 1 && $position === 0 ? 'in_progress' : 'locked';
        $stageStart = $id === 1 ? 'NULL' : "'" . date('Y-m-d', strtotime($start . ' +' . ($position * 4) . ' days')) . "'";
        $stageEnd = $id === 1 ? 'NULL' : "'" . date('Y-m-d', strtotime($start . ' +' . ($position * 4 + ($position === 5 ? 5 : 3)) . ' days')) . "'";
        $sql .= "INSERT INTO project_stages (project_id,stage,position,status,planned_start,planned_end,started_at) VALUES ({$id},'{$stage}'," . ($position + 1) . ",'{$state}',{$stageStart},{$stageEnd}," . ($state === 'in_progress' ? 'NOW()' : 'NULL') . ");\n";
    }
    $sql .= "INSERT INTO project_stage_logs (project_id,stage,started_at,actor_id,action,to_status,note) VALUES ({$id},'survey',NOW(),1,'project_created','{$status}','Dữ liệu minh họa');\n";
    if ($id !== 1) {
        foreach (($id === 2 ? [2, 3] : [2]) as $technician) {
            $sql .= "INSERT INTO project_assignments (project_id,technician_id,planned_start,planned_end,hours_per_day,assigned_by) VALUES ({$id},{$technician},'{$start}','{$end}',4,1);\n";
        }
    }
}
$sql .= "COMMIT;\n";
file_put_contents(dirname(__DIR__) . '/database/tv4-demo.sql', $sql);
echo "Đã tạo database/tv4-demo.sql. Mật khẩu thử nghiệm: Solaris@2026\n";
