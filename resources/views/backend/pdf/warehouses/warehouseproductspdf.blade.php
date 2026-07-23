@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.product_name') }}</th>
                <th>{{ __('translation.available_stock') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1;
            $sum = []; 
            @endphp
            @forelse($items as $product)
                <tr>
                    <td>{{ $counter++ }}</td>
                    <td>{!! $product->name !!}</td>
                    <td>{{ $product->stocks->first()?->stock ?? 0 }}@php $sum[] = $product->stocks->first()?->stock ?? 0 @endphp </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">{{ __('translation.no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-end"></td>
                <td class="text-end">{{ __('translation.total') }}  : @php echo array_sum($sum); @endphp </td>
            </tr>
        </tfoot>
    </table>
@endsection