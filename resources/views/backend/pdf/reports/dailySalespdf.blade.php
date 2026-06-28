@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.invoice_no') }}</th>
                <th>{{ __('translation.customer_name') }}</th>
                @if(Auth::user()->hasDesignation())
                    <th>{{ __('translation.staff_name') }}</th>
                @endif
                <th>{{ __('translation.payment_type') }}</th>
                @foreach($paymentMethods as $method)
                    <th class="text-center">
                        {{ __('translation.currency') }} {{ $method['name'] }}
                    </th>
                @endforeach
                <th>{{ __('translation.currency') }} {{ __('translation.total_amount') }}</th>
                <th>{{ __('translation.transaction_date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pdfSales as $sale)
                @php
                    $summary = $sale->payments
                        ->groupBy('method')
                        ->map(fn($items) => $items->sum('amount'));
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->customer->name ?? '-' }}</td>
                    @if(Auth::user()->hasDesignation())
                        <td>{{ $sale->user->name ?? '-' }}</td>
                    @endif
                    <td>{{ \App\Helpers\Settings::getDataUcfirst($paymentTypes[$sale->payment_type] ?? '') }}</td>
                    @foreach($paymentMethods as $method)
                        <td class="text-center">
                            {{ __('translation.currency') }}
                            {{ number_format($summary[$method['short_name']] ?? 0, 2) }}
                        </td>
                    @endforeach

                    <td>
                        {{ __('translation.currency') }}
                        {{ number_format($sale->total, 2) }}
                    </td>

                    <td>
                        {{ \App\Helpers\Settings::getFormattedDatetime($sale->created_at) }}
                    </td>

                </tr>

            @empty

                @php
                    $columns = 6 + count($paymentMethods) + (Auth::user()->hasDesignation() ? 1 : 0);
                @endphp

                <tr>
                    <td colspan="{{ $columns }}" class="text-center">
                        {{ __('translation.no_data_found') }}
                    </td>
                </tr>

            @endforelse

        </tbody>

        <tfoot>

            <tr class="fw-bold bg-light">

                @if(Auth::user()->hasDesignation())
                    <td></td>
                @endif

                <td colspan="4" class="text-end">
                    {{ __('translation.total') }}
                </td>

                @foreach($paymentMethods as $method)
                    <td class="text-center">
                        {{ __('translation.currency') }}
                        {{ number_format($paymentTotals[$method['short_name']] ?? 0, 2) }}
                    </td>
                @endforeach

                <td>
                    {{ __('translation.currency') }}
                    {{ number_format($totalSales, 2) }}
                </td>

                <td></td>

            </tr>

        </tfoot>

    </table>

@endsection