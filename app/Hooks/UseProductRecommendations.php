<?php
declare(strict_types=1);

namespace App\Hooks;

final class UseProductRecommendations
{
    private const STATUSES = [
        'danger' => [0, 'Cần xử lý'],
        'warning' => [1, 'Cần lưu ý'],
        'info' => [2, 'Đề xuất'],
        'neutral' => [3, 'Bổ sung thiết bị'],
        'success' => [4, 'Đã đạt'],
    ];

    public static function make(array $compatibility): array
    {
        $counts = array_fill_keys(array_keys(self::STATUSES), 0);
        $recommendations = [];
        foreach (($compatibility['rules'] ?? []) as $index => $rule) {
            $type = isset(self::STATUSES[$rule['type'] ?? '']) ? $rule['type'] : 'neutral';
            $counts[$type]++;
            [$priority, $status] = self::STATUSES[$type];
            $rule['type'] = $type;
            $title = mb_strtolower((string) ($rule['title'] ?? ''));
            $category = '';
            $systemType = '';
            $actionLabel = '';
            if ($type !== 'success') {
                if (str_contains($title, 'khung') || str_contains($title, 'kẹp')) {
                    $category = 'khung-gia'; $actionLabel = 'Chọn khung ray & kẹp pin';
                } elseif (str_contains($title, 'tủ điện')) {
                    $category = 'tu-dien'; $actionLabel = 'Chọn tủ điện bảo vệ';
                } elseif ((str_contains($title, 'lithium') || str_contains($title, 'pin lưu trữ')) && str_contains(mb_strtolower($rule['detail'] ?? '') . $title, 'hybrid')) {
                    $category = 'inverter'; $systemType = 'Hybrid'; $actionLabel = 'Chọn inverter Hybrid';
                } elseif (str_contains($title, 'inverter dư công suất') || str_contains($title, 'cần ghép thêm tấm pin')) {
                    $category = 'tam-pin'; $actionLabel = 'Bổ sung tấm pin';
                } elseif (str_contains($title, 'inverter') || str_contains($title, 'biến tần')) {
                    $category = 'inverter'; $actionLabel = 'Chọn inverter phù hợp';
                    if (($compatibility['total_batteries'] ?? 0) > 0) {
                        $systemType = 'Hybrid'; $actionLabel = 'Chọn inverter Hybrid';
                    }
                } elseif (str_contains($title, 'tấm pin') || str_contains($title, 'chưa có thiết bị')) {
                    $category = 'tam-pin'; $actionLabel = 'Chọn tấm pin';
                }
            }
            $filters = UseProductFilters::read(['category' => $category, 'system_type' => $systemType])['filters'];
            $recommendations[] = array_merge($rule, [
                'priority' => $priority,
                'original_index' => $index,
                'status_label' => $status,
                'action_url' => $category !== '' ? UseProductFilters::url($filters) : null,
                'action_label' => $actionLabel,
                'product_category' => $category,
            ]);
        }
        usort($recommendations, static fn (array $a, array $b): int => ($a['priority'] <=> $b['priority']) ?: ($a['original_index'] <=> $b['original_index']));
        return [
            'actions' => array_values(array_filter($recommendations, static fn (array $rule): bool => $rule['type'] !== 'success')),
            'successes' => array_values(array_filter($recommendations, static fn (array $rule): bool => $rule['type'] === 'success')),
            'counts' => $counts,
        ];
    }
}
