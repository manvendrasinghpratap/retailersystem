<div class="row mb-3">
    <div class="col-md-4"><strong>{{ __('translation.requisition_no')}}:</strong> {{ $requisition->requisition_no }}</div>
    <div class="col-md-4"><strong>{{ __('translation.from_warehouse')}}:</strong> {{ $requisition->fromWarehouse->name ?? '-' }}</div>
    <div class="col-md-4"><strong>{{ __('translation.to_warehouse')}}:</strong> {{ $requisition->toWarehouse->name ?? '-' }}</div>
    <div class="col-md-4 mt-3"><strong>{{ __('translation.date')}}:</strong> {{ \App\Helpers\Settings::getFormattedDatetime($requisition->created_at) }}</div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('translation.product') }}</th>
            <th>{{ __('translation.quantity') }}</th>
        </tr>
    </thead>

    <tbody>
        @foreach($requisition->items as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->product->name ?? '-' }}</td>
            <td>{{ $item->qty }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="text-end mt-3">
    <h5>{{ __('translation.total_qty')}}: {{ $requisition->total_qty }}</h5>
</div>