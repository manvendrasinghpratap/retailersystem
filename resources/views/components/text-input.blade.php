@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'mainrows' => 4,
    'islabel' => true,
])

<div class="col-xl-{{ $mainrows }} col-md-6 mb-3">
    
    {{-- Label --}}
    @if($islabel)
        <label for="{{ $name }}" class="form-label">
            {{ $label ?? Str::title(str_replace('_', ' ', $name)) }}
            @if($attributes->get('required'))
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    {{-- Input --}}
    <input
        type="{{ $attributes->get('type', 'text') }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value ?? '') }}"
        placeholder="{{ $placeholder ?? $label ?? '' }}"
        {{ $attributes->merge(['class' => 'form-control']) }}
    >

    {{-- Validation Error --}}
    @error($name)
        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
    @enderror

</div>
