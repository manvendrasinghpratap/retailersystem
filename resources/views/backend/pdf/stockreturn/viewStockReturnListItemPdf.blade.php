@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
        <!-- Return Information -->
        <tr>
            <td width="50%" style="vertical-align: top;"><strong>{{ __('translation.return_no') }}:</strong>{{ $return->return_no }}</td>
            <td width="50%" style="vertical-align: top;"><strong>{{ __('translation.date') }}:</strong>{{ \App\Helpers\Settings::getFormattedDatetime($return->created_at) }}</td>
        </tr>
        <!-- Spacing -->
        <tr>
            <td colspan="2" style="height: 5px;"></td>
        </tr>
        <!-- Vendor and Warehouse Details -->
        <tr>
            <!-- Vendor Details -->
            <td width="50%" style="vertical-align: top; padding-right: 20px;"> <strong>{{ __('translation.vendor') }}{{ __('translation.details') }}:</strong><br>
                <strong>{{ __('translation.company_name') }}</strong>: {{ $return->vendor->company_name ?? 'N/A' }}<br>
                <strong>{{ __('translation.managed_by') }}</strong>:{{ $return->vendor->name ?? 'N/A' }}<br>
                <strong>{{ __('translation.phone') }}</strong>:{{ $return->vendor->phone ?? 'N/A' }}<br>
                <strong>{{ __('translation.email') }}</strong>:{{ $return->vendor->email ?? 'N/A' }}<br>
                <strong>{{ __('translation.address') }}</strong>:{{ $return->vendor->address ?? 'N/A' }}
            </td>
            <!-- Warehouse Details -->
            <td width="50%" style="vertical-align: top; padding-left: 20px;">
                <strong>{{ __('translation.warehouse') }}{{ __('translation.details') }}:</strong><br>
                <strong>{{ __('translation.name') }}</strong>:{{ $return->warehouse->name ?? 'N/A' }}<br>
                <strong>{{ __('translation.managed_by') }}</strong>:{{ $return->warehouse->manager_name ?? 'N/A' }}<br>
                <strong>{{ __('translation.phone') }}</strong>:{{ $return->warehouse->phone ?? 'N/A' }}<br>
                <strong>{{ __('translation.email') }}</strong>:{{ $return->warehouse->email ?? 'N/A' }}<br>
                <strong>{{ __('translation.address') }}</strong>:{{ $return->warehouse->address ?? 'N/A' }}
            </td>

        </tr>

    </table>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.product') }}</th>
                <th>{{ __('translation.quantity') }}</th>
                <th>{{ __('translation.price') }}</th>
                <th>{{ __('translation.total') }}</th>
                <th>{{ __('translation.reason') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($return->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->masterItem->name ?? 'N/A' }}</td>
                    <td>{{ $item->qty }}</td>
                    <td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($item->price) }}</td>
                    <td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($item->total) }}</td>
                    <td>{{ $item->reason }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="text-end rightcorner">
        <h4>{{ __('translation.total')}} : {{ __('translation.currency')}} {{$return->total}}</h4>
    </div>
@endsection