# Quy trình đăng ký và triển khai dự án

Cập nhật ngày 04/11/2026. Nhân viên không cần xác nhận nhận việc: admin phân công thành công là dự án bắt đầu.

## Trạng thái

| Đối tượng | Trạng thái |
| --- | --- |
| Phương án / báo giá | `draft`, `pending_approval`, `approved`, `rejected` |
| Dự án | `pending_approval`, `rejected`, `new`, `in_progress`, `completed`, `cancelled` |
| Giai đoạn | `locked`, `in_progress`, `pending_approval`, `revision_required`, `approved` |

`approved` chỉ dùng cho phương án và giai đoạn. Dự án được duyệt mang trạng thái `new` (chờ phân công). `projects.current_stage` là NULL trước khi phân công. Hủy dự án giữ lại giai đoạn đang làm để tra cứu lịch sử.

## Hai đường đăng ký

1. **Khảo sát trực tiếp:** khách hàng đăng nhập → demo theo hóa đơn → đăng ký khảo sát → tạo dự án `pending_approval` và sáu giai đoạn `locked`. Phương án giữ `draft` vì chưa phải báo giá được quản lý chấp thuận.
2. **Kế hoạch dự định:** chọn thiết bị → demo theo kế hoạch → gửi phương án `pending_approval` → admin duyệt phương án → tạo dự án `pending_approval` và sáu giai đoạn `locked`.

Cả hai tiếp tục: admin duyệt triển khai → `new` → chọn nhân viên, khoảng ngày và giờ/ngày → `in_progress`, mở khảo sát ngay. Hai lần duyệt của đường kế hoạch có mục đích riêng: duyệt cấu hình/dự toán và duyệt tiếp nhận triển khai.

Nút **Lưu dự toán nháp** chỉ lưu phương án. Khách có thể gửi dự toán đã lưu từ danh sách; hệ thống dùng nguồn `source` đã lưu để chọn đúng đường xử lý. Mỗi báo giá chỉ liên kết tối đa một dự án qua UNIQUE `projects.quote_id`. `request_key` duy nhất ngăn gửi lại biểu mẫu tạo trùng.

## Phân quyền và thao tác

- Khách hàng dùng trang Tư vấn & Demo để gửi yêu cầu, xem phương án và dự án của mình. Người chưa đăng nhập hoặc tài khoản nội bộ không được đăng ký thay khách.
- Admin dùng liên kết **Duyệt phương án từ kế hoạch dự định** ở trang lắp đặt; duyệt/từ chối phương án tại danh sách trên trang tư vấn. Duyệt phương án không tự duyệt triển khai.
- Admin dùng bộ lọc **Yêu cầu chờ duyệt** để duyệt hoặc từ chối dự án, sau đó phân công tại **Dự án mới**. Bộ lọc **Cần xử lý** gồm yêu cầu dự án, giai đoạn và yêu cầu phát sinh còn chờ xử lý của dự án đang làm.
- Chỉ kỹ thuật viên còn được phân công mới được upload, gửi duyệt hoặc đề nghị hoãn/đổi người. Không có bước nhận lời mời tham gia.
- Từ chối phương án, từ chối dự án, hủy dự án và trả giai đoạn về bổ sung đều cần lý do. Khách hàng xem được phản hồi duyệt dự án và lý do hủy.

Sáu giai đoạn: khảo sát → thiết kế → mua sắm → thi công → kiểm thử/nối lưới → bàn giao/nghiệm thu. Mỗi bước cần ít nhất một ảnh JPG/PNG để gửi duyệt. Admin duyệt mở bước kế tiếp; yêu cầu bổ sung đưa bước hiện tại về `revision_required`. Duyệt bước cuối hoàn tất dự án và kết thúc phân công. Hủy dự án kết thúc phân công, đóng yêu cầu phát sinh nhưng giữ nhật ký và hồ sơ.

## Vật tư phát sinh, chi phí và hóa đơn

Các chức năng từ `quanLyLapDat04` được tích hợp cùng quy trình duyệt hiện tại:

- Trong chi tiết dự án, admin hoặc kỹ thuật viên được phân công mở **Phát sinh vật tư** ở bước khảo sát. Nếu bước này đã hoàn thành, mở thẻ khảo sát hoặc nhấn mốc khảo sát trên timeline để xem nút. Popup hỗ trợ tìm tên không dấu và lọc loại vật tư.
- Chỉ thêm khi khảo sát, thiết kế hoặc mua sắm đang thực hiện/cần bổ sung; bước khảo sát phải được duyệt trước khi thêm ở các bước sau. Giá và tên vật tư được lưu theo danh mục tại thời điểm thêm. Vật tư phát sinh là dự toán, chưa xuất kho.
- Tổng phải trả = giá trị ban đầu + vật tư phát sinh. Vật tư có sẵn trong giá trị ban đầu không được cộng lần thứ hai. Có phân trang vật tư và lịch sử thanh toán khi vượt 20 bản ghi.
- Chỉ khách hàng sở hữu được **Thanh toán mô phỏng** khi dự án `in_progress` hoặc `completed`. Không thu tiền thật; chặn thanh toán khi chờ duyệt, chờ phân công, bị từ chối hoặc đã hủy. Nếu có phát sinh sau khi đã thanh toán, hệ thống tính số dư mới. Gửi lại cùng số dư đã thanh toán không tạo giao dịch trùng.
- **In hóa đơn** mở trang riêng từ khi bước mua sắm được mở. Nút in gọi hộp thoại in của trình duyệt; có thể chọn lưu PDF. Bản in là chứng từ mô phỏng và dùng số liệu tại thời điểm mở trang.
- Admin chỉ duyệt hoàn thành bước **Phê duyệt & mua sắm vật tư** khi số dư còn phải trả bằng 0, gồm cả vật tư phát sinh. Khi còn thiếu, nút duyệt bị khóa và hiển thị số tiền còn thiếu; server cũng kiểm tra lại trong transaction trước khi mở bước thi công. Admin vẫn có thể yêu cầu bổ sung hồ sơ khi chưa thanh toán đủ.
- Giai đoạn đã hoàn thành thu gọn bằng thẻ mở/đóng; giai đoạn hiện tại được nhấn mạnh. CSS/JS dùng phiên bản theo thời gian sửa file để tránh tải lại tài nguyên cũ từ cache.

## Database và cập nhật

Cài mới dùng `database/solaris_schema.sql` rồi `database/solaris_seed.sql`, hoặc `database/solaris_full.sql`. Không import lại schema/seed để cập nhật database đang sử dụng vì các file này có lệnh xóa dữ liệu.

Với database đang có dữ liệu:

```powershell
php tools/migrate-project-workflow.php
```

Script dùng cấu hình chung `App\Config\Database`, lưu bản sao cấu trúc và dữ liệu các bảng liên quan vào thư mục `solaris-workflow-backups` trong thư mục tạm của hệ điều hành, rồi chạy migration. Đường dẫn bản sao được in khi chạy. Bảng `schema_migrations` ngăn áp dụng lại cùng migration.

- `legacy_status` lưu trạng thái cũ trước chuyển đổi, kể cả trạng thái rỗng do ENUM cũ không nhận `pending_approval`.
- Báo giá có trạng thái liên hệ cũ được đưa về `pending_approval`, không tự phê duyệt.
- Dự án đã có phân công, tài liệu hoặc dấu vết thực hiện được giữ ở luồng đang làm. Dự án cũ `approved` chưa triển khai chuyển `new`; trạng thái không hợp lệ chưa triển khai chuyển `pending_approval`.
- Giữ các dự án `completed`, `cancelled`, `rejected` và nhật ký hiện có.
- `solar_quote_logs` lưu lịch sử phương án trước khi có dự án; `project_stage_logs.stage` cho phép NULL đối với sự kiện cấp dự án.
- Migration `20261104_project_billing` chỉ tạo thêm `project_extra_materials` và `project_payments`, không nhập lại dữ liệu mẫu. Cùng lệnh migration sẽ áp dụng các phiên bản còn thiếu.

## Kiểm tra

```powershell
php tests/project-workflow.php
php tests/tv4-billing.php
node tests/tv4-dialog.cjs
python tests/workflow-http.py
```

Các kiểm tra tạo database tạm với tên riêng, tự xóa sau khi chạy. HTTP test khởi chạy PHP server trên cổng trống, đăng nhập bằng tài khoản kiểm thử, kiểm tra cả hai đường đăng ký, CSRF, quyền truy cập và upload PNG thật; file upload kiểm thử được xóa khi kết thúc. Cần PHP CLI, MySQL/MariaDB và Python 3 cho HTTP test. Có thể đặt `PHP_BINARY` nếu PHP chưa nằm trong PATH.
