<?php
declare(strict_types=1);

use App\Hooks\UseProductSpecifications;

$deviceImageUrl = UseProductSpecifications::imageUrl($item);
$deviceIcon = match (UseProductSpecifications::categorySlug($item)) {
    'tam-pin' => '☀️',
    'inverter' => '⚡',
    'pin-luu-tru' => '🔋',
    'tu-dien' => '🛡️',
    'khung-gia' => '🔩',
    'day-cap' => '🔌',
    'dong-ho' => '📟',
    default => '🔧',
};
?>
<span class="product-device-thumbnail" style="width: 56px; height: 56px; border-radius: 8px; background: var(--bg-card, #141e33); border: 1px solid var(--border-glass, rgba(255,255,255,.08)); display: grid; place-items: center; overflow: hidden; font-size: 1.4rem; flex-shrink: 0;">
    <span data-device-image-fallback <?= $deviceImageUrl ? 'hidden' : '' ?> aria-hidden="true"><?= $deviceIcon ?></span>
    <?php if ($deviceImageUrl): ?>
        <img src="<?= htmlspecialchars($deviceImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) ($item['name'] ?? 'Ảnh sản phẩm'), ENT_QUOTES, 'UTF-8') ?>" width="56" height="56" loading="lazy" decoding="async" style="width: 100%; height: 100%; padding: 4px; object-fit: contain;" onerror="this.hidden = true; this.style.display = 'none'; this.previousElementSibling.hidden = false;">
    <?php endif; ?>
</span>
