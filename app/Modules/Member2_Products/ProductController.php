<?php
/**
 * =========================================================================
 * KHU VỰC 2: DÀNH CHO THÀNH VIÊN 2 (FRONTEND LEAD / PRODUCTS & DESIGN)
 * =========================================================================
 * Nhiệm vụ chính:
 * 1. Phát triển Trang Giải pháp & Sản phẩm (Catalog Tấm pin 550W, Inverter, Khung giá).
 * 2. Bộ lọc sản phẩm, chi tiết thiết bị solar, thông số kỹ thuật.
 * 3. Phụ trách 6 bài tập: 2 bài cuối PHP Form (Bài 7, 8) + 4 bài đầu Mảng & Chuỗi (Bài 1 -> 4).
 */

namespace App\Modules\Member2_Products;

class ProductController
{
    public function index()
    {
        return [
            'status' => 'ready_for_development',
            'member' => 'Thành viên 2',
            'page' => 'Giải pháp & Sản phẩm'
        ];
    }
}
