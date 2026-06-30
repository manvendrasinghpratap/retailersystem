@props([
    'name',
    'label' => null,
    'value' => null,          // Existing file path
    'mainrows' => 4,
    'islabel' => true,
    'labelclass' => '',
    'preview' => true,
])

<div class="col-xl-{{ $mainrows }} col-md-6 mb-3">

    {{-- Label --}}
    @if($islabel)
        <label for="{{ $name }}" class="form-label {{ $labelclass }}">
            {!! $label ?? Str::title(str_replace('_', ' ', $name)) !!}

            @if($attributes->has('required'))
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    {{-- File Input --}}
    <input
        type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')
        ]) }}
    >

    {{-- Validation Error --}}
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

    {{-- Existing File Preview --}}
    @if($preview && !empty($value))
        @php
            $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        @endphp

        <div class="mt-2">

            @if(in_array($extension, $imageExtensions))
                <img src="{{ asset('storage/'.$value) }}"
                     alt="{{ $label }}"
                     class="img-thumbnail"
                     style="max-height:80px;">
            @else
                <a href="{{ asset('storage/'.$value) }}"
                   target="_blank"
                   class="btn btn-sm btn-outline-primary">
                    <i class="mdi mdi-file"></i> View Current File
                </a>
            @endif

        </div>
    @endif

</div>