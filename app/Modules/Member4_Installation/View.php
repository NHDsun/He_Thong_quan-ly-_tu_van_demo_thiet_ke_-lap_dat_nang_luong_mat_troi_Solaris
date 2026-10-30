<?php
declare(strict_types=1);

namespace App\Modules\Member4_Installation;

/** Adapter nhỏ cho ba atomic Blade hiện có trong ứng dụng PHP thuần. */
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
        if (!in_array($name, ['button', 'badge', 'input'], true)) {
            throw new \LogicException('Unsupported atomic component.');
        }
        $template = file_get_contents(dirname(__DIR__, 3) . '/resources/views/components/base/' . $name . '.blade.php');
        $template = preg_replace_callback('/@props\((\[.*?\])\)/s', static fn($match) => '<?php extract(array_merge(' . $match[1] . ', $props), EXTR_SKIP); ?>', $template);
        $template = preg_replace('/\{\{\s*(.*?)\s*\}\}/s', '<?php echo \\App\\Modules\\Member4_Installation\\View::output($1); ?>', $template);
        $attributes = new Attributes($attrs);
        $slot = $text;
        // Chỉ biên dịch template nội bộ trong allowlist; dữ liệu người dùng luôn được escape.
        eval('?>' . $template);
    }

    public static function output(mixed $value): string
    {
        return $value instanceof Attributes ? (string) $value : self::escape($value);
    }

    public static function csrf(): void
    {
        echo '<input type="hidden" name="csrf" value="' . self::escape($_SESSION['installation_csrf']) . '">';
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

final class Attributes
{
    public function __construct(private array $values) {}

    public function merge(array $defaults): self
    {
        $values = array_merge($defaults, $this->values);
        $values['class'] = trim(($defaults['class'] ?? '') . ' ' . ($this->values['class'] ?? ''));
        return new self($values);
    }

    public function __toString(): string
    {
        $result = '';
        foreach ($this->values as $name => $value) {
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/D', $name) || str_starts_with(strtolower($name), 'on') || $value === false || $value === null) {
                continue;
            }
            $result .= View::escape($name) . '="' . View::escape($value === true ? $name : $value) . '" ';
        }
        return $result;
    }
}
