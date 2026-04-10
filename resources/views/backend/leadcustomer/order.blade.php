@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? '' }}
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
                        'pdfId' =>'customerorderspdf',    
                        'pdfRoute' => route('customer.orders.pdf'),
                        'pdfClass' => 'customerorderspdf',
                        'csvId' =>'customerorderscsv',    
                        'csvRoute' => route('customer.orders.csv'),
                        'csvClass' => 'customerorderscsv',
                    ])                 
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route($breadcrumb['route'] ?? '') }}"> 
                    <div class="row">
                        <x-text-input name="customer_name" :value="request('customer_name')" :placeholder="__('translation.search_customer_name')" class="form-control" mainrows="2"/>
                        <x-text-input name="phone_no" :label="__('translation.phone') .' '. __('translation.number') " value="{{ request('phone_no') }}"  id="phone_no" class="onlyinteger phonenumber nocutcopypaste setusername" placeholder="Phone No" mainrows="2"/>
                        <x-date-input name="from_date" :label="__('translation.from_date')" value="{{ request('from_date') ?? '' }}" class="flatdatepickr width-per-100" mainrows="1"/>
                        <x-date-input name="to_date" :label="__('translation.to_date')" value="{{ request('to_date') ?? '' }}" class="flatdatepickrto width-per-100" mainrows="1"/>
                        <x-select-dropdown name="customer_payment_method" label="{{ __('translation.payment_method') }}" :options="\Config::get( 'constants.customer_payment_method' )" :selected="request('customer_payment_method')" class="customer_payment_method" mainrows="2"/>
                        <x-select-dropdown name="customer_order_status" label="{{ __('translation.status') }}" :options="['all' => 'All'] + \Config::get('constants.customer_order_status')"  :selected="request('customer_order_status')??'pending'" class="accountstatus" mainrows="2"/>
                        <x-button submitText="Filter" resetText="Reset" url="{{ route($breadcrumb['route'] ?? '') }}" isbutton="1" iscancel="1" mainrows="2"/> 
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Orders Table --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive overflowx">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>@lang('translation.order') ID</th>
                                <th>@lang('translation.customer_name')</th>
                                <th>@lang('translation.products')</th>
                                <th>@lang('translation.payment_method')</th>
                                <th>@lang('translation.status')</th>
                                <th><strong class="currency-symbol">{{ __('translation.currency')}}</strong> @lang('translation.total') @lang('translation.amount')</th>
                                <th>@lang('translation.date')</th>
                                <th class="text-end">@lang('translation.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                                    <td>{{ sprintf('%05d', $order->id) }}</td>
                                    <td>{{ $order->customer->customer_name ?? '' }}</td>
                                    <td>
                                    @foreach($order->UserOrderDetail as $detail)
                                    {{ $detail->product->title ?? 'N/A' }} ({{ $detail->quantity ?? 1 }})<br>
                                    @endforeach
                                    </td>
                                    <td>{{ ucfirst($order->payment_method) }}</td>
                                    <td><span class="badge bg-{{ $order->status == 'confirmed' ? 'info' : ($order->status == 'cancelled' ? 'danger' : ( $order->status == 'delivered' ? 'success' : ($order->status == 'delivery-pending' ? 'custom-delivery-pending-badge' : 'warning'))) }}">{{ ucfirst($order->status) }}</span></td>
                                    <td>{{ \App\Helpers\Settings::getcustomnumberformat($order->total_amount) }}</td>
                                    <td>{{ \App\Helpers\Settings::getFormattedDatetime($order->created_at) }}</td>
                                    <td class="center">
                                        <x-href-input name="view" href="{{ route('customer.order.review',['id' => \App\Helpers\Settings::getEncodeCode($order->id)]) }}"  action='view' />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">@lang('translation.no_records_found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination links --}}
                <div class="mt-3">
                    {!! $orders->appends(request()->query())->links() !!}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        setupPdfDownload('.customerorderspdf', 'data-downloadroutepdf');
        setupPdfDownload('.customerorderscsv', 'data-downloadroutepdf');
    });
</script>
@endsection
