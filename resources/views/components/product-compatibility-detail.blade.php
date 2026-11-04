<?php
declare(strict_types=1);

use App\Hooks\UseProductFilters;

$hybridPhrase = 'Biến tần HYBRID (Deye / GoodWe Hybrid)';
$detailText = (string) $rule['detail'];
$hasInlineHybridLink = ($rule['type'] ?? '') !== 'success' && str_contains($detailText, $hybridPhrase);
if ($hasInlineHybridLink):
    [$beforeHybrid, $afterHybrid] = explode($hybridPhrase, $detailText, 2);
    $hybridUrl = UseProductFilters::url(UseProductFilters::read(['category' => 'inverter', 'system_type' => 'Hybrid'])['filters']);
    ?><?= htmlspecialchars($beforeHybrid, ENT_QUOTES, 'UTF-8') ?><a class="compatibility-inline-product-link" href="<?= htmlspecialchars($hybridUrl, ENT_QUOTES, 'UTF-8') ?>"><?= $hybridPhrase ?></a><?= htmlspecialchars($afterHybrid, ENT_QUOTES, 'UTF-8') ?><?php
else:
    echo htmlspecialchars($detailText, ENT_QUOTES, 'UTF-8');
endif;
