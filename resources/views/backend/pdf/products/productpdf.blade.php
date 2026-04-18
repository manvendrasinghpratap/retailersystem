@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.category')}}</th>
                <th>{{ __('translation.product_name')}}</th>
                <th>{{ __('translation.selling_price') }}</th>
                <th>{{ __('translation.barcode')}}</th>
                <th>{{ __('translation.sku')}}</th>
                <th>{{ __('translation.status')}}</th>
            </tr>
        </thead>

        <tbody>
            @if(!empty($products))
                @foreach($products as $key => $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $p->category->name ?? '-' }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->selling_price }}</td>
                        <td><img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($p->barcode, 'C128') }}" style="width:120px; height:40px;" /><br>{{$p->barcode}}</td>
                        <td>{{ $p->sku }}</td>
                        <td>
                            @if($p->status == 1)
                                <span class="badge bg-success">{{ __('translation.active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('translation.inactive') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center">No Product Available</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection