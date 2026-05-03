<p><strong>{{ __('translation.purchase_no')}} :</strong> {{ $purchase->purchase_no }}</p>
<p><strong>{{ __('translation.vendor')}} :</strong> {{ $purchase->vendor->name }}</p>
<p><strong>{{ __('translation.warehouse')}} :</strong> {{ $purchase->warehouse->name }}</p> 
<p><strong>{{ __('translation.date')}} :</strong> {{ \App\Helpers\Settings::getFormattedDatetime($purchase->created_at) }}</p>

<hr>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>{{ __('translation.product')}}</th>
            <th>{{ __('translation.quantity')}}</th>
            <th> {{ __('translation.currency')}} {{ __('translation.price')}}</th>
            <th> {{ __('translation.currency')}} {{ __('translation.total')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchase->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($item->cost_price) }}</td>
                <td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($item->total) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="text-end">
    <h4>{{ __('translation.total')}} : {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($purchase->total) }}</h4>
</div>