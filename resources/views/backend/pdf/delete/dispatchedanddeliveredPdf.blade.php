@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')
										<table class="table table-striped align-middle">
																<thead>
																					<tr>
																										<th>#</th>
																										<th>{{ __('translation.customer') }}</th>
																										<th>{{ __('translation.product') }}</th>
																										<th>{{ __('translation.delivery_option') }}</th>
																										<th>{{ __('translation.delivery_staff') }}</th>
																										<th>{{ __('translation.delivery_location') }}</th>
																										<th>{{ __('translation.delivery_date') }}</th>
																										<th>{{ __('translation.order') .' '.__('translation.status') }}</th>
																										<th>{{ __('translation.payment') .' '.__('translation.status') }}</th>
																										<th>{{ __('translation.ordered_at') }}</th>
																					</tr>
																</thead>
										<tbody>
											@forelse($orders as $order)
												<tr>
													<td>{{ $loop->iteration }}</td>
													<td>{{ $order->customer->customer_name }}</td>
													<td class="productdiv">
														@foreach ($order->orderdetails as $detail)
														{{ $detail->menus->title ?? '' }} ({{ $detail->quantity ?? 0 }})@if(!$loop->last), @endif
														@endforeach
													</td>
													{{-- <td>{{ \App\Helpers\Settings::getcustomnumberformat($order->total_amount) }}</td> --}}
													<td>{{ $order->orderdelivery->delivery_option }}</td>
													<td>{{ @$order->orderdelivery->staff->name }}</td>
													<td>{{ $order->orderdelivery->delivery_location }}</td>
													<td>{{ $order->orderdelivery->delivery_date}}</td>
													<td @if($order->status == 'delivered' ) title="{{ __('translation.deliver_to') . ' : ' . ($order->orderdelivery->deliver_to ?? '') . ' | ' . __('translation.notes') . ' : ' . ($order->orderdelivery->note ?? '') . ' | ' . __('translation.delivered_date') . ' : ' . ($order->orderdelivery->delivered_date ? date(config('constants.dateformat.slashdmyonly'), strtotime($order->orderdelivery->delivered_date)) : '') }}" 
														@elseif($order->status == 'delivery-pending')
														title="{{ __('translation.delivery_option') . ' : ' . ($order->orderdelivery->delivery_option ?? '') . ' | ' . __('translation.delivery_location') . ' : ' . ($order->orderdelivery->delivery_location ?? '') . ' | ' . __('translation.delivery_staff') . ' : ' . (@$order->orderdelivery->staff->name ?? '')  . ' | ' . __('translation.delivered_date') . ' : ' . ($order->orderdelivery->delivered_date ? date(config('constants.dateformat.slashdmyonly'), strtotime($order->orderdelivery->delivered_date)) : '') }}"
														@endif>
														<span data-currentlocation = "{{ $order->customer->current_location }}" data-orderdeliveryid="{{ $order->orderdelivery->id }}" data-orderid="{{ $order->id }}" data-orderstatus="{{$order->status}}" class="{{$order->status == 'delivery-pending' ? 'update_order_status_to_delivered': 'order_status_not_confirmed'}} badge bg-{{ $order->status == 'delivered' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'warning') }}"
														{{-- data-delivery_option="{{ $order->orderdelivery->delivery_option ?? '' }}"
														data-delivery_date="{{ $order->orderdelivery->delivery_date ?? '' }}"
														data-delivery_staff_name="{{ $order->orderdelivery?->staff?->name ?? '' }}"
														data-delivery_location="{{ $order->orderdelivery->delivery_location ?? '' }}"
														data-note="{{ $order->orderdelivery->note ?? '' }}"
														data-deliver_to="{{ $order->orderdelivery->deliver_to ?? '' }}" --}}
														data-delivered="{{ 
														__('translation.deliver_to') . ' : ' . ($order->orderdelivery?->deliver_to ?? '') . 
														' | ' . __('translation.notes') . ' : ' . ($order->orderdelivery?->note ?? '') . 
														' | ' . __('translation.delivered_date') . ' : ' . 
														($order->orderdelivery?->delivered_date ? date(config('constants.dateformat.slashdmyonly'), strtotime($order->orderdelivery->delivered_date)) : '') 
														}}"
														data-deliverypending="{{ 
														__('translation.delivery_option') . ' : ' . ($order->orderdelivery?->delivery_option ?? '') . 
														' | ' . __('translation.delivery_location') . ' : ' . ($order->orderdelivery?->delivery_location ?? '') . 
														' | ' . __('translation.near_by') . ' : ' . ($order->orderdelivery->customer?->near_by ?? '') . 
														' | ' . __('translation.delivery_staff') . ' : ' . ($order->orderdelivery?->staff?->name ?? '') . 
														' | ' . __('translation.delivery_date') . ' : ' . 
														($order->orderdelivery?->delivery_date ? date(config('constants.dateformat.slashdmyonly'), strtotime($order->orderdelivery->delivery_date)) : '') 
														}}"
														>{{ ucfirst($order->status) }}
													</span>
													</td>
													<td>
														<span data-orderid="{{ $order->id }}" data-paymentstatus="{{$order->payment_status}}" class="{{$order->payment_status != 'completed' ? 'update_payment_status--': 'payment_status_confirmed'}} badge bg-{{ $order->payment_status == 'completed' ? 'success' : ($order->payment_status == 'cancelled' ? 'danger' : 'warning') }}">
															{{ ucfirst($order->payment_status) }}
														</span>
													</td>
													<td>{{ \App\Helpers\Settings::getFormattedDate($order->ordered_at) }}</td>
												</tr>
											@empty
												<tr>
													<td colspan="11" class="text-center">{{ __('translation.no_orders_found') }}</td>
												</tr>
											@endforelse
										</tbody>
										</table>
@endsection
