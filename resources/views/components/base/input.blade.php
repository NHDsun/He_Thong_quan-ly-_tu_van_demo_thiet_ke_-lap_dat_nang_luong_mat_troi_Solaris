@props([
    'type' => 'text',
    'hasError' => false
])

<input 
    type="{{ $type }}" 
    {{ $attributes->merge([
        'class' => 'sol-input ' . ($hasError ? 'sol-input--error' : '')
    ]) }}
/>
