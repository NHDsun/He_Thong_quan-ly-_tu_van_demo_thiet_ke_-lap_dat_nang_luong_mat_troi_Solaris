<?php
declare(strict_types=1);

use App\Hooks\UseProductFilters;

$priceStep = UseProductFilters::priceStep($priceCategorySlug);
$priceEscape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<span class="product-price-control" data-product-price data-price-steps="<?= $priceEscape(json_encode(UseProductFilters::PRICE_STEPS, JSON_THROW_ON_ERROR)) ?>" data-price-label="<?= $priceEscape($priceInputLabel) ?>">
    <button type="button" data-price-direction="-1" aria-label="<?= $priceEscape('Giảm ' . $priceInputLabel) ?>">−</button>
    <input id="product-price-<?= $priceEscape($priceInputName) ?>" class="products-input" type="number" inputmode="decimal" name="<?= $priceEscape($priceInputName) ?>" min="0" step="any" value="<?= $priceEscape($priceInputValue) ?>" placeholder="<?= $priceEscape($pricePlaceholder ?? 'Không giới hạn') ?>" <?= !empty($priceInputRequired) ? 'required' : '' ?> aria-invalid="<?= !empty($priceInputInvalid) ? 'true' : 'false' ?>" aria-describedby="product-price-hint-<?= $priceEscape($priceInputName) ?>">
    <button type="button" data-price-direction="1" aria-label="<?= $priceEscape('Tăng ' . $priceInputLabel) ?>">+</button>
</span>
<small id="product-price-hint-<?= $priceEscape($priceInputName) ?>" class="product-price-step-hint" data-price-step-hint="<?= $priceEscape($priceInputName) ?>">Bước ±<?= number_format($priceStep, 0, ',', '.') ?> đ</small>
