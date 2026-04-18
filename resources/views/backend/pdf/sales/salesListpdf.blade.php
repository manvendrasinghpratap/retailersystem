@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>{{ __('translation.customer_name') }}</th>
                <th>{{ __('translation.customer_phone') }}</th>
                <th>{{ __('translation.customer_email') }}</th>
                <th>{{ __('translation.invoice_no') }}</th>
                <th>{{ __('translation.cashier') }}</th>
                <th>{{ __('translation.payment_type') }}</th>
                <th>{{ __('translation.payment_method') }}</th>
                <th>{{ __('translation.b_ngn') . ' ' . __('translation.total_amount') }}</th>
                <th>{{ __('translation.transaction_date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->customer->name ?? '-' }}</td>
                    <td>{{ $sale->customer->phone ?? '-' }}</td>
                    <td>{{ $sale->customer->email ?? '-' }}</td>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->user->name ?? '-' }}</td>
                    <td>{{ ($sale->payment_method == null) ? 'Partial Payment' : 'Full Payment' }}</td>
                    <td>{{ $sale->payment_methods }}</td>
                    <td>{{ __('translation.b_ngn') . ' ' . number_format($sale->total, 2) }}</td>
                    <td>{{ App\Helpers\Settings::getFormattedDatetime($sale->created_at)}}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">{{ __('translation.no_sales_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection