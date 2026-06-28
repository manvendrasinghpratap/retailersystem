@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.name') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.createdat') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($paymentTypes) && $paymentTypes->count() > 0)
                @foreach($paymentTypes as $paymentType)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $paymentType->name }}</td>
                        <td>{{ $paymentType->status_text }}</td>
                        <td>{{ $paymentType->created_date }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center">{{ __('translation.no_data_found') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection