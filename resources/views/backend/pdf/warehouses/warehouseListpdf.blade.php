@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.code') }}</th>
                <th>{{ __('translation.warehouse_name') }}</th>
                <th>{{ __('translation.manager') }}</th>
                <th>{{ __('translation.phone') }}</th>
                <th>{{ __('translation.email') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.createdat') }}</th>
            </tr>
        </thead>
        <tbody>
            @if($warehouses->count() > 0)
                @foreach($warehouses as $warehouse)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $warehouse->warehouse_code }}</td>
                        <td>{{ $warehouse->name }}</td>
                        <td>{{ $warehouse->manager_name ?? '-' }}</td>
                        <td>{{ $warehouse->phone ?? '-' }}</td>
                        <td>{{ $warehouse->email ?? '-' }}</td>
                        <td>
                            @if($warehouse->status == 1)
                                <span class="badge bg-success">{{ __('translation.active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('translation.inactive') }}</span>
                            @endif
                        </td>
                        <td>{{ \App\Helpers\Settings::getFormattedDatetime($warehouse->created_at) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" class="text-center">{{ __('translation.no_data_found') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection