# Quy trình đăng ký và triển khai dự án

Cập nhật ngày 04/11/2026. Sau phân công, dự án chờ nhân viên nhấn bắt đầu thực thi. Mỗi bước có lịch riêng do nhân viên đề xuất và admin duyệt; không chia đều sáu bước theo lịch ban đầu.

## Trạng thái

| Đối tượng | Trạng thái |
| --- | --- |
| Phương án / báo giá | `draft`, `pending_approval`, `approved`, `rejected` |
| Dự án | `pending_approval`, `rejected`, `new`, `scheduled`, `in_progress`, `completed`, `cancelled` |
| Giai đoạn | `locked`, `scheduled`, `in_progress`, `pending_approval`, `revision_required`, `approved` |

`approved` chỉ dùng cho phương án và giai đoạn. Dự án được duyệt mang trạng thái `new` (chờ phân công). `projects.current_stage` là NULL trước khi phân công. Hủy dự án giữ lại giai đoạn đang làm để tra cứu lịch sử.

## Hai đường đăng ký

1. **Khảo sát trực tiếp:** khách hàng đăng nhập → demo theo hóa đơn → đăng ký khảo sát → tạo dự án `pending_approval` và sáu giai đoạn `locked`. Phương án giữ `draft` vì chưa phải báo giá được quản lý chấp thuận.
2. **Kế hoạch dự định:** chọn thiết bị → demo theo kế hoạch → gửi phương án `pending_approval` → admin duyệt phương án → tạo dự án `pending_approval` và sáu giai đoạn `locked`.

Cả hai tiếp tục: admin duyệt triển khai → `new` → chọn nhân viên, khoảng ngày tham khảo và giờ/ngày → `scheduled` (chờ bắt đầu), lên lịch khảo sát. Nhân viên nhấn bắt đầu khảo sát (có thể sớm hơn lịch dự kiến) → `in_progress`, ghi `projects.started_at` và `project_stages.started_at`. Hai lần duyệt của đường kế hoạch có mục đích riêng: duyệt cấu hình/dự toán và duyệt tiếp nhận triển khai.

Nút **Lưu dự toán nháp** chỉ lưu phương án. Khách có thể gửi dự toán đã lưu từ danh sách; hệ thống dùng nguồn `source` đã lưu để chọn đúng đường xử lý. Mỗi báo giá chỉ liên kết tối đa một dự án qua UNIQUE `projects.quote_id`. `request_key` duy nhất ngăn gửi lại biểu mẫu tạo trùng.

## Phân quyền và thao tác

- Khách hàng dùng trang Tư vấn & Demo để gửi yêu cầu, xem phương án và dự án của mình. Người chưa đăng nhập hoặc tài khoản nội bộ không được đăng ký thay khách.
- Admin dùng liên kết **Duyệt phương án từ kế hoạch dự định** ở trang lắp đặt; duyệt/từ chối phương án tại danh sách trên trang tư vấn. Duyệt phương án không tự duyệt triển khai.
- Admin dùng bộ lọc **Yêu cầu chờ duyệt** để duyệt hoặc từ chối dự án, sau đó phân công tại **Dự án mới**. Bộ lọc **Cần xử lý** gồm yêu cầu dự án, giai đoạn và yêu cầu phát sinh còn chờ xử lý của dự án đang làm.
- Chỉ kỹ thuật viên còn được phân công mới được upload, gửi duyệt hoặc đề nghị hoãn/đổi người. Không có bước nhận lời mời tham gia riêng. Nhân viên nhấn bắt đầu thực thi để ghi nhận giờ bắt đầu.
- Từ chối phương án, từ chối dự án, hủy dự án và trả giai đoạn về bổ sung đều cần lý do. Khách hàng xem được phản hồi duyệt dự án và lý do hủy.

Sáu giai đoạn: khảo sát → thiết kế → mua sắm → thi công → kiểm thử/nối lưới → bàn giao/nghiệm thu. Mỗi bước cần ít nhất một ảnh JPG/PNG để gửi duyệt. Khi gửi duyệt năm bước đầu, nhân viên phải chọn ngày thực thi bước kế tiếp từ hôm nay trở đi. Admin duyệt đồng thời hoàn thành bước hiện tại và lên lịch bước tiếp theo (`scheduled`, chưa ghi giờ bắt đầu). Nhân viên có thể bắt đầu sớm hơn ngày đã duyệt; lịch dự kiến được giữ và giờ thực tế được ghi riêng. Yêu cầu bổ sung đưa bước hiện tại về `revision_required`; giờ bắt đầu ban đầu được giữ nguyên. Duyệt bước cuối hoàn tất dự án và kết thúc phân công. Hủy dự án kết thúc phân công, đóng yêu cầu phát sinh nhưng giữ nhật ký và hồ sơ.

## Lịch dự kiến và thời gian thực tế

- Admin giữ khoảng ngày tổng thể làm mốc tham khảo và để tính tải phân công; không yêu cầu tối thiểu sáu ngày làm việc. Khoảng lịch phải tăng dần và không quá hai năm.
- Khi phân công, chỉ khảo sát có `planned_start`; cả năm bước sau chưa có ngày. `projects.start_date` là ngày khảo sát dự kiến, `projects.started_at` là giờ bắt đầu thực tế do nút bắt đầu ghi lại.
- Mỗi giai đoạn có trạng thái `scheduled` giữa khóa và thực hiện. Chỉ kỹ thuật viên còn được phân công được bắt đầu sau khi mọi bước trước đã được duyệt. Có thể thực hiện sớm hơn `planned_start`; ngày này là mốc dự kiến, không phải điều kiện khóa thao tác.
- Nhân viên đủ minh chứng chọn `next_start_date` khi gửi duyệt. Lưu trên bước hiện tại tại `proposed_next_start`; chỉ khi admin duyệt mới ghi sang `planned_start` của bước kế tiếp. Ngày cuối tuần được phép; không ép theo cách chia ngày làm việc cũ.
- Không tự ghi giờ bắt đầu bước kế tiếp khi admin duyệt. Bước cuối không yêu cầu ngày kế tiếp; duyệt bàn giao vẫn ghi giờ hoàn tất dự án.
- Khi lịch được duyệt vượt mốc kết thúc ban đầu, hệ thống kéo dài ngày kết thúc dự kiến và lịch giữ chỗ của nhóm tới ngày đó; không tự phân bổ lại các bước còn lại. Thống kê mức rảnh vẫn dùng cơ sở thứ Hai–thứ Sáu, tám giờ/ngày.
- Hoãn chỉ cập nhật lịch bước hiện tại (ngày kết thúc là tùy chọn), không đẩy dây chuyền các bước sau. Ngày thực thi bước sau vẫn được đề xuất khi gửi duyệt hoàn thành.

## Dashboard trang chính quản lý lắp đặt

- Admin có tám card: tổng dự án, yêu cầu dự án chờ duyệt, chờ phân công, dự án chờ bắt đầu, dự án đang thực hiện, dự án hoàn tất, giai đoạn chờ duyệt và yêu cầu hoãn/đổi người đang chờ xử lý.
- Kỹ thuật viên có sáu card: dự án còn được phân công, giai đoạn chờ bắt đầu, giai đoạn đang thực hiện, cần bổ sung hồ sơ, giai đoạn chờ duyệt và yêu cầu do chính mình gửi còn chờ xử lý. Các số liệu chỉ thuộc dự án người đó đang được phân công, không đếm trùng khi có nhiều dòng phân công.
- Card luôn phản ánh toàn phạm vi của vai trò, không thay đổi theo bộ lọc hoặc trang danh sách. Một bước kế tiếp đang chờ bắt đầu vẫn được tính dù dự án tổng thể đang thực hiện. Các yêu cầu/giai đoạn còn treo trên dự án đã kết thúc không được tính vào số việc cần xử lý.
- Dashboard chỉ xuất hiện trên danh sách chính của admin/kỹ thuật viên, không xuất hiện ở trang chi tiết hoặc giao diện khách hàng. Không cần thay đổi database.

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

### Sản phẩm ban đầu từ Kế hoạch dự định

- Khi khách lưu hoặc gửi phương án từ kế hoạch, `solar_quote_items` giữ mã sản phẩm, tên, số lượng và đơn giá từ danh sách thiết bị đang mô phỏng. Khách sửa giỏ hàng hoặc danh mục đổi giá sau đó không thay đổi phương án đã lưu.
- Duyệt phương án tạo yêu cầu dự án chờ duyệt. Khi admin duyệt triển khai thành **Dự án mới**, hệ thống sao chép sản phẩm sang `installation_items` trong cùng transaction; gửi lại thao tác không tạo trùng.
- Hóa đơn đọc tên, số lượng và giá đã lưu của sản phẩm ban đầu. Tổng tiền dự án và cách tính phát sinh được giữ nguyên; phần dự toán ngoài các sản phẩm đã chọn vẫn thể hiện bằng chênh lệch dự toán.
- Phạm vi này chỉ áp dụng cho kế hoạch dự định. Phương án cũ thiếu danh sách sản phẩm cần được khách gửi lại trước khi duyệt triển khai; hệ thống không tự suy đoán thiết bị. Dự án cũ đã có chi tiết sản phẩm được giữ nguyên.
- Migration `20261105_plan_quote_items` tạo bảng chi tiết phương án và thêm tên sản phẩm lưu riêng vào `installation_items`. Với chi tiết dự án đã có, tên được lấy từ danh mục hiện tại khi nâng cấp.

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
- Migration `20261104_stage_schedule` thêm trạng thái chờ bắt đầu, giờ thực tế cấp dự án và ngày đề xuất bước kế tiếp. Giữ nguyên ngày và trạng thái các bước đã có; chỉ suy ra giờ bắt đầu dự án cũ từ giờ bắt đầu giai đoạn đã lưu. Hồ sơ cũ đang chờ duyệt nhưng chưa có ngày kế tiếp cần được trả về bổ sung rồi gửi lại.
- Migration `20261104_project_billing` chỉ tạo thêm `project_extra_materials` và `project_payments`, không nhập lại dữ liệu mẫu. Cùng lệnh migration sẽ áp dụng các phiên bản còn thiếu.

## Kiểm tra

```powershell
php tests/project-workflow.php
php tests/tv4-billing.php
node tests/tv4-dialog.cjs
python tests/workflow-http.py
```

Các kiểm tra tạo database tạm với tên riêng, tự xóa sau khi chạy. HTTP test khởi chạy PHP server trên cổng trống, đăng nhập bằng tài khoản kiểm thử, kiểm tra cả hai đường đăng ký, CSRF, quyền truy cập và upload PNG thật; file upload kiểm thử được xóa khi kết thúc. Cần PHP CLI, MySQL/MariaDB và Python 3 cho HTTP test. Có thể đặt `PHP_BINARY` nếu PHP chưa nằm trong PATH.
