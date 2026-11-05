<?php
use App\Modules\Member4_Installation\View as UI;

if (!isset($invoice['project'], $invoice['summary'])) {
    http_response_code(404);
    return;
}
$project = $invoice['project'];
$summary = $invoice['summary'];
$format = static fn (int $cents): string => number_format($cents / 100, $cents % 100 ? 2 : 0, ',', '.') . ' đ';
$baseItemsTotal = 0;
foreach ($invoice['baseItems'] as $item) {
    $baseItemsTotal += (int) round((float) $item['unit_price'] * 100) * (int) $item['quantity'];
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Hóa đơn <?= UI::escape($project['contract_code']) ?></title>
    <link rel="stylesheet" href="resources/css/solaris-theme.css">
    <link rel="stylesheet" href="resources/css/installation-invoice.css?v=<?= filemtime(dirname(__DIR__, 3) . '/css/installation-invoice.css') ?>">
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
        <div class="tv4-invoice-table" role="region" aria-label="Danh sách sản phẩm và chi phí" tabindex="0">
        <table>
            <thead><tr><th>Nội dung</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
            <tbody>
                <?php foreach ($invoice['baseItems'] as $item): ?>
                    <tr>
                        <td><?= UI::escape($item['name']) ?> <small>(ban đầu)</small></td>
                        <td><?= (int) $item['quantity'] ?></td>
                        <td><?= $format((int) round((float) $item['unit_price'] * 100)) ?></td>
                        <td><?= $format((int) round((float) $item['unit_price'] * 100) * (int) $item['quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$invoice['baseItems']): ?>
                    <tr><td colspan="4">Hồ sơ dự án chưa có danh sách sản phẩm ban đầu.</td></tr>
                <?php endif; ?>
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
        </div>
        <dl>
            <?php if ($invoice['baseItems']): ?>
                <dt>Tổng sản phẩm ban đầu</dt><dd><?= $format($baseItemsTotal) ?></dd>
                <?php if ($baseItemsTotal !== $summary['base']): ?>
                    <dt>Điều chỉnh theo dự toán ban đầu đã duyệt</dt><dd><?= $format($summary['base'] - $baseItemsTotal) ?></dd>
                <?php endif; ?>
            <?php else: ?>
                <dt>Dự toán ban đầu (chưa có chi tiết sản phẩm)</dt><dd><?= $format($summary['base']) ?></dd>
            <?php endif; ?>
            <dt>Vật tư phát sinh</dt><dd><?= $format($summary['extra']) ?></dd>
            <dt>Tổng phải thanh toán</dt><dd><?= $format($summary['total']) ?></dd>
            <dt>Đã thanh toán</dt><dd><?= $format($summary['paid']) ?></dd>
            <dt>Còn phải trả</dt><dd><?= $format($summary['remaining']) ?></dd>
        </dl>
        <div class="tv4-invoice-signatures"><p>Khách hàng<br><small>(Ký, ghi rõ họ tên)</small></p><p>Đại diện SOLARIS<br><small>(Ký, ghi rõ họ tên)</small></p></div>
    </main>
</body>
</html>
