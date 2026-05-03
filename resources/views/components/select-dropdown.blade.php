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
            $showSelect = isset($noselect) ? !$noselect : true;
        @endphp

        @if($showLabel)
            <label for="{{ $name }}">
                {{ $label ?? Str::title(str_replace('_', ' ', $name)) }}

                @if($attributes->get('required'))
                    <span class="text-danger">*</span>
                @endif
            </label>
        @endif

        <select
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $attributes->merge([
                'class' => 'form-control ' . ($errors->has($name) ? 'is-invalid' : '')
            ]) }}
        >

            @if($showSelect)
                <option value="">Select {{ $label }}</option>
            @endif

            @foreach ($options as $key => $text)
                <option value="{{ $key }}"
                    {{ (string)$key === (string)old($name, $selected) ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach

        </select>

        @error($name)
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror

    </div>
</div>