<?php
declare(strict_types=1);

namespace App\Hooks;

use DateTimeImmutable;
use PDO;
use RuntimeException;
use Throwable;

final class UseInstallationWorkflow
{
    public const STAGES = [
        'survey' => 'Khảo sát hiện trường',
        'design' => 'Lập thiết kế kỹ thuật & hồ sơ',
        'procurement' => 'Phê duyệt & mua sắm vật tư',
        'installation' => 'Thi công lắp đặt',
        'grid_connection' => 'Kiểm thử & Nối lưới EVN',
        'handover' => 'Bàn giao & nghiệm thu',
    ];

    public PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Truy vấn SELECT: truyền giá trị riêng qua dấu ? để tránh nối dữ liệu vào SQL.
    public function rows(string $sql, array $params = []): array
    {
        $query = $this->db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll();
    }

    public function run(string $sql, array $params = []): void
    {
        $query = $this->db->prepare($sql);
        $query->execute($params);
    }

    public function project(int $id, array $actor, bool $lock = false): array
    {
        $project = $this->rows('SELECT * FROM projects WHERE id=?' . ($lock ? ' FOR UPDATE' : ''), [$id])[0] ?? null;
        if (!$project || !in_array($actor['role'], ['admin', 'technician'], true)) {
            throw new RuntimeException('Không tìm thấy dự án hoặc bạn không có quyền truy cập.');
        }
        if ($actor['role'] !== 'admin' && !$this->rows('SELECT id FROM project_assignments WHERE project_id=? AND technician_id=? AND ended_at IS NULL', [$id, $actor['id']])) {
            throw new RuntimeException('Bạn không còn được phân công trong dự án này.');
        }
        return $project;
    }

    public function listing(array $actor, string $filter, int $page): array
    {
        $where = '1=1';
        $params = [];
        if ($actor['role'] !== 'admin') {
            $where .= ' AND EXISTS (SELECT 1 FROM project_assignments a WHERE a.project_id=p.id AND a.technician_id=? AND a.ended_at IS NULL)';
            $params[] = $actor['id'];
        }
        if (in_array($filter, ['new', 'in_progress', 'completed'], true)) {
            $where .= ' AND p.status=?';
            $params[] = $filter;
        } elseif ($filter === 'pending' && $actor['role'] === 'admin') {
            $where .= " AND (EXISTS (SELECT 1 FROM project_requests r WHERE r.project_id=p.id AND r.status='pending') OR EXISTS (SELECT 1 FROM project_stages s WHERE s.project_id=p.id AND s.status='pending_approval'))";
        }
        $total = (int) $this->rows("SELECT COUNT(*) n FROM projects p WHERE {$where}", $params)[0]['n'];
        $pager = UsePaginationState::make($total, $page);
        $projects = $this->rows("SELECT p.*, (SELECT COUNT(*) FROM project_stages s WHERE s.project_id=p.id AND s.status='approved') approved_count,
            (SELECT COUNT(*) FROM project_assignments a WHERE a.project_id=p.id AND a.ended_at IS NULL) crew_count,
            (SELECT COUNT(*) FROM project_requests r WHERE r.project_id=p.id AND r.status='pending') request_count
            FROM projects p WHERE {$where} ORDER BY FIELD(p.status,'new','in_progress','completed','cancelled'), p.created_at DESC, p.id DESC LIMIT {$pager['limit']} OFFSET {$pager['offset']}", $params);
        return [
            'projects' => $projects,
            'pager' => $pager,
        ];
    }

    public function detail(int $id, array $actor, int $logPage = 1, int $requestPage = 1): array
    {
        $project = $this->project($id, $actor);
        $stages = $this->rows('SELECT * FROM project_stages WHERE project_id=? ORDER BY position', [$id]);
        $documents = $this->rows('SELECT d.*, u.full_name FROM project_documents d JOIN users u ON u.id=d.uploaded_by WHERE project_id=? ORDER BY d.id DESC', [$id]);
        $crew = $this->rows('SELECT a.*,u.full_name FROM project_assignments a JOIN users u ON u.id=a.technician_id WHERE project_id=? AND ended_at IS NULL ORDER BY a.id', [$id]);
        // Các yêu cầu cũ có phân trang riêng, không giới hạn ngầm lịch sử.
        $requests = $this->rows("SELECT r.*, u.full_name, old.full_name old_name, next.full_name next_name FROM project_requests r
            JOIN users u ON u.id=r.requested_by LEFT JOIN users old ON old.id=r.old_technician_id
            LEFT JOIN users next ON next.id=r.proposed_technician_id WHERE r.project_id=? AND r.status='pending' ORDER BY r.id DESC", [$id]);
        $logPager = UsePaginationState::make((int) $this->rows('SELECT COUNT(*) n FROM project_stage_logs WHERE project_id=?', [$id])[0]['n'], $logPage);
        $logs = $this->rows("SELECT l.*, u.full_name FROM project_stage_logs l LEFT JOIN users u ON u.id=l.actor_id WHERE l.project_id=? ORDER BY l.id DESC LIMIT {$logPager['limit']} OFFSET {$logPager['offset']}", [$id]);
        $requestPager = UsePaginationState::make((int) $this->rows("SELECT COUNT(*) n FROM project_requests WHERE project_id=? AND status<>'pending'", [$id])[0]['n'], $requestPage);
        $requestHistory = $this->rows("SELECT r.*,u.full_name,reviewer.full_name reviewer_name,old.full_name old_name,next.full_name next_name FROM project_requests r
            JOIN users u ON u.id=r.requested_by LEFT JOIN users reviewer ON reviewer.id=r.reviewed_by
            LEFT JOIN users old ON old.id=r.old_technician_id LEFT JOIN users next ON next.id=r.proposed_technician_id
            WHERE r.project_id=? AND r.status<>'pending' ORDER BY r.id DESC LIMIT {$requestPager['limit']} OFFSET {$requestPager['offset']}", [$id]);
        return [
            'project' => $project,
            'stages' => $stages,
            'documents' => $documents,
            'crew' => $crew,
            'requests' => $requests,
            'logs' => $logs,
            'logPager' => $logPager,
            'requestPager' => $requestPager,
            'requestHistory' => $requestHistory,
        ];
    }

    public static function date(string $value): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new RuntimeException('Ngày không hợp lệ.');
        }
        return $date;
    }

    public static function workdays(string $start, string $end): int
    {
        $from = self::date($start);
        $until = self::date($end);
        if ($until < $from || $from->diff($until)->days > 730) {
            throw new RuntimeException('Khoảng lịch phải tăng dần và không vượt quá 2 năm.');
        }
        $count = 0;
        for ($day = $from; $day <= $until; $day = $day->modify('+1 day')) {
            if ($day->format('N') <= 5) {
                $count++;
            }
        }
        return $count;
    }

    public function availability(string $start, string $end, int $page = 1, ?int $excludeProject = null): array
    {
        $days = self::workdays($start, $end);
        $pager = UsePaginationState::make((int) $this->rows("SELECT COUNT(*) n FROM users WHERE role='technician'")[0]['n'], $page);
        $technicians = $this->rows("SELECT id,full_name FROM users WHERE role='technician' ORDER BY full_name,id LIMIT {$pager['limit']} OFFSET {$pager['offset']}");
        foreach ($technicians as $index => $tech) {
            $tech['bookings'] = $this->rows("SELECT a.*,p.project_name,p.contract_code FROM project_assignments a JOIN projects p ON p.id=a.project_id
                WHERE a.technician_id=? AND a.ended_at IS NULL AND p.status='in_progress' AND a.planned_start<=? AND a.planned_end>=? AND (? IS NULL OR p.id<>?) ORDER BY a.planned_start", [$tech['id'], $end, $start, $excludeProject, $excludeProject]);
            $tech['project_count'] = (int) $this->rows("SELECT COUNT(DISTINCT a.project_id) n FROM project_assignments a JOIN projects p ON p.id=a.project_id WHERE a.technician_id=? AND a.ended_at IS NULL AND p.status='in_progress' AND (? IS NULL OR p.id<>?)", [$tech['id'], $excludeProject, $excludeProject])[0]['n'];
            $hours = 0;
            foreach ($tech['bookings'] as $booking) {
                $hours += self::workdays(max($start, $booking['planned_start']), min($end, $booking['planned_end'])) * (float) $booking['hours_per_day'];
            }
            $tech['free_percent'] = $days > 0 ? max(0, (int) round(100 * (1 - $hours / ($days * 8)))) : null;
            $tech['booked_hours'] = $hours;
            $technicians[$index] = $tech;
        }
        // Trong mỗi trang, ưu tiên người rảnh hơn; bằng nhau thì xét số dự án.
        usort($technicians, function ($first, $second) {
            $difference = ($second['free_percent'] ?? 0) - ($first['free_percent'] ?? 0);
            if ($difference !== 0) {
                return $difference;
            }
            return $first['project_count'] - $second['project_count'];
        });
        return [
            'technicians' => $technicians,
            'pager' => $pager,
            'start' => $start,
            'end' => $end,
            'days' => $days,
        ];
    }

    private function note(array $data, string $key = 'reason'): string
    {
        $note = trim((string) ($data[$key] ?? ''));
        if ($note === '' || strlen($note) > 5000) {
            throw new RuntimeException('Vui lòng nhập lý do từ 1 đến 5000 byte.');
        }
        return $note;
    }

    private function log(array $project, array $actor, string $action, ?string $from, ?string $to, string $note = ''): void
    {
        $this->run('INSERT INTO project_stage_logs (project_id,stage,started_at,completed_at,actor_id,action,from_status,to_status,note) VALUES (?,?,NOW(),IF(?="approved",NOW(),NULL),?,?,?,?,?)',
            [$project['id'], $project['current_stage'], $to, $actor['id'], $action, $from, $to, $note]);
    }

    // Một lần gửi form tương ứng với một nhánh xử lý bên dưới.
    public function act(array $actor, int $id, string $action, array $data, array $files = []): void
    {
        $stored = null;
        $this->db->beginTransaction();

        try {
            // Khóa dự án trong lúc ghi để tránh hai người duyệt cùng một lúc.
            $project = $this->project($id, $actor, true);
            if ($project['status'] === 'completed' || $project['status'] === 'cancelled') {
                throw new RuntimeException('Dự án đã kết thúc, không thể thay đổi.');
            }

            $stages = $this->rows(
                'SELECT * FROM project_stages WHERE project_id=? AND stage=?',
                [$id, $project['current_stage']]
            );
            $stage = $stages[0] ?? null;
            if (!$stage) {
                throw new RuntimeException('Dự án chưa được khởi tạo đủ giai đoạn.');
            }

            switch ($action) {
                case 'assign':
                    $this->admin($actor);
                    $this->assign($project, $actor, $data);
                    break;
                case 'approve_stage':
                case 'reject_stage':
                    $this->admin($actor);
                    $this->reviewStage($project, $stage, $actor, $action, $data);
                    break;
                case 'approve_request':
                case 'reject_request':
                    $this->admin($actor);
                    $this->reviewRequest($project, $stage, $actor, $action, $data);
                    break;
                case 'upload':
                    $this->requireTechnician($project, $actor);
                    $stored = $this->uploadDocument($project, $stage, $actor, $data, $files);
                    break;
                case 'submit_stage':
                    $this->requireTechnician($project, $actor);
                    $this->submitStage($project, $stage, $actor, $data);
                    break;
                case 'postpone':
                case 'replacement':
                    $this->requireTechnician($project, $actor);
                    $this->request($project, $stage, $actor, $action, $data);
                    break;
                default:
                    throw new RuntimeException('Thao tác không hợp lệ.');
            }

            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($stored !== null) {
                @unlink(dirname(__DIR__, 2) . '/' . $stored);
            }
            throw $error;
        }
    }

    private function requireTechnician(array $project, array $actor): void
    {
        if ($actor['role'] !== 'technician' || $project['status'] !== 'in_progress') {
            throw new RuntimeException('Chỉ kỹ thuật viên được phân công mới được gửi cập nhật.');
        }
    }

    // Quản lý duyệt hoặc yêu cầu bổ sung giai đoạn hiện tại.
    private function reviewStage(array $project, array $stage, array $actor, string $action, array $data): void
    {
        $id = (int) $project['id'];
        if ($stage['status'] !== 'pending_approval') {
            throw new RuntimeException('Giai đoạn không còn chờ duyệt.');
        }
        $approved = $action === 'approve_stage';
        $note = $approved ? trim((string) ($data['review_note'] ?? '')) : $this->note($data, 'review_note');
        $nextStatus = $approved ? 'approved' : 'revision_required';
        $this->run('UPDATE project_stages SET status=?,reviewed_at=NOW(),reviewed_by=?,review_note=?,completed_at=IF(?="approved",NOW(),NULL) WHERE id=?', [$nextStatus, $actor['id'], $note, $nextStatus, $stage['id']]);
        $this->log($project, $actor, $action, $stage['status'], $nextStatus, $note);
        if ($approved) {
            $this->closeRequests($project, $actor, (int) $stage['id'], 'Giai đoạn đã hoàn thành.');
            $next = $this->rows('SELECT * FROM project_stages WHERE project_id=? AND position=?', [$id, $stage['position'] + 1])[0] ?? null;
            if ($next) {
                $this->run("UPDATE project_stages SET status='in_progress',started_at=NOW() WHERE id=?", [$next['id']]);
                $this->run('UPDATE projects SET current_stage=? WHERE id=?', [$next['stage'], $id]);
                $project['current_stage'] = $next['stage'];
                $this->log($project, $actor, 'stage_started', 'locked', 'in_progress');
            } else {
                $this->run("UPDATE projects SET status='completed',completion_date=CURDATE(),completed_at=NOW() WHERE id=?", [$id]);
                $this->run('UPDATE project_assignments SET ended_at=NOW() WHERE project_id=? AND ended_at IS NULL', [$id]);
                $this->log($project, $actor, 'project_completed', 'in_progress', 'completed');
            }
        }
    }

    private function uploadDocument(array $project, array $stage, array $actor, array $data, array $files): string
    {
        $id = (int) $project['id'];
        if (!in_array($stage['status'], ['in_progress', 'revision_required'], true)) {
            throw new RuntimeException('Chỉ upload khi giai đoạn đang làm hoặc cần bổ sung.');
        }
        $file = UseFileUpload::inspect($files['document'] ?? [], (string) ($data['document_type'] ?? ''));
        if ($this->rows('SELECT id FROM project_documents WHERE stage_id=? AND file_hash=?', [$stage['id'], $file['file_hash']])) {
            throw new RuntimeException('Giai đoạn này đã có tài liệu cùng nội dung.');
        }
        $stored = UseFileUpload::store($file);
        try {
            $this->run('INSERT INTO project_documents (project_id,stage_id,document_type,file_name,file_hash,file_path,file_size_kb,file_type,uploaded_by) VALUES (?,?,?,?,?,?,?,?,?)', [$id, $stage['id'], $file['document_type'], $file['file_name'], $file['file_hash'], $stored, $file['file_size_kb'], $file['file_type'], $actor['id']]);
            $this->log($project, $actor, 'document_uploaded', null, null, $file['file_name']);
        } catch (Throwable $error) {
            unlink(dirname(__DIR__, 2) . '/' . $stored);
            throw $error;
        }
        return $stored;
    }

    // Gửi duyệt chỉ khi đã có ảnh và đã xác nhận yêu cầu hoãn còn chờ.
    private function submitStage(array $project, array $stage, array $actor, array $data): void
    {
        if (!in_array($stage['status'], ['in_progress', 'revision_required'], true)) {
            throw new RuntimeException('Giai đoạn này không thể gửi duyệt.');
        }
        if (!$this->rows("SELECT id FROM project_documents WHERE stage_id=? AND file_type IN ('image/jpeg','image/png') LIMIT 1", [$stage['id']])) {
            throw new RuntimeException('Cần ít nhất một ảnh minh chứng của giai đoạn trước khi gửi duyệt.');
        }
        $pending = $this->rows("SELECT id FROM project_requests WHERE stage_id=? AND request_type='postpone' AND status='pending'", [$stage['id']]);
        if ($pending && ($data['acknowledge_postpone'] ?? '') !== '1') {
            throw new RuntimeException('Có yêu cầu hoãn đang chờ duyệt. Hãy xác nhận đã tiếp tục công việc trước khi gửi duyệt giai đoạn.');
        }
        $this->run("UPDATE project_stages SET status='pending_approval',submitted_at=NOW(),submitted_by=?,review_note=NULL,reviewed_at=NULL,reviewed_by=NULL WHERE id=?", [$actor['id'], $stage['id']]);
        $this->log($project, $actor, 'stage_submitted', $stage['status'], 'pending_approval', $pending ? 'Đã xác nhận tiếp tục công việc trong khi yêu cầu hoãn còn chờ.' : '');
    }

    private function admin(array $actor): void
    {
        if ($actor['role'] !== 'admin') {
            throw new RuntimeException('Thao tác chỉ dành cho quản lý.');
        }
    }

    private function assign(array $project, array $actor, array $data): void
    {
        if ($project['status'] !== 'new') {
            throw new RuntimeException('Dự án đã được phân công.');
        }
        $start = (string) ($data['start_date'] ?? '');
        $end = (string) ($data['end_date'] ?? '');
        if (self::workdays($start, $end) < 6) {
            throw new RuntimeException('Lịch dự án cần ít nhất 6 ngày làm việc (thứ Hai đến thứ Sáu).');
        }
        $technicianIds = [];
        foreach ((array) ($data['technicians'] ?? []) as $value) {
            $technicianId = (int) $value;
            if (!in_array($technicianId, $technicianIds, true)) {
                $technicianIds[] = $technicianId;
            }
        }
        if (!$technicianIds || count($technicianIds) > 100) {
            throw new RuntimeException('Chọn từ 1 đến 100 kỹ thuật viên.');
        }
        $hours = filter_var($data['hours_per_day'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($hours === false || $hours <= 0 || $hours > 8) {
            throw new RuntimeException('Giờ dự kiến của mỗi người phải lớn hơn 0 và tối đa 8 giờ/ngày.');
        }
        foreach ($technicianIds as $tech) {
            $this->technician($tech);
            $this->run('INSERT INTO project_assignments (project_id,technician_id,planned_start,planned_end,hours_per_day,assigned_by) VALUES (?,?,?,?,?,?)', [$project['id'], $tech, $start, $end, $hours, $actor['id']]);
        }
        // Chia đều số ngày làm việc của dự án cho sáu giai đoạn.
        $workingDates = [];
        for ($day = self::date($start); $day <= self::date($end); $day = $day->modify('+1 day')) {
            if ($day->format('N') <= 5) {
                $workingDates[] = $day->format('Y-m-d');
            }
        }
        $totalDays = count($workingDates);
        foreach (array_keys(self::STAGES) as $stageIndex => $stageCode) {
            $startIndex = (int) floor($stageIndex * $totalDays / 6);
            $endIndex = (int) floor(($stageIndex + 1) * $totalDays / 6) - 1;
            $this->run('UPDATE project_stages SET planned_start=?,planned_end=?,status=?,started_at=IF(?=0,NOW(),NULL) WHERE project_id=? AND stage=?', [$workingDates[$startIndex], $workingDates[$endIndex], $stageIndex === 0 ? 'in_progress' : 'locked', $stageIndex, $project['id'], $stageCode]);
        }
        $this->run("UPDATE projects SET status='in_progress',technician_id=?,start_date=?,planned_end_date=? WHERE id=?", [$technicianIds[0], $start, $end, $project['id']]);
        $this->log($project, $actor, 'crew_assigned', 'new', 'in_progress', 'Nhóm: ' . implode(', ', $technicianIds) . '; ' . $hours . ' giờ/người/ngày.');
    }

    private function technician(int $id): void
    {
        if (!$this->rows("SELECT id FROM users WHERE id=? AND role='technician'", [$id])) {
            throw new RuntimeException('Nhân viên được chọn không phải kỹ thuật viên.');
        }
    }

    private function request(array $project, array $stage, array $actor, string $type, array $data): void
    {
        if (!in_array($stage['status'], ['in_progress', 'revision_required'], true)) {
            throw new RuntimeException('Không gửi yêu cầu mới khi giai đoạn đang chờ duyệt hoặc đã hoàn thành.');
        }
        if ($this->rows("SELECT id FROM project_requests WHERE stage_id=? AND request_type=? AND status='pending'", [$stage['id'], $type])) {
            throw new RuntimeException('Đã có yêu cầu cùng loại đang chờ xử lý cho giai đoạn này.');
        }
        $reason = $this->note($data);
        $start = $end = $old = $next = null;
        if ($type === 'postpone') {
            $start = (string) ($data['proposed_start'] ?? '');
            $end = (string) ($data['proposed_end'] ?? '');
            if (self::workdays($start, $end) < 1 || $start < $stage['planned_start'] || $end < $stage['planned_end']) {
                throw new RuntimeException('Lịch hoãn phải có ngày làm việc và không sớm hơn lịch giai đoạn hiện tại.');
            }
        } else {
            $old = (int) ($data['old_technician_id'] ?? 0);
            $next = (int) ($data['proposed_technician_id'] ?? 0);
            $this->replacementValid((int) $project['id'], $old, $next);
        }
        $this->run('INSERT INTO project_requests (project_id,stage_id,request_type,reason,proposed_start,proposed_end,old_technician_id,proposed_technician_id,requested_by) VALUES (?,?,?,?,?,?,?,?,?)', [$project['id'], $stage['id'], $type, $reason, $start, $end, $old, $next, $actor['id']]);
        $this->log($project, $actor, $type . '_requested', null, 'pending', $reason);
    }

    private function replacementValid(int $project, int $old, int $next): array
    {
        $this->technician($next);
        $assignment = $this->rows('SELECT * FROM project_assignments WHERE project_id=? AND technician_id=? AND ended_at IS NULL', [$project, $old])[0] ?? null;
        if (!$assignment || $this->rows('SELECT id FROM project_assignments WHERE project_id=? AND technician_id=? AND ended_at IS NULL', [$project, $next])) {
            throw new RuntimeException('Người cần thay phải thuộc nhóm và người mới chưa có trong nhóm.');
        }
        return $assignment;
    }

    private function reviewRequest(array $project, array $stage, array $actor, string $action, array $data): void
    {
        $request = $this->rows('SELECT * FROM project_requests WHERE id=? AND project_id=?', [(int) ($data['request_id'] ?? 0), $project['id']])[0] ?? null;
        if (!$request || $request['status'] !== 'pending' || (int) $request['stage_id'] !== (int) $stage['id']) {
            throw new RuntimeException('Yêu cầu không còn hợp lệ hoặc đã được xử lý.');
        }
        $approved = $action === 'approve_request';
        $note = $approved ? trim((string) ($data['review_note'] ?? '')) : $this->note($data, 'review_note');
        if ($approved && $stage['status'] === 'pending_approval') {
            throw new RuntimeException('Giai đoạn đang chờ duyệt hoàn thành. Hãy duyệt hoặc trả giai đoạn về bổ sung trước khi duyệt hoãn/đổi người.');
        }
        if ($approved && $request['request_type'] === 'postpone') {
            $shift = (int) self::date($stage['planned_end'])->diff(self::date($request['proposed_end']))->format('%r%a');
            $this->run('UPDATE project_stages SET planned_start=?,planned_end=? WHERE id=?', [$request['proposed_start'], $request['proposed_end'], $stage['id']]);
            $this->run('UPDATE project_stages SET planned_start=DATE_ADD(planned_start,INTERVAL ? DAY),planned_end=DATE_ADD(planned_end,INTERVAL ? DAY) WHERE project_id=? AND position>?', [$shift, $shift, $project['id'], $stage['position']]);
            $this->run('UPDATE projects SET planned_end_date=DATE_ADD(planned_end_date,INTERVAL ? DAY) WHERE id=?', [$shift, $project['id']]);
            $this->run('UPDATE project_assignments SET planned_end=DATE_ADD(planned_end,INTERVAL ? DAY) WHERE project_id=? AND ended_at IS NULL', [$shift, $project['id']]);
            $note = trim($note . ' Dời các bước sau thêm ' . $shift . ' ngày lịch.');
        } elseif ($approved) {
            $assignment = $this->replacementValid((int) $project['id'], (int) $request['old_technician_id'], (int) $request['proposed_technician_id']);
            $this->run('UPDATE project_assignments SET ended_at=NOW() WHERE id=?', [$assignment['id']]);
            $start = max(date('Y-m-d'), $assignment['planned_start']);
            $end = max($start, $assignment['planned_end']);
            $this->run('INSERT INTO project_assignments (project_id,technician_id,planned_start,planned_end,hours_per_day,assigned_by) VALUES (?,?,?,?,?,?)', [$project['id'], $request['proposed_technician_id'], $start, $end, $assignment['hours_per_day'], $actor['id']]);
            if ((int) $project['technician_id'] === (int) $request['old_technician_id']) {
                $this->run('UPDATE projects SET technician_id=? WHERE id=?', [$request['proposed_technician_id'], $project['id']]);
            }
        }
        $status = $approved ? 'approved' : 'rejected';
        $this->run('UPDATE project_requests SET status=?,reviewed_by=?,reviewed_at=NOW(),review_note=? WHERE id=?', [$status, $actor['id'], $note, $request['id']]);
        $this->log($project, $actor, 'request_' . $status, 'pending', $status, '#' . $request['id'] . ': ' . $request['reason'] . '; ' . $note);
    }

    private function closeRequests(array $project, array $actor, int $stage, string $reason): void
    {
        foreach ($this->rows("SELECT id,reason FROM project_requests WHERE stage_id=? AND status='pending'", [$stage]) as $request) {
            $this->run("UPDATE project_requests SET status='superseded',reviewed_by=?,reviewed_at=NOW(),review_note=? WHERE id=?", [$actor['id'], $reason, $request['id']]);
            $this->log($project, $actor, 'request_superseded', 'pending', 'superseded', '#' . $request['id'] . ': ' . $request['reason'] . '; ' . $reason);
        }
    }

    /** Điểm tích hợp TV3: gọi sau khi báo giá đã được chấp thuận. */
    public function createFromApprovedQuote(int $quoteId, int $actorId): int
    {
        if ($this->db->inTransaction()) {
            throw new RuntimeException('Gọi điểm tích hợp sau khi transaction báo giá đã commit.');
        }
        $this->db->beginTransaction();
        try {
            $quote = $this->rows('SELECT * FROM solar_quotes WHERE id=? FOR UPDATE', [$quoteId])[0] ?? null;
            if (!$quote || $quote['status'] !== 'approved' || !$quote['user_id']) {
                throw new RuntimeException('Báo giá phải được chấp thuận và gắn với tài khoản khách hàng.');
            }
            $actor = $this->rows("SELECT id,role FROM users WHERE id=? AND role='admin'", [$actorId])[0] ?? null;
            if (!$actor) {
                throw new RuntimeException('Chỉ quản lý được khởi tạo dự án từ báo giá.');
            }
            $existing = $this->rows('SELECT id FROM projects WHERE quote_id=?', [$quoteId])[0] ?? null;
            if ($existing) {
                $this->db->commit();
                return (int) $existing['id'];
            }
            $code = sprintf('SOLAR-%s-%03d', date('Y'), $quoteId);
            $this->run('INSERT INTO projects (contract_code,quote_id,user_id,project_name,capacity_kwp,total_amount,installation_address) VALUES (?,?,?,?,?,?,?)', [$code, $quoteId, $quote['user_id'], 'Điện mặt trời - ' . $quote['customer_name'], $quote['estimated_system_kwp'], $quote['estimated_total_cost'], $quote['address']]);
            $id = (int) $this->db->lastInsertId();
            foreach (array_keys(self::STAGES) as $i => $stage) {
                $this->run('INSERT INTO project_stages (project_id,stage,position) VALUES (?,?,?)', [$id, $stage, $i + 1]);
            }
            $this->log(['id' => $id, 'current_stage' => 'survey'], $actor, 'project_created', null, 'new', 'Báo giá #' . $quoteId);
            $this->db->commit();
            return $id;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }
}
