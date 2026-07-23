@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.return_no') }}</th>
                <th>{{ __('translation.vendor') }}</th>
                <th>{{ __('translation.warehouse') }}</th>
                <th>{{ __('translation.currency') }} {{ __('translation.total') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.created_at') }}</th>
            </tr>
        </thead>

        <tbody>
            @forelse($returns as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->return_no }}</td>
                    <td>{{ $row->vendor->name ?? '' }}</td>
                    <td>{{ $row->warehouse->name ?? '' }}</td>
                    <td>{{ __('translation.currency') }} {{ $row->total }}</td>
                    <td>
                        @if($row->status == 1)
                            <span class="badge bg-success">{{ __('translation.active') }}</span>
                        @else
                            <span class="badge bg-danger">{{ __('translation.cancelled') }}</span>
                        @endif
                    </td>
                    <td>{{ \App\Helpers\Settings::formatDate($row->created_at, Config::get('constants.dateformat.slashdmy')) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">{{ __('translation.no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection