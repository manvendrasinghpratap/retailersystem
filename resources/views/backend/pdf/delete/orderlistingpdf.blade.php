@extends('backend.pdf.layouts.master')
@section('title', 'Order Listing Report')
@section('content')
    <table border="1" cellspacing="0" cellpadding="5" width="100%">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.customer') }}</th>
                <th>{{ __('translation.product') }}</th>
                <th>{{__('translation.ngn')}} {{ __('translation.total_amount') }} </th>
                <th>{{ __('translation.total_quantity') }}</th>
                <th>{{ __('translation.staff') }}</th>
                <th>{{ __('translation.order') .' '.__('translation.status') }}</th>
                <th>{{ __('translation.payment') .' '.__('translation.status') }}</th>
                <th>{{ __('translation.ordered_at') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalOrders = 0;
                $totalAmount = 0;
                $totalQty = 0;
            @endphp

            @forelse($orders as $key => $order)
                @php
                    $totalOrders++;
                    $totalAmount += $order->total_amount ?? 0;
                    $totalQty += $order->total_quantity ?? 0;

                    // Prepare product list
                    $productList = $order->orderdetails->map(function($d){
                        return ($d->menus->title ?? '') . ' (' . ($d->quantity ?? 0) . ')';
                    })->implode(', ');
                @endphp
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $order->customer->customer_name ?? '-' }}</td>
                    <td style="text-align:left;">{{ $productList }}</td>
                    <td>{{ \App\Helpers\Settings::getcustomnumberformat($order->total_amount) }}</td>
                    <td>{{ $order->total_quantity }}</td>
                    <td>{{ $order->staff->staff_name ?? '-' }}</td>
                    <td>
                        <span style="color:{{ $order->status == 'delivered' ? '#2e7d32' : ($order->status == 'delivery-pending' ? '#b36b00' : '#c62828') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>
                        <span style="color:{{ $order->payment_status == 'completed' ? '#1565c0' : ($order->payment_status == 'cancelled' ? '#c62828' : '#b36b00') }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td>{{ \App\Helpers\Settings::getFormattedDate($order->ordered_at) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;">{{ __('translation.no_orders_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary" style="margin-top:15px;">
        <p><strong>Total Orders:</strong> {{ $totalOrders }}</p>
        <p><strong>Total Quantity:</strong> {{ $totalQty }}</p>
        <p><strong>Total Revenue:</strong> {{__('translation.ngn')}} {{ number_format($totalAmount, 2) }}</p>
    </div>
@endsection
