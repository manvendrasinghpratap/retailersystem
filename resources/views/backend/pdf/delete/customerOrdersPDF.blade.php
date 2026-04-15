@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')
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
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ sprintf('%05d', $order->id) }}</td>
                        <td>{{ $order->customer->customer_name ?? '' }}</td>
                        <td>
                        @foreach($order->UserOrderDetail as $detail)
                        {{ $detail->product->title ?? 'N/A' }} ({{ $detail->quantity ?? 1 }})<br>
                        @endforeach
                        </td>
                        <td>{{ ucfirst($order->payment_method) }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ \App\Helpers\Settings::getcustomnumberformat($order->total_amount) }}</td>
                        <td>{{ \App\Helpers\Settings::getFormattedDatetime($order->created_at) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">@lang('translation.no_records_found')</td>
                    </tr>
                @endforelse
            </tbody>
            @if($orders->count() > 0)
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="6" class="text-end">@lang('translation.total') @lang('translation.amount'):</td>
                    <td colspan="2" class="text-end"><strong class="currency-symbol">{{ __('translation.currency')}}</strong> {{ \App\Helpers\Settings::getcustomnumberformat($orders->sum('total_amount')) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>

@endsection
