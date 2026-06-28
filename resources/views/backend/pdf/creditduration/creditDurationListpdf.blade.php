@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.name') }}</th>
                <th>{{ __('translation.duration_days') }}</th>
                <th>{{ __('translation.interest') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.createdat') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($creditDurations) && $creditDurations->count() > 0)
                @foreach($creditDurations as $creditDuration)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $creditDuration->name }}</td>
                        <td> {{ $creditDuration->duration_days }} {{ __('translation.days') }} </td>
                        <td> {{ number_format($creditDuration->interest, 2) }}% </td>
                        <td>
                            @if($creditDuration->status)
                                <span class="badge bg-success">
                                    {{ __('translation.active') }}
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    {{ __('translation.inactive') }}
                                </span>
                            @endif
                        </td>
                        <td> {{ $creditDuration->created_date }} </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center"> {{ __('translation.no_data_found') }} </td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection