@props([
    'variant' => 'solar', // solar | secondary | outline | danger
    'type' => 'button',
    'size' => 'md'        // sm | md | lg
])

<button 
    type="{{ $type }}" 
    {{ $attributes->merge([
        'class' => "sol-btn sol-btn--{$variant} " . ($size === 'lg' ? 'sol-btn--lg' : ($size === 'sm' ? 'sol-btn--sm' : ''))
    ]) }}
>
    {{ $slot }}
</button>
