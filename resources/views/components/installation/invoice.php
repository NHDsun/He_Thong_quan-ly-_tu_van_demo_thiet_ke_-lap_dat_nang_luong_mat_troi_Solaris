<?php
use App\Modules\Member4_Installation\View as UI;

if (!isset($invoice['project'], $invoice['summary'])) {
    http_response_code(404);
    return;
}
$project = $invoice['project'];
$summary = $invoice['summary'];
$format = static fn (int $cents): string => number_format($cents / 100, $cents % 100 ? 2 : 0, ',', '.') . ' đ';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Hóa đơn <?= UI::escape($project['contract_code']) ?></title>
    <link rel="stylesheet" href="resources/css/solaris-theme.css">
    <link rel="stylesheet" href="resources/css/installation-invoice.css">
</head>
<body>
    <main class="tv4-invoice">
        <nav class="tv4-invoice-actions">
            <button type="button" class="sol-btn sol-btn--solar" onclick="window.print()">In hóa đơn</button>
            <a href="index.php?page=installations&amp;project=<?= (int) $project['id'] ?>">Quay lại dự án</a>
        </nav>
        <h1>SOLARIS — HÓA ĐƠN DỰ ÁN</h1>
        <p><strong>Mã hợp đồng: <?= UI::escape($project['contract_code']) ?></strong></p>
        <p><?= UI::escape($project['project_name']) ?></p>
        <p>Khách hàng: <?= UI::escape($invoice['customer']['full_name']) ?> · <?= UI::escape($invoice['customer']['phone']) ?></p>
        <p>Địa chỉ lắp đặt: <?= UI::escape($project['installation_address']) ?></p>
        <p>Thời điểm in: <?= date('d/m/Y H:i') ?></p>
        <p>Phê duyệt mua sắm: <?= UI::escape(UI::label($invoice['stageStatus'])) ?></p>
        <p>Chứng từ mô phỏng cho bài tập, không phải hóa đơn thuế. Thanh toán trên hệ thống không thu tiền thật.</p>
        <table>
            <thead><tr><th>Nội dung</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
            <tbody>
                <tr><td>Giá trị dự án ban đầu</td><td>1</td><td><?= $format($summary['base']) ?></td><td><?= $format($summary['base']) ?></td></tr>
                <?php foreach ($invoice['materials'] as $item): ?>
                    <tr>
                        <td><?= UI::escape($item['product_name']) ?> (phát sinh)<br><small><?= UI::escape($item['reason']) ?></small></td>
                        <td><?= (int) $item['quantity'] ?></td>
                        <td><?= $format((int) round((float) $item['unit_price'] * 100)) ?></td>
                        <td><?= $format((int) round((float) $item['unit_price'] * 100) * (int) $item['quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($invoice['baseItems']): ?>
            <h2>Vật tư trong dự toán ban đầu</h2>
            <p>Các vật tư sau đã tính trong giá trị ban đầu, không cộng lại vào tổng phải trả.</p>
            <table>
                <thead><tr><th>Vật tư</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
                <tbody>
                    <?php foreach ($invoice['baseItems'] as $item): ?>
                        <tr>
                            <td><?= UI::escape($item['name']) ?></td>
                            <td><?= (int) $item['quantity'] ?></td>
                            <td><?= $format((int) round((float) $item['unit_price'] * 100)) ?></td>
                            <td><?= $format((int) round((float) $item['unit_price'] * 100) * (int) $item['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <dl>
            <dt>Tổng phải thanh toán</dt><dd><?= $format($summary['total']) ?></dd>
            <dt>Đã thanh toán mô phỏng</dt><dd><?= $format($summary['paid']) ?></dd>
            <dt>Còn phải trả</dt><dd><?= $format($summary['remaining']) ?></dd>
        </dl>
        <p>Vật tư phát sinh chỉ được cập nhật trước khi gửi duyệt bước mua sắm. Số liệu trên bản in là số liệu tại thời điểm in.</p>
        <div class="tv4-invoice-signatures"><p>Khách hàng<br><small>(Ký, ghi rõ họ tên)</small></p><p>Đại diện SOLARIS<br><small>(Ký, ghi rõ họ tên)</small></p></div>
    </main>
</body>
</html>
