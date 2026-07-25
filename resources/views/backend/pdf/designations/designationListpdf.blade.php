@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.designation') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.createdat') }}</th>
            </tr>
        </thead>

        <tbody>
            @if(!empty($designations) && $designations->count() > 0)
                @foreach($designations as $designation)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $designation->name }}</td>
                        <td>
                            @if($designation->status == '1')
                                <span class="badge bg-info">{{ __('translation.active') }}</span>
                            @else
                                <span class="badge bg-primary">{{ __('translation.inactive') }}</span>
                            @endif
                        </td>
                        <td>{{ $designation->created_date }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center">{{ __('translation.no_designations_available') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection