@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{__('translation.product')}}</th>
                <th>{{__('translation.opening').' '. __('translation.stock')}}</th>
                <th>{{__('translation.in')}}</th>
                <th>{{__('translation.out')}}</th>
                <th>{{__('translation.closing').' '. __('translation.stock')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row['title'] }}</td>
                <td>{{ $row['opening_stock'] }}</td>
                <td>{{ $row['today_in'] }}</td>
                <td>{{ $row['today_out'] }}</td>
                <td>{{ $row['closing_stock'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

@endsection
