<div class="col-xl-4 col-md-6">
	<div class="form-group mb-3">
		<label for="{{ $name }}">{{ $label ?? Str::title(str_replace('_', ' ', $name)) }} 
		@if($attributes->get('required'))<span class="required error_{{ $name }}"> *</span>@endif
		</label>
		<input
        type="password"
        name="{{ $name }}"
		placeholder="{{ $label }}"
        value="{{ old($name, $value ?? '') }}"
		{{ $attributes->merge(['class' => 'form-control','id' => $name]) }}
        {{ $attributes }}
    >
	</div>
	@error('name')
		<div class = "required text-sm text-red-600 mt-1">{{ $message }}</div>
	@enderror
</div>

