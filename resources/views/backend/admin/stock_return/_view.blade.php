<div class="row mb-3">
	<div class="col-md-4 text-muted"><strong>{{ __('translation.return_no') }}:</strong> {{ $return->return_no }}</div>
	<div class="col-md-4 text-muted"><strong>{{ __('translation.vendor') }}:</strong> {{ $return->vendor->name }}</div>
	<div class="col-md-4 text-muted"><strong>{{ __('translation.warehouse') }}:</strong> {{ $return->warehouse->name }}</div>
	<div class="col-md-4 text-muted mt-2"><strong>{{ __('translation.date') }}:</strong> {{ \App\Helpers\Settings::getFormattedDatetime($return->created_at) }}</div>
</div>

<table class="table table-bordered">
	<thead>
		<tr>
			<th>#</th>
			<th>{{ __('translation.product') }}</th>
			<th>{{ __('translation.quantity') }}</th>
			<th>{{ __('translation.price') }}</th>
			<th>{{ __('translation.total') }}</th>
		</tr>
	</thead>

	<tbody>
		@foreach($return->items as $item)
		<tr>
			<td>{{ $loop->iteration }}</td>
			<td>{{ $item->product->name }}</td>
			<td>{{ $item->qty }}</td>
			<td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($item->price) }}</td>
            <td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($item->total) }}</td>
		</tr>
		@endforeach
	</tbody>
</table>

<div class="text-end">
	<h4>{{ __('translation.total')}} : {{ __('translation.currency')}} {{$return->total}} {{-- \App\Helpers\Settings::getcustomnumberformat($return->total) --}}</h4>
</div>
