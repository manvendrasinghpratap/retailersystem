@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
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
            <th class="text-center">{{ __('translation.currency') }} {{ __('translation.cash') }}</th>
            <th class="text-center">{{ __('translation.currency') }} {{ __('translation.card') }}</th>
            <th class="text-center">{{ __('translation.currency') }} {{ __('translation.transfer') }}</th>
            <th>{{ __('translation.currency') }} {{ __('translation.total_amount') }}</th>
            <th>{{ __('translation.transaction_date') }}</th>
        </tr>
    </thead>

    <tbody>
        @forelse($sales as $sale)
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

                <td>
                    {{ $sale->payment_method == null ? 'Partial Payment' : 'Full Payment' }}
                </td>

                <td class="text-center">
                    {{ __('translation.currency') }} {{ number_format($summary['cash'] ?? 0, 2) }}
                </td>

                <td class="text-center">
                    {{ __('translation.currency') }} {{ number_format($summary['card'] ?? 0, 2) }}
                </td>

                <td class="text-center">
                    {{ __('translation.currency') }} {{ number_format($summary['transfer'] ?? 0, 2) }}
                </td>

                

                <td>
                    {{ __('translation.currency') }} {{ number_format($sale->total, 2) }}
                </td>

                <td>
                    {{ \App\Helpers\Settings::getFormattedDatetime($sale->created_at) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ Auth::user()->hasDesignation() ? 10 : 9 }}" class="text-center">
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
            <td colspan="4" class="text-end">Total</td>

            <td class="text-center">
                {{ __('translation.currency') }} {{ number_format($cashTotal, 2) }}
            </td>

            <td class="text-center">
                {{ __('translation.currency') }} {{ number_format($cardTotal, 2) }}
            </td>

            <td class="text-center">
                {{ __('translation.currency') }} {{ number_format($transferTotal, 2) }}
            </td>
            <td>
                {{ __('translation.currency') }} {{ number_format($totalSales, 2) }}
            </td>

            <td></td>
        </tr>
    </tfoot>
</table>
@endsection