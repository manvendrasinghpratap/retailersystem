@props([
    'submitText' => 'Submit',
    'resetText' => 'Reset',
    'url' => 'javascript:void(0)',
    'isbutton' => true,   // boolean to show/hide submit button
    'iscancel' => true,   // boolean to show/hide cancel/reset button
	'mainrows' => '6'
])

<div class="col-xl-{{ $mainrows }} col-md-6">
    <div class="mb-0 d-flex gap-2 dflex mt-4">
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
