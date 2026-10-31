<?php
/**
 * Bài 13 (Mảng 5): Thay thế & Ma trận số 2 chiều
 * Phụ trách: Thành viên 3
 */

$mang_cu_str = $_POST['mang_cu'] ?? '12, 5, 8, 20, 5, 9, 14, 5, 30';
$gia_tri_cu = $_POST['gia_tri_cu'] ?? '5';
$gia_tri_moi = $_POST['gia_tri_moi'] ?? '99';
$mang_moi_str = '';
$error = '';
$da_thay_the = false;
$so_lan_thay = 0;

// Hàm thay thế phần tử trong mảng
function thay_the_mang(array $mang, $cu, $moi, &$count = 0): array
{
    $count = 0;
    for ($i = 0; $i < count($mang); $i++) {
        if ((string)$mang[$i] === (string)$cu) {
            $mang[$i] = $moi;
            $count++;
        }
    }
    return $mang;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_thay_the'])) {
    if (trim($mang_cu_str) === '') {
        $error = 'Vui lòng nhập các phần tử của mảng!';
    } elseif (trim($gia_tri_cu) === '') {
        $error = 'Vui lòng nhập giá trị cần thay thế!';
    } elseif (trim($gia_tri_moi) === '') {
        $error = 'Vui lòng nhập giá trị thay thế mới!';
    } else {
        $mang_goc = array_map('trim', explode(',', $mang_cu_str));
        $mang_goc = array_filter($mang_goc, fn($v) => $v !== '');
        
        $mang_ket_qua = thay_the_mang($mang_goc, trim($gia_tri_cu), trim($gia_tri_moi), $so_lan_thay);
        $mang_moi_str = implode(', ', $mang_ket_qua);
        $da_thay_the = true;
    }
}
?>

<div class="sol-glass-card" style="padding: 2rem; max-width: 820px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
        <div>
            <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.35rem;">BÀI TẬP 13 (MẢNG 5) • THÀNH VIÊN 3</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading);">
                Thay Thế Phần Tử Trong Mảng & Ma Trận Số
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
        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                Nhập các phần tử của mảng (phân cách bằng dấu phẩy):
            </label>
            <input 
                type="text" 
                name="mang_cu" 
                value="<?= htmlspecialchars($mang_cu_str) ?>" 
                required 
                style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-family: var(--font-mono); font-size: 0.9rem;"
            >
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.3rem;">
                Gợi ý: <code>12, 5, 8, 20, 5, 9, 14, 5, 30</code> (có thể thay thế số 5 thành số 99)
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                    Giá trị cần thay thế:
                </label>
                <input 
                    type="text" 
                    name="gia_tri_cu" 
                    value="<?= htmlspecialchars($gia_tri_cu) ?>" 
                    required 
                    style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-family: var(--font-mono); font-size: 0.9rem;"
                >
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                    Giá trị thay thế mới:
                </label>
                <input 
                    type="text" 
                    name="gia_tri_moi" 
                    value="<?= htmlspecialchars($gia_tri_moi) ?>" 
                    required 
                    style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-family: var(--font-mono); font-size: 0.9rem;"
                >
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <button type="submit" name="btn_thay_the" class="sol-btn sol-btn--solar" style="flex: 1;">
                🔄 Thực Hiện Thay Thế
            </button>
            <button type="button" onclick="document.querySelector('[name=mang_cu]').value=Array.from({length:10},()=>Math.floor(Math.random()*50)+1).join(', ')" class="sol-btn sol-btn--secondary">
                🎲 Phát Sinh Mảng Ngẫu Nhiên
            </button>
        </div>

        <?php if ($da_thay_the): ?>
            <div style="background: rgba(8, 12, 20, 0.8); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: var(--radius-md); padding: 1.5rem; margin-top: 1.5rem;">
                <div style="color: var(--accent-cyan); font-weight: 700; font-size: 0.9rem; margin-bottom: 0.85rem;">
                    📊 KẾT QUẢ XỬ LÝ MẢNG:
                </div>
                
                <div style="margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Mảng ban đầu:</span>
                    <div style="font-family: var(--font-mono); color: #ffffff; padding: 0.5rem 0.75rem; background: rgba(255,255,255,0.03); border-radius: 4px; margin-top: 0.25rem;">
                        [ <?= htmlspecialchars($mang_cu_str) ?> ]
                    </div>
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Mảng sau khi thay thế (<?= $so_lan_thay ?> lần thay thế giá trị "<?= htmlspecialchars($gia_tri_cu) ?>" thành "<?= htmlspecialchars($gia_tri_moi) ?>"):</span>
                    <div style="font-family: var(--font-mono); color: var(--accent-gold); font-weight: 700; padding: 0.5rem 0.75rem; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 4px; margin-top: 0.25rem;">
                        [ <?= htmlspecialchars($mang_moi_str) ?> ]
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </form>
</div>
