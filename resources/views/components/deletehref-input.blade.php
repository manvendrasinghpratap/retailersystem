@props([
    'name',
    'label' => null,
    'href' => 'javascript:void(0)',
])

<a 
    href="{{ $href }}" 
    id="{{ $name }}" 
    {{ $attributes->class(['']) }}
>
    @if($attributes->has('required'))
        <i class="fas fa-trash action-btn darkred"></i>
    @else
        {{ $label }}
    @endif
</a>
