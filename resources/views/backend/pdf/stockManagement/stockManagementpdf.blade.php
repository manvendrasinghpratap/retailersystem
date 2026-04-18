@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.category_name')}}</th>
                <th>{{ __('translation.product_name')}}</th>
                <th>{{ __('translation.sku')}}</th>
                <th>{{ __('translation.barcode')}}</th>
                <th>{{ __('translation.stock')}}</th>
                <th>{{ __('translation.low_alert')}}</th>
                <th>{{ __('translation.status')}}</th>
            </tr>
        </thead>

        <tbody>
            @forelse($inventory as $stock)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $stock->product->category->name ?? '' }}</td>
                    <td>{{ $stock->product->name ?? '' }}</td>
                    <td>{{ $stock->product->sku ?? '' }}</td>
                    <td><img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($stock->product->barcode, 'C128') }}" style="width:120px; height:40px;" /><br>{{$stock->product->barcode}}</td>
                    <td>{{ $stock->stock }}</td>
                    <td>{{ $stock->low_stock_alert }}</td>
                    <td>{{ $stock->isLowStock() ? __('translation.low_stock') : __('translation.normal_stock') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">
                        @lang('translation.no_data_found')
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection