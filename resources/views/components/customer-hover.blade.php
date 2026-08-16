<span class="customer-hover text-primary" style="cursor: pointer;" data-customer-popover data-bs-placement="right" data-bs-html="true" data-bs-content="
        <div class='customer-popover-content' style='min-width:250px;'>
            @if(!empty($customer->name))
                <div class='fw-bold mb-2'>
                    <i class='fas fa-user me-1'></i>
                    {{ e($customer->name ?? '-') }}
                </div>
            @endif
            @if(!empty($customer->phone))
                <div class='mb-2'>
                    <i class='fas fa-phone me-1'></i>
                    {{ e($customer->phone ?? '-') }}
                </div>
            @endif
            @if(!empty($customer->email))
                <div class='mb-2'>
                    <i class='fas fa-envelope me-1'></i>
                    {{ e($customer->email ?? '-') }}
                </div>
            @endif
            @if(!empty($customer->address))
                <div class='mb-2'>
                    <i class='fas fa-map-marker-alt me-1'></i>
                    {{ e($customer->address) }}
                </div>
            @endif
            @if(!empty($customer->delivery_type))
                <div class='mb-2'>
                    <i class='fas fa-tags me-1'></i>
                    {{ \App\Helpers\Settings::getDataTitle(e($customer->delivery_type)) }}
                </div>
            @endif
            @if(!empty($customer->delivery_address))
                <div class='mb-2'>
                    <i class='fas fa-map-marker-alt me-1'></i>
                    {{ e($customer->delivery_address) }}
                </div>
            @endif
            @if(!empty($customer->delivery_notes))
                <div class='mb-2'>
                    <i class='fas fa-sticky-note me-1'></i>
                    {{ e($customer->delivery_notes) }}
                </div>
            @endif
            @if(!empty($customer->delivery_charge))
                <div class='mb-2'>
                    <i class='fas fa-credit-card me- me-1'></i>
                    {{ __('translation.b_ngn') }} {{ e($customer->delivery_charge) }}
                </div>
            @endif
        </div>
    ">
    {{ $slot }}
</span>