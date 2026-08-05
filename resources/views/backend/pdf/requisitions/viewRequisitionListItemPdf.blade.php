@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <div style="width: 100%; margin-bottom: 15px;">

        <div style="display: inline-block; width: 48%; vertical-align: top; margin-bottom: 5px;">
            <strong>{{ __('translation.requisition_no') }}:</strong>
            {{ $requisition->requisition_no }}
        </div>

        <div style="display: inline-block; width: 48%; vertical-align: top;margin-bottom: 5px;">
            <strong>{{ __('translation.from_warehouse') }}:</strong>
            {{ $requisition->fromWarehouse->name ?? '-' }}
        </div>

        <div style="display: inline-block; width: 48%; vertical-align: top; margin-bottom: 5px;">
            <strong>{{ __('translation.for_store') }}:</strong>
            {{ $requisition->store->name ?? '-' }}
        </div>

        <div style="display: inline-block; width: 48%; vertical-align: top; margin-bottom: 5px;">
            <strong>{{ __('translation.requester') }}:</strong>
            {{ $requisition->creator->name ?? '-' }}
        </div>

        <div style="display: inline-block; width: 48%; vertical-align: top; margin-bottom: 5px;">
            <strong>{{ __('translation.date') }}:</strong>
            {{ \App\Helpers\Settings::getFormattedDatetime($requisition->created_at) }}
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.product') }}</th>
                <th>{{ __('translation.quantity') }}</th>
				<th style="width:200px;">{{ __('translation.barcode') }}</th>
                <th>{{ __('translation.accepted_by') }}</th>
                <th>{{ __('translation.action') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requisition->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->masterItem->name ?? '-' }}</td>
                    <td>{{ $item->qty }}</td>
					<td>{!! DNS1D::getBarcodeHTML($item->purchaseItemTracking->barcode, 'C128') !!} <small>
                        <center>{{ $item->purchaseItemTracking->barcode }}</center>
                    </small></td>
                    <td>{{ $item->acceptedBy->name ?? 'NO' }}</td>
                    <td>@if($item->accepted_by == null && $requisition->status == 0)
                            <span class="badge bg-danger">{{ __('translation.cancelled') }}</span>
                        @elseif($item->accepted_by != null && $requisition->status == 1)
                            <span class="badge bg-success">{{ __('translation.accepted') }}</span>
                        @else
                            <span class="badge bg-success">{{ __('translation.moved_to_store') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        {{ __('translation.no_data_found') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="text-end rightcorner">
        <h5 class="text-start pt-3" style="padding: 5px;">
            <strong>{{ __('translation.total_qty') }} : {{ $requisition->total_qty }}</strong>
        </h5>
    </div>
@endsection