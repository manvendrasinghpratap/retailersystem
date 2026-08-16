@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.return_no') }}</th>
                <th>{{ __('translation.invoice_no') }}</th>
                <th>{{ __('translation.customer') }}</th>
                <th>{{ __('translation.products') }}</th>
                <th>{{ __('translation.return_amount') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.created_at') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saleReturns as $return)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $return->return_no }}</strong></td>
                    <td>{{ $return->sale->invoice_no ?? '-' }}</td>
                    <td>{{ $return->customer->name ?? 'Walk-in' }}</td>
                    <td style="max-width: 250px;">
                        @php
                            $products = $return->items->pluck('product.name')->filter()->implode(', ');
                        @endphp
                        {{ Str::limit($products, 50) }}
                    </td>
                    <td>{{ __('translation.currency') }}{{  \App\Helpers\Settings::getcustomnumberformat($return->total_amount) }} </td>
                    <td>
                        @if($return->status === 'completed')
                            <span class="badge bg-success">{{ __('translation.completed') }}</span>
                        @else
                            <span class="badge bg-danger">{{ __('translation.cancelled') }}</span>
                        @endif
                    </td>
                    <td>{{ \App\Helpers\Settings::formatDate($return->created_at, Config::get('constants.dateformat.slashdmy')) }} </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">{{ __('translation.no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection