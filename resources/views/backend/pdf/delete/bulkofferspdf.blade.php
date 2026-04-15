@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')
		<table id="datatable-buttons-" class="table dt-responsive table-bordered table-hover table-striped table-nowrap w-100">
			<thead>
				<tr>
					<th>#</th>
					<th>@lang('translation.product')</th>
					<th>@lang('translation.buy_quantity')</th>
					<th>@lang('translation.free_menu')</th>
					<th>@lang('translation.free_quantity')</th>
					<th>@lang('translation.offer_start_date')</th>
					<th>@lang('translation.offer_end_date')</th>
					<th>@lang('translation.status')</th>
				</tr>
			</thead>
			<tbody>
				@forelse($bulkOffers as $offer)
					<tr>
						<td>{{ $loop->iteration }}</td>
						<td>{{ $offer->menu->title ?? '-' }}</td>
						<td>{{ $offer->buy_quantity }}</td>
						<td>{{ $offer->freeMenu->title ?? '-' }}</td>
						<td>{{ $offer->free_quantity }}</td>
						<td>{{ $offer->start_date ?? '-' }}</td>
						<td>{{ $offer->end_date ?? '-' }}</td>
						<td>
							@if($offer->is_active)
								<span>@lang('translation.active')</span>
							@else
								<span>@lang('translation.inactive')</span>
							@endif
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="8" class="text-center">@lang('translation.no_data_found')</td>
					</tr>
				@endforelse
			</tbody>
		</table>
@endsection
