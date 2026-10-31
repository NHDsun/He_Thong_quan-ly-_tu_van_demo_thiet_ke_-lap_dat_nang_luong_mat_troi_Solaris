<?php
/**
 * =========================================================================
 * KHU VỰC 3: DÀNH CHO THÀNH VIÊN 3 (FULLSTACK / SOLAR CALCULATOR & DATA)
 * =========================================================================
 * Nhiệm vụ chính:
 * 1. Phát triển Trang Tư vấn & Demo Thiết kế (Solar Calculator & Simulator tương tác realtime).
 * 2. Tích hợp Hook tính toán UseSolarCalculator.php (tính kWp, số tấm pin, diện tích mái, hoàn vốn).
 * 3. Phân trang tự động khi dữ liệu > 20 dòng (UsePaginationState.php).
 * 4. Phụ trách 6 bài tập: 3 bài cuối Mảng & Chuỗi (Bài 5 -> 7) + 3 bài đầu PHP MySQL (Bài 1 -> 3).
 */

namespace App\Modules\Member3_Simulator;

class SolarDemoController
{
    public function index(array $cartProducts = [])
    {
        return [
            'status' => 'ready_for_development',
            'member' => 'Thành viên 3',
            'page' => 'Tư vấn & Demo Thiết kế (Solar Calculator)',
            'cart_products' => $cartProducts,
        ];
    }
}
