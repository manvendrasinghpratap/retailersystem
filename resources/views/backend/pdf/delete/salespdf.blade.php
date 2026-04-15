@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')
<table class="table table-striped align-middle">
	<thead>
		<tr>
			<th>#</th>
			<th>{{ __('translation.date') }}</th>
			<th>{{ __('translation.staff') }}/{{ __('translation.customer') }}</th>
			<th>{{ __('translation.product') }}</th>
			<th>{{ __('translation.taken') }}</th>
			<th>{{ __('translation.incentive') }}</th>
			<th>{{ __('translation.sold') }}</th>
			<th>{{ __('translation.returned') }}</th>
			<th>{{ __('translation.type') }}</th>
		</tr>
	</thead>
	<tbody>
		@forelse($finalRecords as $i => $row)
			<tr>
				<td>{{ $i + 1 }}</td>
				<td>{{ \App\Helpers\Settings::getFormattedDate($row['date']) }}</td>
					<td>{{ $row['staff'] }}</td>
				<td>{{ $row['products'] }}</td>
				<td>{{ $row['taken'] }}</td>
				<td>{{ $row['incentive'] }}</td>
				<td>{{ $row['sold'] }}</td>
				<td>{{ $row['returned'] }}</td>
				<td>		@if(!empty($row['order_id']))
					<span class="badge bg-success">(From Order #{{ $row['order_id'] }})</span>
					@else
					<span class="badge bg-secondary">(Manual Sale)</span>
					@endif</td>
			</tr>
		@empty
			<tr>
				<td colspan="9" class="text-center">{{ __('translation.no_sales_found') }}</td>
			</tr>
		@endforelse
	</tbody>

	@if(!empty($totals) && ($totals['taken'] > 0 || $totals['sold'] > 0 || $totals['returned'] > 0))
		<tfoot>
			<tr>
				<td colspan="4" class="text-end"><strong>Total</strong></td>
				<td style="text-align:center;"><strong>{{ $totals['taken'] }}</strong></td>
				<td style="text-align:center;"><strong>{{ $totals['incentive_taken'] }}</strong></td>
				<td style="text-align:center;"><strong>{{ $totals['sold'] }}</strong></td>
				<td style="text-align:center;"><strong>{{ $totals['returned'] }}</strong></td>
				<td></td>
			</tr>
		</tfoot>
	@endif
</table>

@endsection
