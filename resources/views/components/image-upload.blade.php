@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 3,
    'mainrows' => 4,
])
<div class="col-xl-{{ $mainrows }} col-md-6">
    <div class="form-group mb-3">
        <label for="{{ $name }}">
            {{ $label ?? Str::title(str_replace('_', ' ', $name)) }}
            @if($attributes->get('required'))
                <span class="required error_{{ $name }}"> *</span>
            @endif
        </label>
        
        <input 
			type="file" 
			name="{{ $name }}" 
			id="{{ $name }}" 
			class="form-control"
			accept="image/*"
		>
		@if ($value)
        <!--<div style="margin-top: 10px;">
            <img src="{{ asset($value) }}" alt="Preview" style="max-height: 100px;">
        </div>-->
		@endif
    </div>

    @error($name)
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>



<!---
<div class="form-group">
    <label for="{{ $name }}">{{ $label }}</label>
    <input 
        type="file" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        class="form-control"
        accept="image/*"
    >
    @if ($value)
        <div style="margin-top: 10px;">
            <img src="{{ asset($value) }}" alt="Preview" style="max-height: 100px;">
        </div>
    @endif
    @error($name)
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>-->
