@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.purchase_no') }}</th>
                <th>{{ __('translation.vendor') }}</th>
                <th>{{ __('translation.warehouse') }}</th>
                <th style="max-width:250px; white-space:normal; word-break:break-word;">{{ __('translation.products') }}</th>
                <th>{{__('translation.currency')}} {{ __('translation.total') }}</th>
                <th>{{ __('translation.date') }}</th>
                <th>{{ __('translation.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->purchase_no }}</td>
                    <td>{{ $row->vendor->company_name ?? '' }}</td>
                    <td>{{ $row->warehouse->name ?? '' }}</td>
                    <td style="max-width:250px; white-space:normal; word-break:break-word;">
                        {{ $row->product_names }}
                    </td>
                    <td>{{__('translation.currency')}} {{ $row->total }}</td>
                    <td>{{ \App\Helpers\Settings::formatDate($row->created_at, Config::get('constants.dateformat.slashdmy')) }}</td>
                    <td>@if($row->status == 0)
                        <span class="badge bg-danger">{{__('translation.cancelled')}}</span>
                    @else
                            <span class="badge bg-success">{{__('translation.active')}}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">{{ __('translation.no_data_found')}}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection