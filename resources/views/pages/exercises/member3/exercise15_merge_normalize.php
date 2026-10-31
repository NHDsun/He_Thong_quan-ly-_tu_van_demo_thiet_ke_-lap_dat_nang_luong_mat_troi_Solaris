<?php
/**
 * Bài 15 (Mảng 7): Ghép mảng và chuẩn hóa chuỗi
 * Phụ trách: Thành viên 3
 */

$mang_a_str = $_POST['mang_a'] ?? '15, 3, 28, 9, 42';
$mang_b_str = $_POST['mang_b'] ?? '7, 28, 12, 3, 50, 19';
$chuoi_raw = $_POST['chuoi_raw'] ?? '   ngUyỄN    vĂn   aN   bẢo   ';

$mang_c_str = '';
$mang_c_tang_str = '';
$chuoi_chuan = '';
$tan_suat = [];
$error = '';
$da_xu_ly = false;

// Hàm chuẩn hóa chuỗi tiếng Việt (xóa khoảng trắng thừa, viết hoa chữ cái đầu)
function chuan_hoa_ho_ten(string $str): string
{
    // Bỏ khoảng trắng đầu cuối
    $str = trim($str);
    // Thay thế nhiều khoảng trắng liên tiếp bằng 1 khoảng trắng
    $str = preg_replace('/\s+/', ' ', $str);
    // Chuyển toàn bộ về chữ thường
    $str = mb_strtolower($str, 'UTF-8');
    // Viết hoa chữ cái đầu mỗi từ tiếng Việt
    return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
}

// Hàm đếm số lần xuất hiện của các phần tử trong mảng
function dem_tan_suat(array $arr): array
{
    $freq = [];
    foreach ($arr as $val) {
        $key = (string)$val;
        if (!isset($freq[$key])) {
            $freq[$key] = 0;
        }
        $freq[$key]++;
    }
    return $freq;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_ghep_chuan_hoa'])) {
    if (trim($mang_a_str) === '' || trim($mang_b_str) === '') {
        $error = 'Vui lòng nhập đầy đủ các phần tử cho cả Mảng A và Mảng B!';
    } else {
        $arr_a = array_map('trim', explode(',', $mang_a_str));
        $arr_b = array_map('trim', explode(',', $mang_b_str));

        // Lọc bỏ rỗng
        $arr_a = array_filter($arr_a, fn($v) => $v !== '');
        $arr_b = array_filter($arr_b, fn($v) => $v !== '');

        // 1. Ghép Mảng A và Mảng B thành Mảng C
        $mang_c = array_merge($arr_a, $arr_b);
        $mang_c_str = implode(', ', $mang_c);

        // 2. Sắp xếp mảng C tăng dần
        $mang_c_tang = $mang_c;
        sort($mang_c_tang, SORT_NUMERIC);
        $mang_c_tang_str = implode(', ', $mang_c_tang);

        // 3. Đếm tần suất
        $tan_suat = dem_tan_suat($mang_c);

        // 4. Chuẩn hóa chuỗi họ tên
        if (trim($chuoi_raw) !== '') {
            $chuoi_chuan = chuan_hoa_ho_ten($chuoi_raw);
        }

        $da_xu_ly = true;
    }
}
?>

<div class="sol-glass-card" style="padding: 2rem; max-width: 820px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
        <div>
            <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.35rem;">BÀI TẬP 15 (MẢNG 7) • THÀNH VIÊN 3</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading);">
                Ghép Mảng, Đếm Tần Suất & Chuẩn Hóa Chuỗi Họ Tên
            </h2>
        </div>
        <a href="index.php?page=exercises" class="sol-btn sol-btn--secondary sol-btn--sm">⬅ Quay Lại Portal</a>
    </div>

    <?php if ($error): ?>
        <div style="padding: 0.75rem 1rem; background: rgba(244, 63, 94, 0.15); border: 1px solid var(--accent-rose); border-radius: var(--radius-sm); color: #fecdd3; font-size: 0.85rem; margin-bottom: 1.25rem;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                    Mảng A (các số nguyên, cách nhau bằng dấu phẩy):
                </label>
                <input 
                    type="text" 
                    name="mang_a" 
                    value="<?= htmlspecialchars($mang_a_str) ?>" 
                    required 
                    style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-family: var(--font-mono); font-size: 0.9rem;"
                >
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                    Mảng B (các số nguyên, cách nhau bằng dấu phẩy):
                </label>
                <input 
                    type="text" 
                    name="mang_b" 
                    value="<?= htmlspecialchars($mang_b_str) ?>" 
                    required 
                    style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-family: var(--font-mono); font-size: 0.9rem;"
                >
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                Chuỗi họ tên cần chuẩn hóa (xóa khoảng trắng thừa, viết hoa đầu từ):
            </label>
            <input 
                type="text" 
                name="chuoi_raw" 
                value="<?= htmlspecialchars($chuoi_raw) ?>" 
                style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.9rem;"
            >
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <button type="submit" name="btn_ghep_chuan_hoa" class="sol-btn sol-btn--solar" style="flex: 1;">
                🧩 Ghép Mảng & Chuẩn Hóa Chuỗi
            </button>
        </div>

        <?php if ($da_xu_ly): ?>
            <div style="background: rgba(8, 12, 20, 0.85); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: var(--radius-md); padding: 1.5rem; margin-top: 1.5rem;">
                <div style="color: var(--accent-cyan); font-weight: 700; font-size: 0.9rem; margin-bottom: 1rem;">
                    📊 KẾT QUẢ GHÉP MẢNG & CHUẨN HÓA:
                </div>

                <div style="margin-bottom: 0.85rem;">
                    <span style="color: var(--text-muted); font-size: 0.825rem;">Mảng C (ghép từ Mảng A + Mảng B):</span>
                    <div style="font-family: var(--font-mono); color: #ffffff; padding: 0.5rem 0.75rem; background: rgba(255,255,255,0.03); border-radius: 4px; margin-top: 0.25rem;">
                        [ <?= $mang_c_str ?> ] (Tổng <?= count($mang_c) ?> phần tử)
                    </div>
                </div>

                <div style="margin-bottom: 0.85rem;">
                    <span style="color: var(--text-muted); font-size: 0.825rem;">Mảng C sau khi sắp xếp tăng dần:</span>
                    <div style="font-family: var(--font-mono); color: var(--accent-gold); font-weight: 700; padding: 0.5rem 0.75rem; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 4px; margin-top: 0.25rem;">
                        [ <?= $mang_c_tang_str ?> ]
                    </div>
                </div>

                <?php if (!empty($chuoi_chuan)): ?>
                    <div style="margin-bottom: 1rem;">
                        <span style="color: var(--text-muted); font-size: 0.825rem;">Chuỗi sau khi chuẩn hóa:</span>
                        <div style="font-size: 1.05rem; color: var(--accent-emerald); font-weight: 700; padding: 0.6rem 0.85rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 4px; margin-top: 0.25rem;">
                            "<?= htmlspecialchars($chuoi_chuan) ?>"
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <span style="color: var(--text-muted); font-size: 0.825rem;">Bảng thống kê tần suất xuất hiện trong Mảng C:</span>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.4rem;">
                        <?php foreach ($tan_suat as $val => $count): ?>
                            <div style="padding: 0.35rem 0.65rem; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: var(--radius-sm); font-size: 0.8rem;">
                                <strong style="color: #ffffff;"><?= $val ?></strong>: <span style="color: var(--accent-cyan); font-weight: 700;"><?= $count ?> lần</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </form>
</div>
