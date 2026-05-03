@props([
    'name',
    'label' => null,
    'href' => 'javascript:void(0)',
    'action' => 'edit',
])

<a 
    href="{{ $href }}" 
    id="{{ $name }}" 
    {{ $attributes->class(['']) }}
>
    <i class="fas fa-trash action-btn text-danger" title="Delete"></i>
</a>
