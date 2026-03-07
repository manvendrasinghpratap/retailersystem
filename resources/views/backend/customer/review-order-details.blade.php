@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? '' }}
@endsection

@section('content')
@include('backend.components.breadcrumb')

{{-- Orders Table --}}
<div class="row">
    <div class="col-12">
        <div class="card">
         <div class="card-header"><h4 class="card-title">{{array_key_exists('routeTitle',$breadcrumb)?$breadcrumb['routeTitle']:''}}</h4></div>
            <div class="card-body">
               <div class="row">
               <div class="label vital-signs">@lang('translation.customer') {{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}}</div>
               </div>
                <div class="table-responsive overflowx">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>@lang('translation.product')</th>
                                <th class="text-center">@lang('translation.quantity')</th>
                                <th class="text-end"><strong class="currency-symbol">{{ __('translation.currency')}}</strong> @lang('translation.price') </th>
                                <th class="text-end"><strong class="currency-symbol">{{ __('translation.currency')}}</strong> @lang('translation.subtotal')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->UserOrderDetail as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $detail->product->title ?? 'Product Removed' }}</td>
                                    <td class="text-center">{{ $detail->quantity }}</td>
                                    <td class="text-end">{{ \App\Helpers\Settings::getcustomnumberformat($detail->price) }}</td>
                                    <td class="text-end">{{ \App\Helpers\Settings::getcustomnumberformat($detail->price * $detail->quantity) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total</th>
                                <th class="text-end">{{ \App\Helpers\Settings::getcustomnumberformat($order->total_amount) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if( empty($order->order_id) || (optional($order->order)->status != 'delivered' && optional($order->order)->status != 'delivery-pending') )
                <div class="row">
                    <div class="label vital-signs">@lang('translation.order') @lang('translation.status') and {{--@lang('translation.payment') @lang('translation.status')--}}  @lang('translation.comment') <span id="payment_error" class="text-danger font-weight-bold"></span></div>
                </div>
                <form action="{{ route($breadcrumb['updateroute']) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="off" id="revieworderForm">
                  @csrf
                 <input type="hidden" name="user_order_id" value="{{ $order->id }}" />
                 <input type="hidden" name="user_id" value="{{ $order->user_id }}" />
                 
                 <div class="row mb-0">
                    @php
                    // Get all statuses from config
                    $statusOptions = \Config::get('constants.customer_order_status');

                    // If order status is 'confirmed', remove 'pending' from the list
                    if (!empty($order->status) && $order->status != 'pending') {
                        unset($statusOptions['pending']);
                    }
                    if (!empty($order->status) && $order->status == 'delivery-pending') {
                        unset($statusOptions['pending']);
                        unset($statusOptions['confirmed']);
                    }
                    @endphp
                     <x-select-dropdown name="customer_order_status" :label="__('translation.order'). ' '. __('translation.status')" :options="$statusOptions" class="accountstatus form-control required-select"  mainrows="3" required :selected="$order->status ?? 'pending'"/>
                     {{-- <x-select-dropdown name="payment_status" :label="__('translation.payment'). ' '. __('translation.status')" :options="\Config::get( 'constants.payment_status' )" class="payment_status form-control required-select"  mainrows="3" required/> --}}
                     <x-textarea-input name="comment" id="comment" :label="__('translation.comment')" value="{{ old('comment', $order->comment ?? '') }}" class="form-control" rows="1" mainrows="9" required />    
                 </div>
                <div class="row mb-3">
                    <div class="form-group mt-6">
                        <x-form-buttons submitText="{{ $submitText ?? 'Update' }}"
                            resetText="{{ __('translation.cancel') }}"
                            url="{{ route(array_key_exists('add_new_route', $breadcrumb) ? $breadcrumb['add_new_route'] : 'javascript:void(0)') }}"
                            class="btn-success" :iscancel="true"  :isbutton=true/>
                    </div>
                </div>
                 @endif
                </form>
            </div>

            </div>
        </div>
    </div>
</div>
@endsection
