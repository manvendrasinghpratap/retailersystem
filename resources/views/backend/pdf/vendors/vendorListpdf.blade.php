@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.vendor_code') }}</th>
                <th>{{ __('translation.company_name') }}</th>
                <th>{{ __('translation.vendor_name') }}</th>
                <th>{{ __('translation.phone') }}</th>
                <th>{{ __('translation.email') }}</th>
                <th>{{ __('translation.opening_balance') }}</th>
                <th>{{ __('translation.current_balance') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.createdat') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($vendors) && $vendors->count() > 0)
                @foreach($vendors as $vendor)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $vendor->vendor_code }}</td>
                        <td>{{ $vendor->company_name }}</td>
                        <td>{{ $vendor->name }}</td>
                        <td>{{ $vendor->phone }}</td>
                        <td>{{ $vendor->email }}</td>
                        <td>{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($vendor->opening_balance) }}</td>
                        <td>{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($vendor->current_balance) }}</td>
                        <td>
                            @if($vendor->status == 1)
                                <span class="badge bg-success">{{ __('translation.active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('translation.inactive') }}</span>
                            @endif
                        </td>
                        <td>{{ App\Helpers\Settings::getFormattedDatetime($vendor->created_at) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="10" class="text-center">{{ __('translation.no_data_found') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection