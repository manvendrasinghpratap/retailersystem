@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 3,
    'mainrows' => 6,
])
<div class="col-xl-{{ $mainrows }} col-md-6">
	<div class="form-group mb-3">
		<label for="{{ $name }}">{{ $label ?? Str::title(str_replace('_', ' ', $name)) }} 
		@if($attributes->get('required'))<span class="required error_{{ $name }}"> *</span>@endif
		</label>
		<textarea
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $placeholder ?? $label }}"
        {{ $attributes->merge(['class' => 'form-control']) }}
        rows="{{ $rows ?? 3 }}"
		>{{ old($name, $value ?? '') }}</textarea>
	</div>
	@error($name)
		<div class = "required text-sm text-red-600 mt-1">{{ $message }}</div>
	@enderror
</div>

