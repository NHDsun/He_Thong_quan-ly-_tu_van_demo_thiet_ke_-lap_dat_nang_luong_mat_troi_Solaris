<?php
declare(strict_types=1);

namespace App\Modules\Member4_Installation;

/** Các hàm dùng chung khi hiển thị trang lắp đặt. */
final class View
{
    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function url(array $changes = []): string
    {
        $allowed = array_intersect_key($_GET, array_flip(['project', 'filter', 'p', 'from', 'to', 'staff_page', 'log_page', 'doc_page', 'request_page']));
        return 'index.php?' . http_build_query(array_merge(['page' => 'installations'], $allowed, $changes));
    }

    public static function component(string $name, string $text = '', array $props = [], array $attrs = []): void
    {
        // PHP thuần dùng include; không cần tự biên dịch cú pháp Blade.
        switch ($name) {
            case 'button':
                $variant = $props['variant'] ?? 'solar';
                $size = $props['size'] ?? 'md';
                $classes = 'sol-btn sol-btn--' . $variant;
                if ($size === 'sm' || $size === 'lg') {
                    $classes .= ' sol-btn--' . $size;
                }
                $attrs['type'] = $props['type'] ?? 'button';
                break;
            case 'badge':
                $classes = 'sol-badge sol-badge--' . ($props['variant'] ?? 'amber');
                break;
            case 'input':
                $classes = 'sol-input';
                if (!empty($props['hasError'])) {
                    $classes .= ' sol-input--error';
                }
                $attrs['type'] = $props['type'] ?? 'text';
                break;
            default:
                throw new \InvalidArgumentException('Component không hợp lệ.');
        }
        $attrs['class'] = trim($classes . ' ' . ($attrs['class'] ?? ''));
        require dirname(__DIR__, 3) . '/resources/views/components/installation/atomic.php';
    }

    public static function attributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $name => $value) {
            if ($value === false || $value === null) {
                continue;
            }
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $name) || str_starts_with(strtolower($name), 'on')) {
                continue;
            }
            if ($value === true) {
                $value = $name;
            }
            $html .= self::escape($name) . '="' . self::escape($value) . '" ';
        }
        return $html;
    }

    public static function csrf(): void
    {
        echo '<input type="hidden" name="csrf" value="' . self::escape($_SESSION['installation_csrf']) . '">';
    }

    public static function form(string $action): void
    {
        self::csrf();
        echo '<input type="hidden" name="action" value="' . self::escape($action) . '">';
    }

    public static function pager(array $pager, string $key): void
    {
        if ($pager['pages'] <= 1) {
            return;
        }
        echo '<nav class="tv4-pager" aria-label="Phân trang"><span>Trang ' . $pager['page'] . ' / ' . $pager['pages'] . '</span>';
        foreach ([-1 => 'Trước', 1 => 'Sau'] as $delta => $label) {
            $page = $pager['page'] + $delta;
            if ($page >= 1 && $page <= $pager['pages']) {
                echo '<a class="sol-btn sol-btn--outline sol-btn--sm" href="' . self::escape(self::url([$key => $page])) . '">' . $label . '</a>';
            }
        }
        echo '</nav>';
    }

    public static function label(string $status): string
    {
        return ['new' => 'Chờ phân công', 'in_progress' => 'Đang thực hiện', 'completed' => 'Hoàn tất',
            'cancelled' => 'Đã hủy', 'locked' => 'Chưa mở', 'pending_approval' => 'Chờ duyệt',
            'revision_required' => 'Cần bổ sung', 'approved' => 'Đã duyệt', 'pending' => 'Chờ xử lý',
            'rejected' => 'Từ chối', 'superseded' => 'Đã đóng'] [$status] ?? $status;
    }

    public static function actionLabel(string $action): string
    {
        return ['project_created' => 'Khởi tạo dự án', 'crew_assigned' => 'Phân công đội ngũ',
            'stage_started' => 'Bắt đầu giai đoạn', 'document_uploaded' => 'Tải minh chứng',
            'stage_submitted' => 'Gửi duyệt giai đoạn', 'approve_stage' => 'Duyệt giai đoạn',
            'reject_stage' => 'Yêu cầu bổ sung', 'postpone_requested' => 'Đề nghị hoãn lịch',
            'replacement_requested' => 'Đề nghị thay nhân viên', 'request_approved' => 'Duyệt yêu cầu',
            'request_rejected' => 'Từ chối yêu cầu', 'request_superseded' => 'Đóng yêu cầu',
            'project_completed' => 'Hoàn tất dự án'][$action] ?? $action;
    }

    public static function documentLabel(string $type): string
    {
        return ['stage_photo' => 'Ảnh minh chứng', 'survey_photo' => 'Ảnh khảo sát',
            'technical_drawing' => 'Bản vẽ kỹ thuật', 'procurement_invoice' => 'Hóa đơn vật tư',
            'installation_photo' => 'Ảnh thi công', 'testing_photo' => 'Ảnh kiểm thử',
            'evn_permit' => 'Hồ sơ EVN', 'handover_report' => 'Biên bản bàn giao'][$type] ?? $type;
    }
}
