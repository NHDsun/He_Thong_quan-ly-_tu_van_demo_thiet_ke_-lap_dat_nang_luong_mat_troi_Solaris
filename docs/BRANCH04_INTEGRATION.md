# Tích hợp chức năng từ quanLyLapDat04

Nguồn: hai commit `7a12684` (thanh toán, vật tư, hóa đơn) và `aa4c3c0` (giao diện) trên branch local `quanLyLapDat04`.

Các phần được tích hợp trên `quanLyLapDat05`:

- `UseProjectBilling`: tính giá trị ban đầu, phát sinh, đã thanh toán và số dư; thêm vật tư; thanh toán mô phỏng; dữ liệu hóa đơn.
- Component chi phí/thanh toán, hóa đơn, popup vật tư và danh sách vật tư gắn bước khảo sát.
- CSS riêng cho bản in, popup và các thẻ giai đoạn; JS mở/đóng popup, tìm vật tư không dấu, lọc loại và mở thẻ hoàn thành từ timeline.
- Các đường xử lý tương ứng trong controller, workflow, bootstrap và helper view.
- Migration thêm hai bảng chi phí; đồng bộ schema/seed/full SQL hiện tại và chuyển các kiểm tra billing sang schema chung.

Điều chỉnh để tương thích quy trình hiện tại:

- Giữ bước admin duyệt yêu cầu dự án trước phân công, giữ phân công là bắt đầu ngay.
- Chỉ thanh toán khi dự án đang thực hiện hoặc đã hoàn tất. Giữ cho khách hàng chỉ được xem/thanh toán dự án của mình.
- Các sự kiện vật tư/thanh toán dùng chung nhật ký hiện tại, cho phép các sự kiện cấp dự án không gắn giai đoạn.

Hai file xuất SQL cũ ở gốc branch nguồn (`solaris_database_upgrade.sql`, `solaris_data_insert_fixed.sql`) không được dùng để cập nhật database hiện tại; schema và migration mới đã chứa các bảng cần thiết. Bộ HTTP test cũ được thay bằng `tests/workflow-http.py`, chạy cả quy trình tiếp nhận mới và billing trên database riêng.

Hướng dẫn thao tác: [PROJECT_WORKFLOW.md](PROJECT_WORKFLOW.md).
