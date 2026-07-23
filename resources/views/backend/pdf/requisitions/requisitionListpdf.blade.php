@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.requisition_no') }}</th>
                <th>{{ __('translation.from_warehouse') }}</th>
                <th>{{ __('translation.for_store') }}</th>
                <th>{{ __('translation.total_qty') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.requester') }}</th>
                <th>{{ __('translation.createdat') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requisitions as $req)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $req->requisition_no }}</td>
                    <td>{{ $req->fromWarehouse->name ?? '-' }}</td>
                    <td>{{ $req->store->name ?? '-' }}</td>
                    <td>{{ $req->total_qty }}</td>
                    <td>
                        @if($req->status == 3)
                            <span class="badge bg-success">{{ __('translation.moved_to_store') }}</span>
                        @elseif($req->status == 2)
                            <span class="badge bg-warning">{{ __('translation.partial_to_store') }}</span>
                        @elseif($req->status == 1)
                            <span class="badge bg-dark">{{ __('translation.active') }}</span>
                        @else
                            <span class="badge bg-danger">{{ __('translation.cancelled') }}</span>
                        @endif
                    </td>
                    <td>{{ $req->creator->name ?? '-' }}</td>
                    <td>{{ \App\Helpers\Settings::getFormattedDatetime($req->created_at) }}</td>
                </tr>

            @empty
                <tr>
                    <td colspan="7" class="text-center">{{ __('translation.no_data_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection