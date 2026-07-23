<div class="row mb-3">
	<div class="col-md-6 text-muted"><strong>{{ __('translation.return_no') }}:</strong> {{ $return->return_no }}</div>
	<div class="col-md-6 text-muted"><strong>{{ __('translation.date') }}:</strong> {{ \App\Helpers\Settings::getFormattedDatetime($return->created_at) }}</div>
	<br>
	<br>
	<div class="col-md-6 text-muted"><strong>{{ __('translation.vendor') }} {{ __('translation.details') }}:-</strong>
		<br><strong>{{__('translation.company_name')}}</strong> : {{ $return->vendor->company_name ?? 'N/A' }}
		<br><strong>{{__('translation.managed_by')}}</strong> : {{ $return->vendor->name }}
		<br><strong>{{__('translation.phone')}}</strong> : {{ $return->vendor->phone }}
		<br><strong>{{__('translation.email')}}</strong> : {{ $return->vendor->email }}
		<br><strong>{{__('translation.address')}}</strong> : {{ $return->vendor->address }}
	</div>
	<div class="col-md-6 text-muted"><strong>{{ __('translation.warehouse') }} {{ __('translation.details') }}:-</strong>
		<br><strong>{{ __('translation.name')}}</strong> : {{ $return->warehouse->name }}
		<br><strong>{{ __('translation.managed_by')}}</strong> : {{ $return->warehouse->manager_name ?? 'N/A' }}
		<br><strong>{{ __('translation.phone')}}</strong> : {{ $return->warehouse->phone }}
		<br><strong>{{ __('translation.email')}}</strong> : {{ $return->warehouse->email }}
		<br><strong>{{ __('translation.address')}}</strong> : {{ $return->warehouse->address }}
	</div>
</div>

<table class="table table-bordered">
	<thead>
		<tr>
			<th>#</th>
			<th>{{ __('translation.product') }}</th>
			<th>{{ __('translation.quantity') }}</th>
			<th>{{ __('translation.price') }}</th>
			<th>{{ __('translation.total') }}</th>
			<th>{{ __('translation.reason') }}</th>
		</tr>
	</thead>

	<tbody>
		@foreach($return->items as $item)
			<tr>
				<td>{{ $loop->iteration }}</td>
				<td>{{ $item->masterItem->name ?? 'N/A' }}</td>
				<td>{{ $item->qty }}</td>
				<td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($item->price) }}</td>
				<td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($item->total) }}</td>
				<td>{{ $item->reason }}</td>
			</tr>
		@endforeach
	</tbody>
</table>

<div class="text-end">
	<h4>{{ __('translation.total')}} : {{ __('translation.currency')}} {{$return->total}}</h4>
</div>