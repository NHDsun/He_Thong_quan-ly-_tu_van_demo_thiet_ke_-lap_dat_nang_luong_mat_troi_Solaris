@props([
    'icon' => '⚡',
    'value' => '0',
    'unit' => '',
    'label' => ''
])

<div class="solar-glass-card sol-stat-card">
    <div class="sol-stat-card__icon">{{ $icon }}</div>
    <div class="sol-stat-card__value">
        {{ $value }}@if($unit)<span class="unit">{{ $unit }}</span>@endif
    </div>
    <div class="sol-stat-card__label">{{ $label }}</div>
</div>
