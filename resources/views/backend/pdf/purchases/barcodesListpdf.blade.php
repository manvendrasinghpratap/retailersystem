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
                <th>{{ __('translation.products') }}</th>
                <th>{{ __('translation.quantity') }}</th>
                <th>{{ __('translation.tracking') }}</th>
                <th>{{ __('translation.createdat') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td> {{ $row->purchase->purchase_no ?? '-' }}</td>
                    <td> {{ $row->purchase->vendor->company_name ?? '-' }}</td>
                    <td> {{ $row->purchase->warehouse->name ?? '-' }}</td>
                    <td> {{ $row->masterItem->name ?? '-' }}</td>
                    <td>{{$row->trackings->count()}}</td>
                    <td><span class="badge bg-info">{{ \App\Helpers\Settings::getDataTitle($row->tracking_type) }}</span></td>
                    <td>{{ \App\Helpers\Settings::formatDate($row->purchase->created_at, Config::get('constants.dateformat.slashdmy')) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan=" 7" class="text-center">{{ __('translation.no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection