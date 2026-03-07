@props([
	'mainrows' => '4'
])
<div class="col-xl-{{ $mainrows }} col-md-6">
	<div class="form-group mb-3">
		<label for="{{ $name }}">{{ $label ?? Str::title(str_replace('_', ' ', $name)) }} 
		@if($attributes->get('required'))<span class="required error_{{ $name }}"> </span>@endif
		</label>
		<!--<input name="hire_date" id="hire_date" data-year="1970" type="text" class="form-control hire_date flatdatepickr" required=""   value=""  placeholder="dd/mm/yy" >-->
		<input
        type="text"
        name="{{ $name }}"
		placeholder="{{ $label }}"
        value="{{ old($name, $value ?? '') }}"
		{{ $attributes->merge(['class' => 'form-control date_picker','id' => $name]) }}
        {{ $attributes }}
    >
	</div>
	@error($name)
		<div class = "required text-sm text-red-600 mt-1">{{ $message }}</div>
	@enderror
</div>

