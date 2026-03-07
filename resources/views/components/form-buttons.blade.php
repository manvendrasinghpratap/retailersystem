@props([
    'submitText' => 'Submit',
    'resetText' => 'Reset',
    'url' => 'javascript:void(0)',
    'isbutton' => true,   // boolean
    'iscancel' => true,   // boolean
])

<div class="form-group center">
    <div class="d-flex gap-2 dflex">
        @if($isbutton)
            <button type="submit" {{ $attributes->merge(['class' => 'btn btn-primary']) }}>
                {{ $submitText }}
            </button>
        @endif    

        @if($iscancel)
            <a href="{{ $url }}" class="btn btn-secondary">{{ $resetText }}</a>
        @endif    
    </div>
</div>
