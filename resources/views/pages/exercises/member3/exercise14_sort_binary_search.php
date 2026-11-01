<?php
/**
 * Bài 14 (Mảng 6): Sắp xếp mảng (Hoán vị) & Tìm kiếm nhị phân
 * Phụ trách: Thành viên 3
 */

$mang_str = $_POST['mang'] ?? '42, 17, 89, 5, 23, 76, 31, 12, 98, 64';
$so_tim = $_POST['so_tim'] ?? '23';
$tang_str = '';
$giam_str = '';
$search_result = null;
$error = '';
$da_xu_ly = false;

// Hàm hoán vị 2 phần tử
function hoan_vi_phan_tu(&$a, &$b): void
{
    $tam = $a;
    $a = $b;
    $b = $tam;
}

// Hàm sắp xếp mảng tăng dần
function sap_xep_tang(array $arr): array
{
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$i] > $arr[$j]) {
                hoan_vi_phan_tu($arr[$i], $arr[$j]);
            }
        }
    }
    return $arr;
}

// Hàm sắp xếp mảng giảm dần
function sap_xep_giam(array $arr): array
{
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$i] < $arr[$j]) {
                hoan_vi_phan_tu($arr[$i], $arr[$j]);
            }
        }
    }
    return $arr;
}

// Hàm tìm kiếm nhị phân (trên mảng đã sắp xếp tăng dần)
function tim_kiem_nhi_phan(array $arr, $x): array
{
    $left = 0;
    $right = count($arr) - 1;
    $steps = 0;

    while ($left <= $right) {
        $steps++;
        $mid = (int) floor(($left + $right) / 2);

        if ($arr[$mid] == $x) {
            return [
                'found' => true,
                'index' => $mid,
                'steps' => $steps,
                'value' => $arr[$mid]
            ];
        }

        if ($arr[$mid] < $x) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return [
        'found' => false,
        'index' => -1,
        'steps' => $steps,
        'value' => $x
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_xu_ly'])) {
    if (trim($mang_str) === '') {
        $error = 'Vui lòng nhập các phần tử số của mảng!';
    } else {
        $arr_raw = array_map('trim', explode(',', $mang_str));
        $arr_nums = [];
        foreach ($arr_raw as $val) {
            if (is_numeric($val)) {
                $arr_nums[] = (float)$val;
            }
        }

        if (empty($arr_nums)) {
            $error = 'Mảng phải chứa các giá trị số hợp lệ!';
        } else {
            $mang_tang = sap_xep_tang($arr_nums);
            $mang_giam = sap_xep_giam($arr_nums);

            $tang_str = implode(', ', $mang_tang);
            $giam_str = implode(', ', $mang_giam);

            if (trim($so_tim) !== '' && is_numeric($so_tim)) {
                $search_result = tim_kiem_nhi_phan($mang_tang, (float)$so_tim);
            }

            $da_xu_ly = true;
        }
    }
}
?>

<div class="sol-glass-card" style="padding: 2rem; max-width: 820px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
        <div>
            <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.35rem;">BÀI TẬP 14 (MẢNG 6) • THÀNH VIÊN 3</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading);">
                Sắp Xếp Mảng Tăng/Giảm & Tìm Kiếm Nhị Phân
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
                Nhập dãy số mảng cần sắp xếp (phân cách bằng dấu phẩy):
            </label>
            <input 
                type="text" 
                name="mang" 
                value="<?= htmlspecialchars($mang_str) ?>" 
                required 
                style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-family: var(--font-mono); font-size: 0.9rem;"
            >
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                Giá trị số cần tìm kiếm nhị phân (Binary Search):
            </label>
            <input 
                type="number" 
                name="so_tim" 
                value="<?= htmlspecialchars($so_tim) ?>" 
                step="any"
                style="width: 100%; max-width: 280px; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-family: var(--font-mono); font-size: 0.9rem;"
            >
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <button type="submit" name="btn_xu_ly" class="sol-btn sol-btn--solar" style="flex: 1;">
                ⚡ Sắp Xếp & Tìm Kiếm Nhị Phân
            </button>
            <button type="button" onclick="document.querySelector('[name=mang]').value=Array.from({length:10},()=>Math.floor(Math.random()*100)+1).join(', ')" class="sol-btn sol-btn--secondary">
                🎲 Phát Sinh Dãy Mới
            </button>
        </div>

        <?php if ($da_xu_ly): ?>
            <div style="background: rgba(8, 12, 20, 0.85); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: var(--radius-md); padding: 1.5rem; margin-top: 1.5rem;">
                <div style="color: var(--accent-cyan); font-weight: 700; font-size: 0.9rem; margin-bottom: 1rem;">
                    📊 KẾT QUẢ SẮP XẾP VÀ TÌM KIẾM:
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div style="padding: 1rem; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: var(--radius-sm);">
                        <div style="color: var(--accent-emerald); font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem;">
                            ⬆️ SẮP XẾP TĂNG DẦN:
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 0.9rem; color: #ffffff;">
                            [ <?= $tang_str ?> ]
                        </div>
                    </div>

                    <div style="padding: 1rem; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: var(--radius-sm);">
                        <div style="color: var(--accent-gold); font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem;">
                            ⬇️ SẮP XẾP GIẢM DẦN:
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 0.9rem; color: #ffffff;">
                            [ <?= $giam_str ?> ]
                        </div>
                    </div>
                </div>

                <?php if ($search_result !== null): ?>
                    <div style="padding: 1rem; background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: var(--radius-sm);">
                        <div style="color: var(--accent-cyan); font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem;">
                            🔍 KẾT QUẢ TÌM KIẾM NHỊ PHÂN TRÊN MẢNG TĂNG DẦN:
                        </div>
                        <?php if ($search_result['found']): ?>
                            <div style="color: #ffffff; font-size: 0.875rem;">
                                Tìm thấy số <strong style="color: var(--accent-gold);"><?= $search_result['value'] ?></strong> tại chỉ mục <code>[<?= $search_result['index'] ?>]</code> (vị trí thứ <?= $search_result['index'] + 1 ?>) sau <strong style="color: var(--accent-emerald);"><?= $search_result['steps'] ?></strong> bước so sánh nhị phân.
                            </div>
                        <?php else: ?>
                            <div style="color: #fca5a5; font-size: 0.875rem;">
                                Không tìm thấy số <strong><?= $search_result['value'] ?></strong> trong mảng sau <?= $search_result['steps'] ?> bước phân chia!
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </form>
</div>
