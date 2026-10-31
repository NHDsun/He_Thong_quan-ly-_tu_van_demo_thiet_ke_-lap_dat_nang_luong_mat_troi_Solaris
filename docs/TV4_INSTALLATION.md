# TV4 — Quản lý lắp đặt SOLARIS

Tài liệu giải thích chi tiết các thay đổi, chức năng và hàm xử lý: [TV4_THAY_DOI_CHUC_NANG_LUONG_CODE.md](TV4_THAY_DOI_CHUC_NANG_LUONG_CODE.md).

## Đọc code theo một chức năng

### Bổ sung giao diện khách hàng và khách chưa đăng nhập

- Khách chưa đăng nhập xem trang giới thiệu sáu giai đoạn, quyền lợi giám sát và form đăng nhập. Trang giới thiệu không cần truy vấn database.
- Tài khoản `customer` xem các dự án có `projects.user_id` bằng ID của mình, gồm dự án mới, đang thực hiện và đã hoàn tất. Tài khoản mẫu có sẵn: `khachhang.demo`, mật khẩu `Solaris@2026`.
- Trang chi tiết khách hàng dùng `customer-detail.php`, chỉ hiển thị thông tin dự án, lịch, tiến độ sáu bước và nhóm đang phụ trách. Không có upload, gửi yêu cầu, phê duyệt, nhật ký hoặc hồ sơ xử lý nội bộ.
- Hook kiểm tra quyền sở hữu khi mở chi tiết; chặn toàn bộ thao tác ghi của khách hàng ngay trong `act()`. Route download tài liệu nội bộ cũng chặn khách hàng.
- `guest.php` chứa nội dung công khai; không tra cứu dự án riêng bằng ID/mã hợp đồng khi chưa đăng nhập. Cấu trúc database không thay đổi.

Phân hệ dùng PHP thuần, PDO và JavaScript thông thường. Không cần cài framework hoặc thư viện giao diện. Đợt chỉnh sửa này giữ nguyên cấu trúc database và các chức năng; nếu đã import database trước đó thì không cần import lại.

Nên đọc theo thứ tự:

1. `index.php` chọn trang `installations`, gọi `bootstrap.php` để nạp các class và khởi tạo session.
2. `InstallationController::index()` nhận GET/POST, kiểm tra đăng nhập và chuyển thao tác cho hook. GET dùng để xem trang; POST dùng để lưu thay đổi.
3. `UseInstallationWorkflow` chứa truy vấn SQL và điều kiện nghiệp vụ. Bắt đầu bằng `act()`: đây là một `switch` theo giá trị `action` từ form.
4. `installations.blade.php` nhận mảng dữ liệu, hiển thị danh sách hoặc include trang chi tiết. Đuôi file giữ theo cấu trúc nhóm, nội dung là PHP thuần.
5. `detail.php` hiển thị tiến độ và các yêu cầu; `staff.php` hiển thị danh sách nhân viên và lịch. `atomic.php` là HTML của button, input và badge; `View.php` có các hàm hỗ trợ xuất HTML.
6. `installation.css` chỉ định giao diện. `installation.js` lưu lựa chọn nhân viên khi chuyển trang và cập nhật dòng “Đã chọn … người”.

Component được nạp bằng `require`, nên nhận biến trong phạm vi của nơi gọi. `detail.php` và `staff.php` nhận `$viewData`; `atomic.php` nhận `$name`, `$text`, `$attrs` từ `View::component()`. Chú thích `@var` đầu file giúp IDE nhận biết kiểu biến, không tạo giá trị cho biến. Các file kiểm tra dữ liệu đầu vào và trả HTTP 404 nếu mở trực tiếp, thay vì cố hiển thị khi thiếu dữ liệu.

Ví dụ: để giải thích chức năng **gửi duyệt giai đoạn**, lần theo luồng:

```text
Form trong detail.php (action = submit_stage)
    -> InstallationController::index()
    -> UseInstallationWorkflow::act(): case 'submit_stage'
    -> submitStage(): kiểm tra ảnh, cảnh báo hoãn, UPDATE trạng thái, INSERT nhật ký
    -> chuyển về trang chi tiết để đọc và hiển thị dữ liệu mới
```

Các hàm nghiệp vụ chính:

| Hàm | Công việc |
| --- | --- |
| `listing()` / `detail()` | Đọc danh sách / thông tin chi tiết dự án |
| `availability()` | Đếm dự án và tính % thời gian rảnh của nhân viên |
| `assign()` | Thêm các phân công và mở giai đoạn khảo sát |
| `uploadDocument()` | Kiểm tra file rồi lưu file và thông tin tài liệu |
| `submitStage()` | Kỹ thuật viên gửi yêu cầu duyệt giai đoạn |
| `reviewStage()` | Quản lý duyệt hoặc trả về bổ sung; mở bước tiếp theo |
| `request()` / `reviewRequest()` | Gửi / xử lý yêu cầu hoãn, thay người |
| `createFromApprovedQuote()` | Tạo dự án từ báo giá được chấp thuận |

Những khái niệm cần hiểu khi trình bày: **Session** giữ ID người đăng nhập; **PDO + dấu `?`** truyền dữ liệu an toàn vào SQL; **CSRF token** kiểm tra form thuộc phiên hiện tại; **transaction** giúp các câu UPDATE/INSERT của một thao tác cùng thành công hoặc cùng hủy. Đây là các kiểm tra cơ bản được giữ lại khi đơn giản hóa cách viết.

## Khởi chạy bằng XAMPP

1. Bật Apache và MySQL trong XAMPP, mở phpMyAdmin.
2. Import `database/tv4-installation.sql`. File tự tạo database `solaris`, charset `utf8mb4`, gồm 11 bảng năng lượng mặt trời. Không bao gồm các bài tập bán sữa. Đây là schema cho database mới; `CREATE TABLE IF NOT EXISTS` không phải migration nâng cấp bảng cũ.
3. Nếu muốn chạy thử ngay, import thêm `database/tv4-demo.sql` vào database thử nghiệm vừa tạo. File mẫu dùng ID cố định, chỉ import một lần vào database trống và không dùng cho dữ liệu thật.
4. Truy cập `http://localhost/Doan/He_Thong_quan-ly-_tu_van_demo_thiet_ke_-lap_dat_nang_luong_mat_troi_Solaris/index.php?page=installations`.
5. Đăng nhập bằng một trong các tài khoản mẫu sau, mật khẩu chung **`Solaris@2026`**:

| Tên đăng nhập | Vai trò / tình huống |
| --- | --- |
| `admin.tv4` | Quản lý; dự án mới chờ phân công |
| `kythuat.an` | Kỹ thuật viên đang làm hai dự án |
| `kythuat.binh` | Kỹ thuật viên đang làm một dự án |
| `kythuat.chi` | Kỹ thuật viên chưa có dự án |
| `kythuat.dung` | Kỹ thuật viên chưa có dự án |

SQL chỉ chứa hash mật khẩu. Đổi/xóa các tài khoản mẫu trước khi dùng dữ liệu thật. Có thể tạo lại file mẫu với `C:\xampp\php\php.exe tools/export-tv4-demo.php`.

Mặc định kết nối `127.0.0.1:3306`, database `solaris`, user `root`, mật khẩu rỗng theo XAMPP. Để đổi, sao chép `app/Modules/Member4_Installation/config.local.example.php` thành `config.local.php` và sửa các giá trị. File cấu hình riêng được Git bỏ qua. Biến môi trường `SOLARIS_DB_HOST`, `SOLARIS_DB_PORT`, `SOLARIS_DB_NAME`, `SOLARIS_DB_USER`, `SOLARIS_DB_PASSWORD` được ưu tiên hơn file cấu hình.

Trong `php.ini`, đặt `upload_max_filesize = 5M`, `post_max_size = 8M` hoặc lớn hơn, bật extension `pdo_mysql`, `fileinfo`, rồi khởi động lại Apache. Mã PHP vẫn kiểm tra giới hạn 5MB theo kích thước thật. Không sửa cấu hình XAMPP tự động.

## Phạm vi và quy tắc nghiệp vụ

- Quản lý là `admin`. Trang chính có bộ lọc dự án mới, đang làm, chờ phê duyệt, hoàn tất và tất cả. Kỹ thuật viên chỉ thấy dự án có phân công còn hiệu lực.
- Một dự án có nhiều thành viên trong `project_assignments`. `projects.technician_id` giữ người đầu tiên/trưởng nhóm để tương thích schema trước; **không dùng cột này làm điều kiện quyền truy cập**. Đây là thay đổi có chủ đích so với mục 7 của PROJECT_CONTEXT theo yêu cầu nhóm nhiều kỹ thuật viên.
- Một lần phân công chọn nhiều kỹ thuật viên, lịch chung và số giờ dự kiến mỗi người/ngày. Sáu bước được chia đều trên ngày làm việc. Lịch dự kiến cần ít nhất sáu ngày làm việc và tối đa hai năm. Chọn nhân viên được giữ khi đổi trang danh sách bằng sessionStorage; máy chủ xác minh lại tất cả ID.
- % rảnh tính theo khoảng ngày đang xem, thứ Hai–thứ Sáu, 8 giờ/ngày, không trừ lễ hay nghỉ cá nhân. Tổng giờ đặt lịch = số ngày làm việc giao nhau × giờ/ngày của từng phân công. % rảnh = max(0, 100 × (1 − tổng giờ đặt lịch / giờ khả dụng)). Khoảng chỉ có cuối tuần hiển thị không có ngày làm việc. Số dự án tính tất cả dự án đang thực hiện, không chỉ dự án giao với lịch đang xem.
- Danh sách nhân viên phân trang tối đa 20; trong mỗi trang sắp theo mức rảnh giảm dần rồi số dự án tăng dần. Lịch bên phải hiển thị từng phân công và số giờ/ngày, có thể cuộn ngang trên điện thoại. Có thể chọn người đã bận; đây là dữ liệu hỗ trợ quyết định, không phải ràng buộc chống chồng lịch.
- Nhóm mặc định tham gia toàn bộ dự án; cơ chế phân công từng người theo từng giai đoạn và thay đổi quy mô nhóm sau khi khởi công chưa nằm trong đợt này. Yêu cầu đổi người thay một thành viên bằng một người chưa có trong nhóm; quản lý duyệt thì kết thúc phân công cũ, thêm phân công mới và cập nhật trưởng nhóm nếu cần. Người bị thay mất quyền đọc/ghi/tải tài liệu ngay.
- Sáu bước bắt buộc tuần tự: `locked → in_progress → pending_approval → approved`. Nếu từ chối, quản lý bắt buộc nhập lý do và chuyển về `revision_required`. Chỉ kỹ thuật viên trong nhóm upload, đề nghị hoãn, đổi người hoặc gửi duyệt. Chỉ quản lý duyệt.
- Cần ít nhất một ảnh JPG/PNG của đúng giai đoạn trước khi gửi duyệt. Đang chờ duyệt thì khóa upload và yêu cầu mới; quản lý có thể trả về bổ sung.
- Yêu cầu hoãn là lịch đề xuất của giai đoạn hiện tại, không tự dừng công việc. Kỹ thuật viên vẫn có thể làm tiếp. Khi gửi duyệt hoàn thành lúc còn yêu cầu hoãn, bắt buộc đánh dấu xác nhận đã làm tiếp và hoàn thành. Quản lý không thể duyệt hoãn/đổi người khi giai đoạn đang chờ duyệt hoàn thành: cần duyệt hoàn thành hoặc trả về bổ sung trước.
- Duyệt hoãn cập nhật lịch giai đoạn hiện tại; dời các bước sau, ngày kết thúc dự án và lịch nhóm theo chênh lệch **ngày lịch** của ngày kết thúc cũ/mới. Không tự động bỏ cuối tuần khi dời. Nếu lịch đã trễ thực tế, quản lý cần xem lại mức rảnh trước khi duyệt; hiện chưa có lịch nghỉ lễ, tự tối ưu lịch hoặc cảnh báo vượt công suất theo từng ngày.
- Duyệt hoàn thành tự đóng các yêu cầu còn chờ của giai đoạn với trạng thái `superseded`; toàn bộ lý do và kết quả được lưu vào nhật ký. Bước 6 được duyệt thì hoàn tất dự án và giải phóng lịch nhân viên.
- Các thao tác ghi dùng transaction và khóa dòng dự án để chống duyệt hai lần/xung đột trạng thái. Mọi thay đổi trạng thái ghi vào `project_stage_logs`; thời gian thực tế từng giai đoạn nằm ở `project_stages`.

## Upload và quyền truy cập

`UseFileUpload` kiểm tra lỗi upload, kích thước thật, phần mở rộng và MIME bằng `fileinfo`; ảnh được kiểm tra `getimagesize`, DWG kiểm tra thêm chữ ký `AC10xx` do MIME Windows có thể trả `application/octet-stream`. Các file được đặt tên SHA-256 theo quy tắc của dự án. `file_hash` lưu SHA-256 **nội dung**, khác với hash dùng đặt tên file; unique `(stage_id,file_hash)` chống trùng trong cùng giai đoạn.

File nằm ở `assets/uploads/installation/`; `.htaccess` chặn truy cập trực tiếp trên Apache XAMPP. Chỉ tải/xem qua route `index.php?page=installations&document=ID` có kiểm tra quyền. Ảnh xem trước thêm `preview=1`; các định dạng khác tải dạng attachment. Nếu chuyển sang máy chủ không áp dụng `.htaccess`, phải cấu hình chặn thư mục này tương đương trước khi sử dụng. PHP built-in server chỉ dùng kiểm thử cục bộ, không cung cấp bảo vệ thư mục qua `.htaccess`.

CSRF kiểm tra tại máy chủ; truy vấn PDO prepared statements; mọi dữ liệu hiển thị được escape. Đăng nhập module kiểm tra password hash và có giới hạn thử theo session. Khi tích hợp xác thực TV1, dùng giới hạn đăng nhập chung của TV1.

## Tích hợp TV1

Hiện chưa có `UseAuthSessionState.php`. Module cung cấp form đăng nhập riêng phục vụ phát triển với users thật trong CSDL, không có nút giả lập vai trò. Sau khi TV1 đăng nhập, đặt `$_SESSION['user_id']` bằng ID người dùng; module đọc lại `role` từ CSDL cho mỗi request. Session TV1 được ưu tiên; đăng xuất qua TV1. Có thể tắt đăng nhập riêng bằng biến môi trường `SOLARIS_INSTALLATION_LOGIN=0`.

Không chỉnh sửa module TV1. Chỉ thêm bootstrap khi `page=installations` và thay placeholder TV4 trong `index.php` — điểm tích hợp tối thiểu để dispatch trước khi xuất HTML, cần cho redirect và download.

## Tích hợp TV3

Không sửa controller TV3. Khi báo giá đã được quản lý chấp thuận và transaction báo giá đã commit, TV3 gọi:

```php
require_once __DIR__ . '/../../Hooks/UsePaginationState.php';
require_once __DIR__ . '/../../Hooks/UseFileUpload.php';
require_once __DIR__ . '/../../Hooks/UseInstallationWorkflow.php';

$projectId = (new \App\Hooks\UseInstallationWorkflow($pdo))
    ->createFromApprovedQuote($quoteId, $adminId);
```

Đoạn require minh họa đặt trong module TV3. `$pdo` là kết nối PDO dùng chung, `$adminId` là ID admin đã xác thực phía máy chủ, không lấy từ form. Báo giá bắt buộc `status=approved` và `user_id` hợp lệ. Cùng một quote gọi lại trả về cùng ID dự án nhờ khóa và unique `quote_id`; khởi tạo đủ sáu giai đoạn cùng transaction. Mã hợp đồng `SOLAR-YYYY-XXX` dùng ID báo giá đệm ít nhất ba chữ số. Hook tự quản lý transaction, không gọi trong transaction đang mở.

TV3 nên gọi điểm tích hợp như một phần của luồng phê duyệt, có cơ chế thử lại nếu tạo dự án thất bại sau khi báo giá đã commit. Có thể bọc bằng điều phối transaction chung trong tương lai nếu cần tính nguyên tử toàn luồng giữa hai module.

## Kiến trúc và giao diện

`UseInstallationWorkflow`, `UseFileUpload`, `UsePaginationState` → `InstallationController` → page view → components. CSS riêng `resources/css/installation.css` kế thừa các biến thực tế của `solaris-theme.css`; không sửa file theme hay code TV2. Trang chi tiết chia 75/25 trên desktop, một cột trên mobile; bảng chọn nhân viên chia 20/80, dòng nhân viên và lịch đồng bộ.

Repository có template Blade nhưng chưa có Composer/Blade engine. TV4 dùng `require` để hiển thị component PHP trong `components/installation/atomic.php`, không dùng `eval`, không tự dịch Blade và không có lớp mô phỏng `$attributes`. Component PHP dùng cùng class CSS và biến thể với Design System. Đây là bản tương thích PHP thuần trong phạm vi TV4; không sửa hay trực tiếp render các template Blade của TV2. Khi nhóm tích hợp Blade đầy đủ, có thể chuyển sang renderer và component chung. Các page TV4 vẫn giữ đuôi `.blade.php` theo cấu trúc thư mục nhóm.

Danh sách dự án, nhân viên, tài liệu, lịch sử yêu cầu và nhật ký dùng `UsePaginationState`, tối đa 20 bản ghi/trang. Yêu cầu chờ tối đa hai loại/giai đoạn, không có phân trang vì luôn ít hơn 20. Danh sách nhóm thành viên là thông tin của một dự án, không phải bảng dữ liệu quản trị riêng.

## Kiểm thử

```powershell
C:\xampp\php\php.exe tests/tv4-workflow.php
$testDatabase = C:\xampp\php\php.exe tests/tv4-http.php setup
$env:SOLARIS_DB_NAME = $testDatabase
C:\xampp\php\php.exe -S 127.0.0.1:8765 -t .
# Trong terminal khác:
C:\xampp\php\php.exe tests/tv4-http.php
# Dừng máy chủ thử nghiệm rồi dọn dữ liệu:
C:\xampp\php\php.exe tests/tv4-http.php cleanup
Remove-Item Env:SOLARIS_DB_NAME
```

Test nghiệp vụ dùng database `solaris_tv4_test_<random>` riêng và xóa trong finally; không sửa database `solaris`. Test HTTP dùng `solaris_tv4_http_<random>` riêng, trạng thái nằm ở file `.tmp` được Git bỏ qua, cleanup xóa file upload do test tạo rồi xóa database đó. Test HTTP là luồng một lần trên fixture mới; muốn chạy lại, cleanup rồi setup lại. Chỉ dùng tài khoản MySQL có quyền tạo/xóa database thử nghiệm.

Kiểm thử bao gồm tạo dự án chống trùng, nhóm nhiều người, RBAC, chuyển người và thu hồi quyền, tính mức rảnh, hoãn và dời lịch, xác nhận khi gửi duyệt lúc có hoãn, trả bổ sung, hoàn tất tuần tự, phân trang, CSRF, đăng nhập, multipart upload, file giả ảnh, chống trùng nội dung và quyền tải tài liệu. Không có tra cứu công khai, đăng ký tư vấn hoặc Bài 19–24 trong phạm vi này.
