@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')
    <table class="table table-striped align-middle">
											<thead>
															<tr>
																			<th>#</th>
																			<th>{{ __('translation.product')}}</th>
																			<th>{{ __('translation.type')}}</th>
																			<th>{{ __('translation.quantity')}}</th>
																			<th>{{ __('translation.old')}} {{ __('translation.quantity')}}</th>
																			<th>{{ __('translation.new')}} {{ __('translation.quantity')}}</th>
																			<th>{{ __('translation.staff')}}</th>
																			<th>{{ __('translation.transaction')}} {{ __('translation.date')}}</th>
															</tr>
											</thead>
												<tbody> 
																	@forelse ($movements as $m)
																				<tr>
																								<td>{{ $loop->iteration }}</td>
																								<td>{{ $m->product->title ?? 'N/A' }}</td>
																								<td>{{ $m->type }}</td>
																								<td>{{ $m->quantity }}</td>
																								<td>{{ $m->old_quantity }}</td>
																								<td>{{ $m->new_quantity }}</td>
																								<td>{{ @$m->staff->staff_name }}</td>
																								<td>{{ App\Helpers\Settings::getFormattedDate($m->date)}}</td>
																				</tr>
																	@empty
																					<tr>
																						<td colspan="7" class="text-center">@lang('translation.no_data_found')</td>
																					</tr>
																	@endforelse
												</tbody>
    </table>
	
@endsection
