<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-main-primary btn-block']) }}>
    {{ $slot }}
</button>
