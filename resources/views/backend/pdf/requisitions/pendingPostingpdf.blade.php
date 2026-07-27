@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.requisition_no') }}</th>
                <th>{{ __('translation.from_warehouse') }}</th>
                <!-- <th>{{ __('translation.for_store') }}</th> -->
                <th>{{ __('translation.product') }}</th>
                <th>{{ __('translation.quantity') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->requisition->requisition_no ?? '-' }}</td>
                    <td>{{ $item->requisition->fromWarehouse->name ?? '-' }}</td>
                    <!-- <td>{{ $item->requisition->store->name ?? '-' }}</td> -->
                    <td>{{ $item->masterItem->name ?? '-' }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>
                        @if($item->accepted_by)
                            <span class="badge bg-success">{{ __('translation.posted') }}</span>
                        @else
                            <span class="badge bg-warning">{{ __('translation.pending_posting') }}</span>
                        @endif
                    </td>
                    <td>{{ \App\Helpers\Settings::getFormattedDatetime($item->created_at) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">
                        {{ __('translation.no_data_found') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection