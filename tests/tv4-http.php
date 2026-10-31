<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$state = __DIR__ . '/.tv4-http.tmp';
$mode = $argv[1] ?? 'test';
$db = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', getenv('SOLARIS_DB_USER') ?: 'root', getenv('SOLARIS_DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if ($mode === 'setup') {
    if (is_file($state)) { throw new RuntimeException('Hãy cleanup phiên thử nghiệm trước.'); }
    $name = 'solaris_tv4_http_' . bin2hex(random_bytes(6));
    $schema = file_get_contents(dirname(__DIR__) . '/database/tv4-installation.sql');
    $db->exec(str_replace(['CREATE DATABASE IF NOT EXISTS solaris ', 'USE solaris;'], ["CREATE DATABASE IF NOT EXISTS {$name} ", "USE {$name};"], $schema));
    $db->exec(str_replace('USE solaris;', "USE {$name};", file_get_contents(dirname(__DIR__) . '/database/tv4-demo.sql')));
    file_put_contents($state,$name);
    echo $name;
    exit;
}
$name = trim(file_get_contents($state));
if (!preg_match('/^solaris_tv4_http_[a-f0-9]{12}$/D',$name)) { throw new RuntimeException('Invalid test database.'); }
$db->exec("USE `{$name}`");
if ($mode === 'cleanup') {
    foreach ($db->query('SELECT file_path FROM project_documents')->fetchAll(PDO::FETCH_COLUMN) as $path) {
        $root = realpath(dirname(__DIR__) . '/assets/uploads/installation');
        $file = realpath(dirname(__DIR__) . '/' . $path);
        if ($root && $file && str_starts_with($file,$root . DIRECTORY_SEPARATOR)) { unlink($file); }
    }
    $db->exec("DROP DATABASE `{$name}`");
    unlink($state);
    exit("Đã xóa dữ liệu thử nghiệm.\n");
}
$url = 'http://127.0.0.1:8765/index.php?page=installations';
$cookies = tempnam(sys_get_temp_dir(),'tv4cookies');
$curl = curl_init();
curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>$cookies,CURLOPT_COOKIEJAR=>$cookies,CURLOPT_TIMEOUT=>15]);
$http = static function (string $suffix='', ?array $data=null) use($curl,$url): array {
    curl_setopt($curl,CURLOPT_URL,$url.$suffix);
    curl_setopt($curl,CURLOPT_POST,$data!==null);
    if ($data!==null) { curl_setopt($curl,CURLOPT_POSTFIELDS,$data); }
    $body=curl_exec($curl);
    if ($body===false) { throw new RuntimeException(curl_error($curl)); }
    if (str_contains($body,'Fatal error') || str_contains($body,'Warning:')) { throw new RuntimeException('PHP error: '.$body); }
    return [curl_getinfo($curl,CURLINFO_HTTP_CODE),$body];
};
$check = static function(bool $ok,string $message):void {if(!$ok)throw new RuntimeException('FAIL: '.$message); echo "PASS HTTP: {$message}\n";};
$csrf = static function(string $html):string {preg_match('/name="csrf" value="([a-f0-9]+)"/',$html,$match);return $match[1]??'';};
$png = tempnam(sys_get_temp_dir(),'tv4png');
$bad = tempnam(sys_get_temp_dir(),'tv4bad');
$large = tempnam(sys_get_temp_dir(),'tv4large');
file_put_contents($png,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jwWQAAAAASUVORK5CYII='));
file_put_contents($bad,'<?php echo "bad";');
file_put_contents($large,str_repeat('x',5*1024*1024+1));
try {
    [$code,$body]=$http(); $check($code===200 && str_contains($body,'Đăng nhập theo dõi dự án') && str_contains($body,'Quyền lợi khi theo dõi dự án'),'Khách chưa đăng nhập xem quy trình, quyền lợi và form đăng nhập');
    $token=$csrf($body);
    [$code,$body]=$http('', ['action'=>'login','csrf'=>'wrong','username'=>'admin.tv4','password'=>'Solaris@2026']);
    $check(str_contains($body,'Phiên biểu mẫu hết hạn'),'Chặn CSRF sai');
    [$code]=$http('', ['action'=>'login','csrf'=>$token,'username'=>'admin.tv4','password'=>'Solaris@2026']);
    $check($code===303,'Đăng nhập quản lý và chuyển hướng');
    [$code,$body]=$http('&project=1'); $token=$csrf($body);
    $check($code===200 && str_contains($body,'Phân công &amp; bắt đầu dự án') && str_contains($body,'tv4-booking'),'Render chi tiết và lịch phân công');
    [$code]=$http('&project=1',['action'=>'assign','csrf'=>$token,'start_date'=>'2026-11-02','end_date'=>'2026-11-20','technicians[0]'=>'4','technicians[1]'=>'5','hours_per_day'=>'4']);
    $check($code===303,'Gửi form phân công nhóm');
    [$code,$body]=$http('&project=1');
    $check(str_contains($body,'Phạm Ngọc Chi') && str_contains($body,'Võ Anh Dũng'),'Hiển thị nhóm vừa phân công');
    $http('', ['action'=>'logout','csrf'=>$csrf($body)]);
    [, $body]=$http();
    $http('', ['action'=>'login','csrf'=>$csrf($body),'username'=>'kythuat.an','password'=>'Solaris@2026']);
    [, $body]=$http('&project=1');
    $check(str_contains($body,'không còn được phân công'),'Chặn truy cập dự án người khác');
    [, $body]=$http('&project=2'); $token=$csrf($body);
    $check(str_contains($body,'Yêu cầu duyệt giai đoạn') && !str_contains($body,'Duyệt &amp; mở bước tiếp theo'),'Đúng thao tác kỹ thuật viên');
    $http('&project=2',['action'=>'upload','csrf'=>$token,'document_type'=>'stage_photo','document'=>new CURLFile($bad,'image/png','fake.png')]);
    [, $body]=$http('&project=2');
    $check(str_contains($body,'đúng định dạng'),'Từ chối file giả ảnh');
    $http('&project=2',['action'=>'upload','csrf'=>$token,'document_type'=>'stage_photo','document'=>new CURLFile($large,'image/png','large.png')]);
    [, $body]=$http('&project=2');
    $check(str_contains($body,'5MB'),'Từ chối file vượt 5MB');
    $http('&project=2',['action'=>'upload','csrf'=>$token,'document_type'=>'stage_photo','document'=>new CURLFile($png,'image/png','evidence.png')]);
    [, $body]=$http('&project=2');
    $check(str_contains($body,'evidence.png'),'Upload ảnh qua multipart thực tế');
    $id=(int)$db->query('SELECT MAX(id) FROM project_documents')->fetchColumn();
    [$code,$body]=$http('&document='.$id.'&preview=1');
    $check($code===200 && str_starts_with($body,"\x89PNG"),'Xem ảnh qua route kiểm tra quyền');
    $http('&project=2',['action'=>'upload','csrf'=>$token,'document_type'=>'stage_photo','document'=>new CURLFile($png,'image/png','same.png')]);
    [, $body]=$http('&project=2');
    $check(str_contains($body,'cùng nội dung'),'Chống trùng theo hash nội dung');
    $http('&project=2',['action'=>'submit_stage','csrf'=>$token]);
    [, $body]=$http('&project=2');
    $check(str_contains($body,'Đã gửi minh chứng'),'Gửi duyệt giai đoạn qua HTTP');
    $http('', ['action'=>'logout','csrf'=>$csrf($body)]);
    [, $body]=$http();
    $http('', ['action'=>'login','csrf'=>$csrf($body),'username'=>'kythuat.dung','password'=>'Solaris@2026']);
    [, $body]=$http('&document='.$id);
    $check(str_contains($body,'không còn được phân công'),'Chặn tải tài liệu dự án người khác');
    $http('', ['action'=>'logout','csrf'=>$csrf($body)]);
    [, $body]=$http();
    [$code]=$http('', ['action'=>'login','csrf'=>$csrf($body),'username'=>'khachhang.demo','password'=>'Solaris@2026']);
    $check($code===303,'Đăng nhập bằng tài khoản khách hàng');
    [, $body]=$http();
    $check(str_contains($body,'Dự án của tôi') && str_contains($body,'SOLAR-2026-001') && str_contains($body,'SOLAR-2026-003'),'Khách hàng xem danh sách dự án của mình');
    [, $body]=$http('&project=2');
    $token=$csrf($body);
    $check(str_contains($body,'customer-stage-6') && !str_contains($body,'name="document"') && !str_contains($body,'Yêu cầu duyệt giai đoạn') && !str_contains($body,'Nhật ký dự án'),'Chi tiết khách hàng có sáu bước và không có điều khiển nội bộ');
    $before=$db->query('SELECT status FROM project_stages WHERE id=7')->fetchColumn();
    foreach (['assign','upload','submit_stage','approve_stage','reject_stage','postpone','replacement','approve_request','reject_request'] as $action) {
        $http('&project=2',['action'=>$action,'csrf'=>$token]);
        [, $body]=$http('&project=2');
        $check(str_contains($body,'chỉ có quyền xem'),'Máy chủ từ chối POST khách hàng: '.$action);
    }
    $check($db->query('SELECT status FROM project_stages WHERE id=7')->fetchColumn()===$before,'POST giả không đổi trạng thái giai đoạn');
    [, $body]=$http('&document='.$id);
    $check(str_contains($body,'khu vực xử lý nội bộ'),'Khách hàng không tải hồ sơ nội bộ bằng URL trực tiếp');
    $db->exec("INSERT INTO projects (contract_code,user_id,project_name,capacity_kwp,total_amount,installation_address) VALUES ('PRIVATE-OTHER',1,'Du an rieng',5,50000000,'Dia chi rieng')");
    $otherId=(int)$db->lastInsertId();
    [, $body]=$http();
    $check(!str_contains($body,'PRIVATE-OTHER'),'Danh sách khách hàng không lộ dự án chủ sở hữu khác');
    [, $body]=$http('&project='.$otherId);
    $check(str_contains($body,'không có quyền xem') && !str_contains($body,'Dia chi rieng'),'Chặn IDOR qua URL chi tiết khách hàng');
    echo "Kiểm tra HTTP hoàn thành. Chạy cleanup sau khi kiểm tra trình duyệt.\n";
} finally { curl_close($curl); unlink($cookies); unlink($png); unlink($bad); unlink($large); }
