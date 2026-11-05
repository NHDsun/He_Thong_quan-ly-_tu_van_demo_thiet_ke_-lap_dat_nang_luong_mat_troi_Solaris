<?php
/** Bài 7–12 trên portal = bài 1–6 của phần Mảng, chuỗi & hàm trong PDF. */
require_once dirname(__DIR__, 5) . '/app/Exercises/Member2ArrayExercises.php';

use App\Exercises\Member2ArrayExercises as Arrays;

$exerciseNumber = (int) $ex - 6;
$titles = [
    1 => 'Tạo mảng ngẫu nhiên và thống kê',
    2 => 'Tính tổng dãy số',
    3 => 'Phát sinh mảng và tính toán',
    4 => 'Tìm kiếm phần tử trong mảng',
    5 => 'Thay thế phần tử trong mảng',
    6 => 'Sắp xếp mảng tăng và giảm',
];
$descriptions = [
    1 => 'Phát sinh số nguyên từ -100 đến 150 để quan sát được số âm, số chẵn và giá trị dưới 100.',
    2 => 'Nhập các số cách nhau bằng dấu phẩy. Có thể dùng số âm và số thập phân với dấu chấm.',
    3 => 'Phát sinh các số nguyên từ 0 đến 20; xây dựng và sử dụng 5 hàm tạo, xuất, tổng, min, max.',
    4 => 'Tìm vị trí xuất hiện đầu tiên của một số trong dãy; vị trí được đếm từ 1.',
    5 => 'Thay thế tất cả phần tử có giá trị bằng số cần thay, giữ nguyên thứ tự ban đầu.',
    6 => 'Sắp xếp giá trị số theo thứ tự tăng dần và giảm dần, không sắp theo ký tự.',
];
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$posted = static fn (string $key): string => is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
$values = [
    'n' => $posted('n'),
    'numbers' => $posted('numbers'),
    'needle' => $posted('needle'),
    'old' => $posted('old'),
    'new' => $posted('new'),
];
$result = [];
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    try {
        if ($exerciseNumber === 1) {
            $numbers = Arrays::generate(Arrays::countFromInput($values['n']), -100, 150);
            $stats = Arrays::statistics($numbers);
            $result = [
                'Mảng vừa tạo' => Arrays::output($numbers),
                'Số phần tử chẵn' => $stats['even'],
                'Số phần tử nhỏ hơn 100' => $stats['below100'],
                'Tổng các phần tử âm' => $stats['negativeSum'],
                'Vị trí các số 0 (đếm từ 1)' => $stats['zeroPositions'] ? implode(', ', $stats['zeroPositions']) : 'Không có phần tử bằng 0',
                'Mảng tăng dần' => Arrays::output(Arrays::ascending($numbers)),
            ];
        } elseif ($exerciseNumber === 3) {
            $numbers = Arrays::generate(Arrays::countFromInput($values['n']), 0, 20);
            $result = [
                'Mảng phát sinh' => Arrays::output($numbers, ' '),
                'Tổng' => Arrays::sum($numbers),
                'Giá trị nhỏ nhất' => Arrays::minimum($numbers),
                'Giá trị lớn nhất' => Arrays::maximum($numbers),
            ];
        } else {
            $numbers = Arrays::parseNumbers($values['numbers']);
            if ($exerciseNumber === 2) {
                $result = ['Tổng dãy số' => Arrays::sum($numbers)];
            } elseif ($exerciseNumber === 4) {
                $needle = Arrays::numberFromInput($values['needle']);
                $position = Arrays::search($numbers, $needle);
                $result = [
                    'Mảng' => Arrays::output($numbers),
                    'Kết quả tìm kiếm' => $position === null
                        ? 'Không tìm thấy ' . $needle . ' trong mảng'
                        : 'Đã tìm thấy ' . $needle . ' tại vị trí thứ ' . $position . ' của mảng',
                ];
            } elseif ($exerciseNumber === 5) {
                $old = Arrays::numberFromInput($values['old']);
                $new = Arrays::numberFromInput($values['new']);
                $replaced = Arrays::replace($numbers, $old, $new);
                $result = [
                    'Mảng cũ' => Arrays::output($numbers),
                    'Mảng mới' => Arrays::output($replaced['numbers']),
                    'Số phần tử được thay' => $replaced['count'],
                ];
            } elseif ($exerciseNumber === 6) {
                $result = [
                    'Tăng dần' => Arrays::output(Arrays::ascending($numbers)),
                    'Giảm dần' => Arrays::output(Arrays::descending($numbers)),
                ];
            }
        }
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    }
}
?>
<link rel="stylesheet" href="resources/css/member2-exercises.css?v=<?= filemtime(dirname(__DIR__, 4) . '/css/member2-exercises.css') ?>">
<section class="sol-glass-card member2-exercise">
    <header class="member2-exercise__header">
        <div>
            <span class="sol-badge sol-badge--emerald">BÀI TẬP <?= (int) $ex ?> • THÀNH VIÊN 2</span>
            <h2><?= $escape($titles[$exerciseNumber]) ?></h2>
            <p><?= $escape($descriptions[$exerciseNumber]) ?></p>
        </div>
        <a href="index.php?page=exercises" class="sol-btn sol-btn--secondary sol-btn--sm">⬅ Quay Lại Portal</a>
    </header>

    <?php if ($error !== ''): ?><p class="member2-exercise__error" role="alert"><?= $escape($error) ?></p><?php endif; ?>

    <form name="member2_exercise_<?= $exerciseNumber ?>" method="post" action="index.php?page=exercises&amp;exercise=<?= (int) $ex ?>" class="member2-exercise__form">
        <?php if ($exerciseNumber === 1 || $exerciseNumber === 3): ?>
            <label for="member2-n">Số phần tử n (1–100)</label>
            <input id="member2-n" name="n" type="text" inputmode="numeric" value="<?= $escape($values['n']) ?>" placeholder="Ví dụ: 10" required>
        <?php else: ?>
            <label for="member2-numbers">Dãy số, cách nhau bằng dấu phẩy</label>
            <input id="member2-numbers" name="numbers" type="text" value="<?= $escape($values['numbers']) ?>" placeholder="Ví dụ: -3, 0, 12, 2.5" required>
            <?php if ($exerciseNumber === 4): ?>
                <label for="member2-needle">Số cần tìm</label>
                <input id="member2-needle" name="needle" type="text" value="<?= $escape($values['needle']) ?>" placeholder="Ví dụ: 12" required>
            <?php elseif ($exerciseNumber === 5): ?>
                <label for="member2-old">Số cần thay</label>
                <input id="member2-old" name="old" type="text" value="<?= $escape($values['old']) ?>" placeholder="Ví dụ: 0" required>
                <label for="member2-new">Số thay thế</label>
                <input id="member2-new" name="new" type="text" value="<?= $escape($values['new']) ?>" placeholder="Ví dụ: 99" required>
            <?php endif; ?>
        <?php endif; ?>
        <div class="member2-exercise__actions">
            <button type="submit" class="sol-btn sol-btn--solar">⚡ <?= $escape([1 => 'Thực hiện', 2 => 'Tổng dãy số', 3 => 'Phát sinh và tính toán', 4 => 'Tìm kiếm', 5 => 'Thay thế', 6 => 'Sắp xếp'][$exerciseNumber]) ?></button>
            <a href="index.php?page=exercises&amp;exercise=<?= (int) $ex ?>" class="sol-btn sol-btn--secondary">Làm mới</a>
        </div>
    </form>

    <?php if ($result): ?>
        <div class="member2-exercise__results" aria-live="polite">
            <h2>Kết quả</h2>
            <?php foreach ($result as $label => $value): ?>
                <label><?= $escape($label) ?>
                    <input type="text" value="<?= $escape($value) ?>" readonly aria-label="<?= $escape($label) ?>">
                </label>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
