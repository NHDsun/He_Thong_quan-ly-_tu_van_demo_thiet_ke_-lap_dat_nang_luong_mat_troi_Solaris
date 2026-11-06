# Đồng bộ danh mục 196 sản phẩm

`database/solaris_catalog_additions.sql` là gói bổ sung 170 sản phẩm TV2 và 7 danh mục từ máy thành viên 2. Gói này dành cho máy đã có 26 sản phẩm mẫu từ `database/solaris_data_insert.sql` hoặc `database/solaris_seed.sql`.

Sau khi nhập trên một CSDL chỉ có đúng 26 sản phẩm mẫu, tổng số là **196 sản phẩm**: 195 đang hiển thị và 1 đang ẩn. Các 26 sản phẩm và ID hiện có được giữ nguyên. Sản phẩm mới được cấp ID bởi CSDL đích, vì vậy không sao chép ID nguồn từ máy thành viên 2.

## Trước khi nhập

1. Cập nhật mã nguồn mới nhất. Ảnh của 170 sản phẩm đã nằm trong `assets/products/` và được Git theo dõi; không cần xuất ảnh từ CSDL.
2. Sao lưu CSDL `solaris` trên **máy đích** bằng chức năng Export của phpMyAdmin. Giữ bản sao lưu ở nơi riêng, không đưa bản sao chứa dữ liệu người dùng lên Git.
3. Trong phpMyAdmin, kiểm tra `SELECT COUNT(*) FROM products;` và `SELECT COUNT(*) FROM categories;`. Với bộ mẫu chuẩn, kết quả lần lượt là 26 và 4. Nếu máy đã có dữ liệu khác, kiểm tra trùng `product_code` và danh mục trước khi nhập; kết quả cuối có thể không đúng 196.
4. **Không nhập lại** `solaris_data_insert.sql`, `solaris_seed.sql` hay `solaris_full.sql` trên CSDL đang dùng. Các file đó có thể xóa/ghi đè dữ liệu hiện hữu.

## Nhập và kiểm tra

Trong phpMyAdmin, chọn CSDL `solaris` → tab **Import** → chọn `database/solaris_catalog_additions.sql` → **Go**. File chỉ thêm danh mục theo `slug` và sản phẩm theo `product_code`; không xóa hoặc cập nhật bản ghi cũ. Nhập lần hai không tạo bản ghi trùng.

Sau khi nhập, chạy:

```sql
SELECT COUNT(*) AS total, SUM(is_active=1) AS visible FROM products;
SELECT COUNT(*) AS categories FROM categories;
SELECT COUNT(*) AS local_catalog FROM products WHERE product_code='JAM72D40-LB-575';
```

Trên bộ mẫu chuẩn, kết quả mong đợi là `196 / 195`, `11`, và `1`. Mở catalog, xem phân trang, mở ảnh và thử lọc danh mục. Mã sản phẩm đã có trước sẽ được **giữ nguyên**, không ghi đè bằng bản xuất; nếu có mã trùng nhưng nội dung khác thì cần đối chiếu thủ công.

## Kiểm thử gói xuất

Trên máy nguồn có đủ 170 sản phẩm, `php tools/export-member2-catalog.php` kiểm tra số lượng và ảnh rồi tạo file SQL. `php tests/member2-catalog-migration.php` chạy file SQL hai lần trên bảng tạm trong cùng một kết nối MySQL; bảng dữ liệu thật không bị thay đổi.
