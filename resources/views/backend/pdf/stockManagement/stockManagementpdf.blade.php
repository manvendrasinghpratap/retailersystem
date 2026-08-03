@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.category_name') }}</th>
                <th>{{ __('translation.product_name') }}</th>
                <th>{{ __('translation.qty_in_stock') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventory as $stock)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $stock->category_name }}</td>
                    <td>{{ \App\Helpers\Settings::getDataTitle($stock->product_name) }}</td>
                    <td>{{ $stock->total_stock }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">
                        @lang('translation.no_data_found')
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection