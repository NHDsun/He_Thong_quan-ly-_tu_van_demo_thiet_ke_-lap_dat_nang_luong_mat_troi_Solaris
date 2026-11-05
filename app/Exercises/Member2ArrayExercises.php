<?php

declare(strict_types=1);

namespace App\Exercises;

use InvalidArgumentException;

/** Các phép xử lý của sáu bài Mảng, chuỗi & hàm (thành viên 2). */
final class Member2ArrayExercises
{
    public static function countFromInput(string $raw): int
    {
        $raw = trim($raw);
        if (!preg_match('/^[1-9][0-9]*$/D', $raw) || strlen($raw) > 3 || (int) $raw > 100) {
            throw new InvalidArgumentException('Số phần tử phải là số nguyên dương từ 1 đến 100.');
        }
        return (int) $raw;
    }

    public static function numberFromInput(string $raw): int|float
    {
        $raw = trim($raw);
        if (!preg_match('/^[+-]?(?:[0-9]+(?:\.[0-9]+)?|\.[0-9]+)$/D', $raw) || strlen($raw) > 32) {
            throw new InvalidArgumentException('Giá trị cần tìm/thay phải là một số hợp lệ (dùng dấu chấm cho phần thập phân).');
        }
        $number = (float) $raw;
        if (!is_finite($number)) {
            throw new InvalidArgumentException('Giá trị số quá lớn.');
        }
        return floor($number) === $number && $number >= PHP_INT_MIN && $number <= PHP_INT_MAX ? (int) $number : $number;
    }

    /** @return list<int|float> */
    public static function parseNumbers(string $raw): array
    {
        if (trim($raw) === '') {
            throw new InvalidArgumentException('Vui lòng nhập dãy số, cách nhau bằng dấu phẩy.');
        }
        $parts = explode(',', $raw);
        if (count($parts) > 100) {
            throw new InvalidArgumentException('Dãy số không được vượt quá 100 phần tử.');
        }
        $numbers = [];
        foreach ($parts as $index => $part) {
            try {
                $numbers[] = self::numberFromInput($part);
            } catch (InvalidArgumentException) {
                throw new InvalidArgumentException('Phần tử thứ ' . ($index + 1) . ' không phải là số hợp lệ hoặc đang bị bỏ trống.');
            }
        }
        return $numbers;
    }

    /** @return list<int> */
    public static function generate(int $count, int $min, int $max): array
    {
        if ($count < 1 || $count > 100 || $min > $max) {
            throw new InvalidArgumentException('Kích thước hoặc khoảng phát sinh không hợp lệ.');
        }
        $numbers = [];
        for ($i = 0; $i < $count; $i++) {
            $numbers[] = rand($min, $max);
        }
        return $numbers;
    }

    /** @return array{even: int, below100: int, negativeSum: int, zeroPositions: list<int>} */
    public static function statistics(array $numbers): array
    {
        $even = 0;
        $below100 = 0;
        $negativeSum = 0;
        $zeroPositions = [];
        foreach ($numbers as $index => $number) {
            if ($number % 2 === 0) $even++;
            if ($number < 100) $below100++;
            if ($number < 0) $negativeSum += $number;
            if ($number === 0) $zeroPositions[] = $index + 1;
        }
        return compact('even', 'below100', 'negativeSum', 'zeroPositions');
    }

    public static function output(array $numbers, string $separator = ', '): string
    {
        return implode($separator, array_map(static fn (int|float $n): string => (string) $n, $numbers));
    }

    public static function sum(array $numbers): int|float
    {
        return array_sum($numbers);
    }

    public static function minimum(array $numbers): int|float
    {
        return min($numbers);
    }

    public static function maximum(array $numbers): int|float
    {
        return max($numbers);
    }

    /** Vị trí đầu tiên, đếm từ 1; null nếu không tìm thấy. */
    public static function search(array $numbers, int|float $needle): ?int
    {
        foreach ($numbers as $index => $number) {
            if ($number == $needle) {
                return $index + 1;
            }
        }
        return null;
    }

    /** @return array{numbers: array, count: int} */
    public static function replace(array $numbers, int|float $old, int|float $new): array
    {
        $count = 0;
        foreach ($numbers as &$number) {
            if ($number == $old) {
                $number = $new;
                $count++;
            }
        }
        unset($number);
        return ['numbers' => $numbers, 'count' => $count];
    }

    public static function ascending(array $numbers): array
    {
        sort($numbers, SORT_NUMERIC);
        return $numbers;
    }

    public static function descending(array $numbers): array
    {
        rsort($numbers, SORT_NUMERIC);
        return $numbers;
    }
}
