<!--<button type="submit" value="{{ $label }}" {{ $attributes->merge(['class' => 'btn btn-secondary']) }} >{{ $label }}</button>-->

@props([
    'name',
    'label' => null,
    'href' => 'javascript:void(0)',
])

<a 
    href="{{ $href }}" 
    id="{{ $name }}" 
    {{ $attributes->class(['btn btn-secondary']) }}
>
    @if($attributes->has('required'))
        <i class="fas fa-edit action-btn"></i>
    @else
        {{ $label }}
    @endif
</a>