@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th width="50">#</th>
                <th>{{ __('translation.warehouse') }}</th>
                <th>{{ __('translation.product') }}</th>
                <th>{{ __('translation.available_qty') }}</th>
                <th>{{ __('translation.last_updated') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $stock)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $stock->warehouse->name ?? '-' }}</td>
                    <td>{{ $stock->masterItem->name ?? '-' }}</td>
                    <td>{{ $stock->stock }}</td>
                    <td>
                        {{ \App\Helpers\Settings::getFormattedDatetime($stock->updated_at) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        {{ __('translation.no_stock_available') }}
                    </td>
                </tr>
            @endforelse
            @if($stocks->count() > 0)
                <tr>
                    <th colspan="3" class="text-end">{{ __('translation.total') }}</th>
                    <th>{{ $stocks->sum('stock') }}</th>
                    <th></th>
                </tr>
            @endif
        </tbody>
        <tfoot>
        </tfoot>
    </table>
@endsection