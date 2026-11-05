<?php declare(strict_types=1); ?>
<article class="product-advice-card product-advice-card--<?= htmlspecialchars($rule['type'], ENT_QUOTES, 'UTF-8') ?>">
    <span class="product-advice-icon" aria-hidden="true"><?= htmlspecialchars($rule['icon'] ?? '📋', ENT_QUOTES, 'UTF-8') ?></span>
    <div class="product-advice-content">
        <div class="product-advice-title"><h3><?= htmlspecialchars($rule['title'], ENT_QUOTES, 'UTF-8') ?></h3><span class="product-advice-status"><?= htmlspecialchars($rule['status_label'], ENT_QUOTES, 'UTF-8') ?></span></div>
        <p><?php include __DIR__ . '/product-compatibility-detail.blade.php'; ?></p>
    </div>
    <?php if ($rule['action_url'] && !$hasInlineHybridLink): ?>
        <a class="product-advice-action compatibility-action-link" href="<?= htmlspecialchars($rule['action_url'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rule['action_label'], ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">→</span></a>
    <?php endif; ?>
</article>
