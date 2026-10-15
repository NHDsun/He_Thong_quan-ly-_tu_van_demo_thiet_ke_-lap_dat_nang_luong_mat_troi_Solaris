<?php
/**
 * =========================================================================
 * KHU VỰC 1: DÀNH CHO THÀNH VIÊN 1 (BẠN - TEAM LEAD / CORE BACKEND & AUTH)
 * =========================================================================
 * Nhiệm vụ chính:
 * 1. Quản lý Trang Home (Trang chủ) và Trang Thông Tin Cá Nhân (Hồ sơ 4 thành viên).
 * 2. Cấu trúc Auth (Đăng nhập, Đăng xuất, Lưu Session, Ghi nhớ Cookies).
 * 3. Gửi Email Báo giá tự động qua SMTP (SolarQuoteMail.php).
 * 4. Phụ trách 6 bài tập phần PHP & FORM (Bài 1 -> Bài 6).
 */

namespace App\Modules\Member1_Lead;

class HomeController
{
    public function index()
    {
        return [
            'status' => 'success',
            'view' => 'pages.home'
        ];
    }
}
