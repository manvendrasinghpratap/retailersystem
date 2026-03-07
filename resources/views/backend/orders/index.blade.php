@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? __('translation.order_listing') }}
@endsection

@section('content')
@include('backend.components.breadcrumb')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>  
                <div class="d-inline-block">
                    @include('backend.components.exportpdfcsv', [
                    'pdfId' =>'downloadorderpdf',    
                    'pdfRoute' => route('order.downloadOrderListing'),
                    'pdfClass' => 'downloadorderpdf',
                    'csvId' =>'downloadordercsv',    
                    'csvRoute' => route('order.downloadCsvOrderListing'),
                    'csvClass' => 'downloadordercsv',
                    ])                 
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                     <form method="GET" action="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : '') }}" class="mb-3"> 
                        <div class="row">
                            <x-text-input name="customer_name" :value="request('customer_name')" :placeholder="__('translation.search_customer_name')" class="form-control" mainrows="2"/>
                            <x-select-dropdown name="staff_id" label="{{ __('translation.staff') }}" :options="$staffList" :selected="request('staff_id')" class="staff" mainrows="2"/>
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="\Config::get( 'constants.order_status' )" :selected="request('status')" class="accountstatus" mainrows="2"/>
                            <x-date-input name="from_date" :label="__('translation.from_date')" value="{{ request('from_date') ?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d',strtotime('-7 days'))) }}" class="flatdatepickr " mainrows="2"/>
                            <x-date-input name="to_date" :label="__('translation.to_date')" value="{{ request('to_date') ?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickrto" mainrows="2"/>
                            <x-button submitText="Filter" resetText="Reset" url="{{ route($breadcrumb['route']??'') }}" isbutton="1" iscancel="1" mainrows="2"/>                       
                        </div>
                    </form>
                 {{-- Filter Form End--}}   
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm rounded-2xl">
            <div class="card-body">
                {{-- Orders Table --}}
                <div class="table-responsive overflowx">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('translation.customer') }}</th>
                                <th>{{ __('translation.product') }}</th>
                                <th> {{__('translation.ngn')}} {{ __('translation.total_amount') }}</th>
                                <th>{{ __('translation.total_quantity') }}</th>
                                <th>{{ __('translation.staff') }}</th>
                                <th>{{ __('translation.order') .' '.__('translation.status') }}</th>
                                <th>{{ __('translation.payment') .' '.__('translation.status') }}</th>
                                <th>{{ __('translation.ordered_at') }}</th>
                                <th>{{ __('translation.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $ii = 0; @endphp
                            @forelse($orders as $order)
                                <tr>
                                    <td><x-href-input name="view" href="{{ route('order.edit',['id' => \App\Helpers\Settings::getEncodeCode($order->id)]) }}" action="no" label="{{ ++$ii }}" /> </td>
                                    <td><a title="{{ $order->comment }}" data-comment="{{ $order->comment }}" data-orderid="{{ $order->id }}" href="javascript:void(0);" class="order_comments">{{ $order->customer->customer_name }}</a></td>
                                    <td class="productdiv">
                                        @foreach ($order->orderdetails as $detail)
                                        {{ $detail->menus->title ?? '' }} ({{ $detail->quantity ?? 0 }})@if(!$loop->last), @endif
                                        @endforeach
                                    </td>
                                    <td>{{ \App\Helpers\Settings::getcustomnumberformat($order->total_amount) }}</td>
                                    <td>{{ $order->total_quantity }}</td>
                                    <td>{{ $order->staff->staff_name }}</td>
                                    <td @if($order->status == 'delivered') 
                                        title="{{ __('translation.deliver_to') . ' : ' . ($order->orderdelivery->deliver_to ?? '') . ' | ' . __('translation.notes') . ' : ' . ($order->orderdelivery->note ?? '') . ' | ' . __('translation.delivered_date') . ' : ' . ($order->orderdelivery->delivered_date ?? '') }}" 
                                        @elseif($order->status == 'delivery-pending')
                                        title="{{ __('translation.delivery_option') . ' : ' . ($order->orderdelivery->delivery_option ?? '') . ' | ' . __('translation.delivery_location') . ' : ' . ($order->orderdelivery->delivery_location ?? '') . ' | ' . __('translation.delivery_staff') . ' : ' . (@$order->orderdelivery->staff->name ?? '')  . ' | ' . __('translation.delivered_date') . ' : ' . ($order->orderdelivery->delivered_date ?? '') }}" 
                                        @endif>
                                        <span data-currentlocation="{{ $order->customer->current_location }}" data-customerid="{{ $order->customer_id }}" data-orderid="{{ $order->id }}" data-orderstatus="{{ $order->status }}" class="{{ $order->status == 'confirmed' ? 'update_order_status' : ($order->status == 'delivery-pending' || $order->status == 'delivered' ? 'order_status_not_confirmed' : '') }} badge bg-{{ $order->status == 'delivered' ? 'success' : ($order->status == 'delivery-pending' ? 'warning' : ($order->status == 'cancelled' ? 'danger' : 'info')) }}" 
                                        {{-- data-delivery_option="{{ $order->orderdelivery->delivery_option ?? '' }}"
                                        data-delivery_date="{{ $order->orderdelivery->delivery_date ?? '' }}"
                                        data-delivery_staff_name="{{ $order->orderdelivery?->staff?->name ?? '' }}"
                                        data-delivery_location="{{ $order->orderdelivery->delivery_location ?? '' }}"
                                        data-note="{{ $order->orderdelivery->note ?? '' }}"
                                        data-deliver_to="{{ $order->orderdelivery->deliver_to ?? '' }}"
                                        data-delivered_date="{{ $order->orderdelivery?->delivered_date ? date(config('constants.dateformat.slashdmyonly'), strtotime($order->orderdelivery->delivered_date)) : '' }}" --}}
                                         data-delivered="{{ 
                                        __('translation.deliver_to') . ' : ' . ($order->orderdelivery?->deliver_to ?? '') . 
                                        ' | ' . __('translation.notes') . ' : ' . ($order->orderdelivery?->note ?? '') . 
                                        ' | ' . __('translation.delivered_date') . ' : ' . 
                                        ($order->orderdelivery?->delivered_date ?? '') 
                                        }}"
                                        data-deliverypending="{{ 
                                        __('translation.delivery_option') . ' : ' . ($order->orderdelivery?->delivery_option ?? '') . 
                                        ' | ' . __('translation.delivery_location') . ' : ' . ($order->orderdelivery?->delivery_location ?? '') . 
                                        ' | ' . __('translation.near_by') . ' : ' . ($order->orderdelivery->customer?->near_by ?? '') . 
                                        ' | ' . __('translation.delivery_staff') . ' : ' . ($order->orderdelivery?->staff?->name ?? '') . 
                                        ' | ' . __('translation.delivery_date') . ' : ' . 
                                        ($order->orderdelivery?->delivery_date ? $order->orderdelivery->delivered_date : '') 
                                        }}"
                                            >{{ ucfirst($order->status) }}</span>
                                    </td>
                                    <td>
                                        @php $adminpop = ((Auth::user()->user_type < 3 || Auth::user()->designation_id == 12) && $order->payment_status == 'completed' && $order->status != 'delivery-pending' && $order->status != 'delivered') ? 'update_payment_status' : ''; @endphp
                                        <span data-orderid="{{ $order->id }}" data-paymentstatus="{{$order->payment_status}}" class="{{$order->payment_status != 'completed' ? 'update_payment_status': $adminpop}} badge bg-{{ $order->payment_status == 'completed' ? 'success' : ($order->payment_status == 'cancelled' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td>{{ \App\Helpers\Settings::getFormattedDate($order->ordered_at) }}</td>
                                    <td>
                                        <x-href-input name="view" href="{{ route('order.edit',['id' => \App\Helpers\Settings::getEncodeCode($order->id)]) }}"  action='view' />
                                        @if( ($order->status == 'delivered') || ($order->status == 'delivery-pending') )
                                            <x-href-input target='_blank' name="pdfOrder" href="{{ route('order.invoice',['id' => \App\Helpers\Settings::getEncodeCode($order->id)]) }}" action="pdf" />
                                        @endif
                                        @if( ($order->status == 'pending') || ($order->status == 'cancelled') )
                                        <x-deletehref-input name="DeleteButton" :label="__('translation.delete')" required href="javascript:void(0)" class="deleteData" data-deleteid="{{ $order->id }}" data-routeurl="{{ route('order.destroy') }}" />
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">{{ __('translation.no_orders_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="right user-navigation" style="float:right">{!! $orders->appends(request()->input())->links() !!}</div>
                {{-- Pagination End --}}
                {{-- Orders Table End --}}
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
        setupPdfDownload('.downloadorderpdf', 'data-downloadroutepdf');
        setupPdfDownload('.downloadordercsv', 'data-downloadroutepdf');
    });
</script>
@endsection
