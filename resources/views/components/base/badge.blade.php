@props([
    'variant' => 'corona', // corona | emerald | ocean | danger
])

<span {{ $attributes->merge(['class' => "sol-badge sol-badge--{$variant}"]) }}>
    {{ $slot }}
</span>
