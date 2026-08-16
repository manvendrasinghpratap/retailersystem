@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.customer_name') }}</th>
                <th>{{ __('translation.invoice_no') }}</th>
                <th>{{ __('translation.cashier') }}</th>
                <th>{{ __('translation.payment_type') }}</th>
                <th>{{ __('translation.payment_status') }}</th>
                <th>{{ __('translation.payment_method') }}</th>
                <th>{{ __('translation.amount') }}</th>
                <th>{{ __('translation.tax') }}</th>
                <th>{{ __('translation.fullfillment_method') }}</th>
                <th>{{ __('translation.delivery_charges') }}</th>
                <th>{{ __('translation.total_amount') }}</th>
                <th>{{ __('translation.approval_status') }}</th>
                <th>{{ __('translation.transaction_date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $index => $sale)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sale->customer->name ?? '-' }}</td>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->user->name ?? '-' }}</td>
                    <td>{{ ucfirst($paymentTypes[$sale->payment_type] ?? $sale->payment_type ?? '-') }}</td>
                    <td>{{ ucfirst($sale->payment_status ?? '-') }}</td>
                    <td>{{ ucfirst($sale->payment_methods ?? '-') }}</td>
                    <td>{{ __('translation.b_ngn') }} {{ number_format($sale->subtotal ?? 0, 2) }}</td>
                    <td>{{ __('translation.b_ngn') }} {{ number_format($sale->tax ?? 0, 2) }}</td>
                    <td>{{ \App\Helpers\Settings::getDataTitle($sale->delivery_type ?? '-') }}</td>
                    <td>{{ __('translation.b_ngn') }} {{ number_format($sale->delivery_charge ?? 0, 2) }}</td>
                    <td>{{ __('translation.b_ngn') }} {{ \App\Helpers\Settings::getcustomnumberformat($sale->total) }}</td>
                    <td>{{ ucfirst($sale->payment_approval_status ?? '-') }}</td>
                    <td>{{ \App\Helpers\Settings::getFormattedDatetime($sale->created_at) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="text-center">{{ __('translation.no_sales_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection