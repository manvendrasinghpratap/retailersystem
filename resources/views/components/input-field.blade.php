@props([
    'id',
    'label',
    'type' => 'text',
    'name',
    'value' => '',
    'placeholder' => '',
    'disabled' => false,
    'labelstatus' => true,
    'iconLibrary' => 'line', // line | bootstrap | fontawesome
])

<div class="mb-1">

    @if($labelstatus)
        <label for="{{ $id }}" class="form-label">
            {{ $label ?? Str::title(str_replace('_', ' ', $name)) }}

            @if($attributes->get('required'))
                <span class="required error_{{ $name }}"> *</span>
            @endif
        </label>
    @endif

    @if($type == 'password')

        <div class="position-relative">
                <input
                    id="{{ $id }}"
                    type="{{ $type }}"
                    name="{{ $name }}"
                    value="{{ old($name, $value) }}"
                    placeholder="{{ $placeholder }}"
                    @disabled($disabled)
                    {{ $attributes->merge(['class' => 'form-control pe-5 '.($errors->has($name)?'is-invalid':'')]) }}
                >
                <span class="toggle-password"
                    data-target="{{ $id }}"
                    data-library="{{ $iconLibrary }}"
                    style="position:absolute;right:15px;top:50%;transform:translateY(-50%);cursor:pointer;z-index:1000;">
                    @if($iconLibrary == 'bootstrap')
                        <i class="bi bi-eye"></i>
                    @elseif($iconLibrary == 'fontawesome')
                        <i class="fa fa-eye"></i>
                    @else
                        <i class="las la-eye"></i>
                    @endif

                </span>
        </div>

    @else

        <input
            id="{{ $id }}"
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @disabled($disabled)
            {{ $attributes->merge([
                'class' => 'form-control ' . ($errors->has($name) ? 'is-invalid' : '')
            ]) }}
        >

    @endif

    @error($name)
        <small class="invalid-feedback text-danger">{{ $message }}</small>
    @enderror

</div>