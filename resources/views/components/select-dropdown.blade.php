@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 3,
    'mainrows' => 4,
    'nolabel' => false,
    'noselect' => false,
])
<div class="col-xl-{{ $mainrows }} col-md-6">
<div class="form-group mb-3">
    @php
    $showLabel = isset($nolabel) ? !$nolabel : true;
    @endphp
    @php
    $showSelect = isset($noselect) ? !$noselect : true;
    @endphp
    @if($showLabel)
    <label for="{{ $name }}"  > {{ $label ?? Str::title(str_replace('_', ' ', $name)) }} @if($attributes->get('required'))<span class="required error_{{ $name }}"> *</span>@endif</label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
		{{ $attributes->merge(['class' => 'form-control','id' => $name]) }}
        {{ $attributes }}
    >
		@if($showSelect)
        <option value=''>Select {{$label}}</option>
        @endif
        @foreach ($options as $key => $text)
            <option value="{{ $key }}" {{ (string)$key === (string)old($name, $selected) ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>
	</div>
    @error($name)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
