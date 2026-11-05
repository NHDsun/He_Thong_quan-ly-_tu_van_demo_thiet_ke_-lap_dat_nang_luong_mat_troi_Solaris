<?php
declare(strict_types=1);

namespace App\Hooks;

use RuntimeException;

final class UseProjectBilling
{
    public function __construct(private UseInstallationWorkflow $workflow)
    {
    }

    public function summary(array $project): array
    {
        $id = (int) $project['id'];
        // Tính tiền bằng số nguyên xu, tránh sai số khi cộng trừ số thực.
        $extra = (int) $this->workflow->rows('SELECT COALESCE(SUM(quantity*unit_price)*100,0) cents FROM project_extra_materials WHERE project_id=?', [$id])[0]['cents'];
        $paid = (int) $this->workflow->rows('SELECT COALESCE(SUM(amount)*100,0) cents FROM project_payments WHERE project_id=?', [$id])[0]['cents'];
        $base = (int) round((float) $project['total_amount'] * 100);
        return ['base' => $base, 'extra' => $extra, 'total' => $base + $extra,
            'paid' => $paid, 'remaining' => max(0, $base + $extra - $paid)];
    }

    public function canAdd(array $project): bool
    {
        return $project['status'] === 'in_progress'
            && in_array($project['current_stage'], ['survey', 'design', 'procurement'], true)
            && ($project['current_stage'] === 'survey' || (bool) $this->workflow->rows("SELECT id FROM project_stages WHERE project_id=? AND stage='survey' AND status='approved'", [$project['id']]))
            && (bool) $this->workflow->rows("SELECT id FROM project_stages WHERE project_id=? AND stage=? AND status IN ('in_progress','revision_required')", [$project['id'], $project['current_stage']]);
    }

    public function details(array $project, int $page = 1, int $paymentPage = 1): array
    {
        $id = (int) $project['id'];
        $pager = UsePaginationState::make((int) $this->workflow->rows('SELECT COUNT(*) n FROM project_extra_materials WHERE project_id=?', [$id])[0]['n'], $page);
        $paymentPager = UsePaginationState::make((int) $this->workflow->rows('SELECT COUNT(*) n FROM project_payments WHERE project_id=?', [$id])[0]['n'], $paymentPage);
        return [
            'summary' => $this->summary($project),
            'canAdd' => $this->canAdd($project),
            'canPrint' => (bool) $this->workflow->rows("SELECT id FROM project_stages WHERE project_id=? AND stage='procurement' AND status<>'locked'", [$id]),
            'materials' => $this->workflow->rows("SELECT * FROM project_extra_materials WHERE project_id=? ORDER BY id DESC LIMIT {$pager['limit']} OFFSET {$pager['offset']}", [$id]),
            'payments' => $this->workflow->rows("SELECT * FROM project_payments WHERE project_id=? ORDER BY id DESC LIMIT {$paymentPager['limit']} OFFSET {$paymentPager['offset']}", [$id]),
            'pager' => $pager, 'paymentPager' => $paymentPager,
        ];
    }

    // act() đã khóa dự án và mở transaction trước khi gọi các thao tác tiền/vật tư.
    public function pay(array $project, array $actor, array $data): void
    {
        if ($actor['role'] !== 'customer' || (int) $project['user_id'] !== (int) $actor['id'] || !in_array($project['status'], ['in_progress', 'completed'], true)) {
            throw new RuntimeException('Chỉ khách hàng sở hữu dự án mới được thanh toán.');
        }
        $summary = $this->summary($project);
        if ($summary['remaining'] <= 0) {
            throw new RuntimeException('Dự án đã thanh toán đủ.');
        }
        // Số dư gửi từ biểu mẫu chỉ dùng để phát hiện trang đã cũ.
        if ((string) ($data['expected_remaining'] ?? '') !== (string) $summary['remaining'] || ($data['confirm_simulation'] ?? '') !== '1') {
            throw new RuntimeException('Hãy tải lại trang và xác nhận số tiền thanh toán.');
        }
        $code = 'SIM-' . strtoupper(bin2hex(random_bytes(12)));
        $amount = sprintf('%d.%02d', intdiv($summary['remaining'], 100), $summary['remaining'] % 100);
        $this->workflow->run('INSERT INTO project_payments (project_id,paid_by,amount,payment_code) VALUES (?,?,?,?)', [$project['id'], $actor['id'], $amount, $code]);
        $this->workflow->recordEvent($project, $actor, 'payment_simulated', $code . '; ' . $amount . ' VND (mô phỏng).');
    }

    public function add(array $project, array $actor, array $data): void
    {
        if (!in_array($actor['role'], ['admin', 'technician'], true) || !$this->canAdd($project)) {
            throw new RuntimeException('Chỉ bổ sung vật tư khi khảo sát, thiết kế hoặc mua sắm đang làm/cần bổ sung.');
        }
        $quantity = filter_var($data['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000]]);
        $reason = trim((string) ($data['material_reason'] ?? ''));
        $product = $this->workflow->rows('SELECT id,name,price FROM products WHERE id=? AND is_active=1 FOR UPDATE', [(int) ($data['product_id'] ?? 0)])[0] ?? null;
        if (!$product || !$quantity || $reason === '' || strlen($reason) > 5000 || (float) $product['price'] < 0) {
            throw new RuntimeException('Chọn vật tư đang kinh doanh, số lượng 1–10.000 và lý do tối đa 5000 byte.');
        }
        $summary = $this->summary($project);
        if ($summary['total'] + (int) round((float) $product['price'] * 100) * $quantity > 999999999999999) {
            throw new RuntimeException('Tổng chi phí vượt giới hạn cho phép.');
        }
        $this->workflow->run('INSERT INTO project_extra_materials (project_id,product_id,product_name,quantity,unit_price,reason,added_by) VALUES (?,?,?,?,?,?,?)', [$project['id'], $product['id'], $product['name'], $quantity, $product['price'], $reason, $actor['id']]);
        $this->workflow->recordEvent($project, $actor, 'material_added', $product['name'] . ' × ' . $quantity . '; ' . $reason);
    }

    public function invoice(int $id, array $actor): array
    {
        $db = $this->workflow->db;
        $ownTransaction = !$db->inTransaction();
        if ($ownTransaction) {
            $db->beginTransaction();
        }
        try {
            $project = $this->workflow->project($id, $actor);
            $stage = $this->workflow->rows("SELECT status FROM project_stages WHERE project_id=? AND stage='procurement' AND status<>'locked'", [$id])[0] ?? null;
            if (!$stage) {
                throw new RuntimeException('Chỉ in hóa đơn từ bước Phê duyệt & mua sắm vật tư.');
            }
            $invoice = ['project' => $project, 'summary' => $this->summary($project), 'stageStatus' => $stage['status'],
                'customer' => $this->workflow->rows('SELECT full_name,email,phone FROM users WHERE id=?', [$project['user_id']])[0],
                'baseItems' => $this->workflow->rows('SELECT i.quantity,i.unit_price,COALESCE(i.product_name,p.name) name FROM installation_items i JOIN products p ON p.id=i.product_id WHERE i.project_id=? ORDER BY i.id', [$id]),
                'materials' => $this->workflow->rows('SELECT * FROM project_extra_materials WHERE project_id=? ORDER BY id', [$id])];
            if ($ownTransaction) {
                $db->commit();
            }
            return $invoice;
        } catch (\Throwable $error) {
            if ($ownTransaction && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $error;
        }
    }
}
