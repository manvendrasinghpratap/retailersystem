@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')
							<table class="table table-striped align-middle">
												<thead>
																<tr>
																				<th>#</th>
																				<th>{{ __('translation.first_name') }}</th>
																				<th>{{ __('translation.last_name') }}</th>
																				<th>{{ __('translation.phone') .' '. __('translation.number') }}</th>
																				<th>{{__('translation.status')}}</th>
																				<th>{{__('translation.createdat')}}</th>
																</tr>
												</thead>
												<tbody>
													@forelse($customers as $customer)
																	<tr>
																				<td>{{ $loop->iteration }}</td>
																				<td>{{ $customer->customer_suffix .' '. $customer->first_name }}</td>
																				<td>{{ $customer->last_name }}</td>
																				<td>{{ $customer->phone_no }}</td>
																				<td>
																					<span class="badge bg-{{ $customer->status ? 'success' : 'danger' }}">
																						{{ $customer->status ? 'Active' : 'Inactive' }}
																					</span>
																				</td>
																				<td>{{ \App\Helpers\Settings::getFormattedDatetime($customer->created_at) }}</td>
																	</tr>
															@empty
																<tr>
																	<td colspan="7" class="text-center">No customers found</td>
																</tr>
															@endforelse
												</tbody>
							</table>
@endsection
