<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Exercises/Member2ArrayExercises.php';

use App\Exercises\Member2ArrayExercises as Arrays;

$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};
$reject = static function (callable $action) use ($check): void {
    try {
        $action();
        $check(false, 'Expected invalid input to be rejected');
    } catch (InvalidArgumentException) {
        $check(true, 'Invalid input rejected');
    }
};
$render = static function (int $number, array $post = []): string {
    $ex = $number + 6;
    $_SERVER['REQUEST_METHOD'] = $post ? 'POST' : 'GET';
    $_POST = $post;
    ob_start();
    include dirname(__DIR__) . '/resources/views/pages/exercises/member2/array_exercises.php';
    return (string) ob_get_clean();
};

foreach (['', '0', '-1', '1.5', '101', 'abc', '99999999999999999'] as $bad) {
    $reject(static fn () => Arrays::countFromInput($bad));
}
$check(Arrays::countFromInput('1') === 1 && Arrays::countFromInput('100') === 100, 'Boundary counts');
foreach (['', '1,,2', '1,', ',1', 'a,2', '2;3', '1e3'] as $bad) {
    $reject(static fn () => Arrays::parseNumbers($bad));
}
$check(Arrays::parseNumbers(' -2, 0, +3, .5, 1.25 ') === [-2, 0, 3, 0.5, 1.25], 'Signed, zero and decimal numbers');
$reject(static fn () => Arrays::parseNumbers(implode(',', array_fill(0, 101, '1'))));
$check(Arrays::sum([-2, 0, 2, 1.5]) === 1.5, 'Sum with negative and decimal values');
$check(Arrays::minimum([0, 20, 5]) === 0 && Arrays::maximum([0, 20, 5]) === 20, 'Min/max boundaries');
$check(Arrays::search([2, 0, 2], 2) === 1 && Arrays::search([2, 0, 2], 8) === null, 'First match and no match');
$check(Arrays::replace([0, 2, 0], 0, -1) === ['numbers' => [-1, 2, -1], 'count' => 2], 'Replace all matches including zero');
$check(Arrays::replace([1, 2], 3, 5)['count'] === 0, 'No replacement');
$check(Arrays::ascending([10, -2, 2, 2]) === [-2, 2, 2, 10], 'Numeric ascending order with duplicates');
$check(Arrays::descending([10, -2, 2, 2]) === [10, 2, 2, -2], 'Numeric descending order with duplicates');
$check(count(Arrays::generate(100, 0, 20)) === 100, 'Maximum array size');
$check(count(array_filter(Arrays::generate(30, 0, 20), static fn ($n) => $n < 0 || $n > 20)) === 0, 'Generated range');
$reject(static fn () => Arrays::generate(0, 0, 20));
$check(Arrays::statistics([-2, 0, 101, 100, -1, 0]) === ['even' => 4, 'below100' => 4, 'negativeSum' => -3, 'zeroPositions' => [2, 6]], 'All statistics including negative and zero positions');
$check(Arrays::statistics([1, 3])['zeroPositions'] === [], 'No zero position');

for ($number = 1; $number <= 6; $number++) {
    $html = $render($number);
    $check(str_contains($html, 'method="post"') && str_contains($html, 'exercise=' . ($number + 6)), 'GET form for exercise ' . $number);
}
$check(str_contains($render(1, ['n' => '10']), 'Vị trí các số 0') && str_contains($render(1, ['n' => '10']), 'Mảng tăng dần'), 'Exercise 1 statistics');
$check(str_contains($render(2, ['numbers' => '-3, 2.5, 0']), 'value="-0.5"'), 'Exercise 2 sum');
$check(str_contains($render(3, ['n' => '1']), 'Giá trị lớn nhất'), 'Exercise 3 generated result');
$check(str_contains($render(4, ['numbers' => '2,0,2', 'needle' => '2']), 'vị trí thứ 1'), 'Exercise 4 first match');
$check(str_contains($render(4, ['numbers' => '2,0', 'needle' => '9']), 'Không tìm thấy'), 'Exercise 4 no match');
$check(str_contains($render(5, ['numbers' => '0,1,0', 'old' => '0', 'new' => '-2']), 'value="-2, 1, -2"'), 'Exercise 5 replacement');
$check(str_contains($render(6, ['numbers' => '10,-2,2']), 'value="-2, 2, 10"'), 'Exercise 6 numeric sorting');
$check(str_contains($render(2, ['numbers' => '1,,2']), 'role="alert"'), 'Invalid form input shown as error');
$check(!str_contains($render(2, ['numbers' => '<script>']), '<script>'), 'HTML input escaped');

echo "PASS: {$checks} assertions for member 2 array exercises\n";
