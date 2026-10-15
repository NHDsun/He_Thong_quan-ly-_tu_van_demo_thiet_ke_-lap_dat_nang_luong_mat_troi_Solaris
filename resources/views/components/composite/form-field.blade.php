@props([
    'label',
    'name',
    'type' => 'text',
    'value' => '',
    'error' => null,
    'unit' => null,
    'placeholder' => ''
])

<div class="sol-field-group">
    <label for="{{ $name }}" class="sol-field-label">
        <span>{{ $label }}</span>
        @if($unit)
            <span class="sol-text-muted" style="font-size: 0.8rem; font-weight: normal;">({{ $unit }})</span>
        @endif
    </label>
    <x-base.input 
        id="{{ $name }}" 
        name="{{ $name }}" 
        :type="$type" 
        :value="$value" 
        :placeholder="$placeholder"
        :hasError="!empty($error)" 
        {{ $attributes }} 
    />
    @if($error)
        <span class="sol-field-error">{{ $error }}</span>
    @endif
</div>
