@extends('backend.layouts.master-horizontal')

@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}}
@endsection

@section('content')
    @include('backend.components.breadcrumb')
<!-- FILTER -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="card-title">{{ __('translation.filter') }}</h4>
                <div>
                    @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloadrequisitionpdf',    
                        'showPdf'=>false, 
                        'pdfRoute' => route('admin.requisitions.exportPdf'),
                        'pdfClass' => 'downloadrequisitionpdf',
                        'showCsv'=>false, 
                        'csvId' =>'downloadrequisitioncsv',    
                        'csvRoute' => route('admin.requisitions.exportCsv'),
                        'csvClass' => 'downloadrequisitioncsv',
                    ])                 
                </div>
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="row">
                        <x-text-input name="requisition_no" label="{{ __('translation.requisition_no') }}" :value="request('requisition_no')" mainrows="2"/>
                        <x-select-dropdown name="from_warehouse_id" label="{{ __('translation.from_warehouse') }}" :options="$warehouses" :selected="request('from_warehouse_id')" mainrows="2" class="warehouse"/>
                        <x-select-dropdown name="for_store_id" label="{{ __('translation.for_store') }}" :options="$stores" :selected="request('for_store_id')" mainrows="2" class="store"/> 
                        <x-text-input name="from_date" :label="__('translation.from_date')" value="{{ request('from_date') }}" mainrows="2" class="flatdatepickr" />
                        <x-text-input name="to_date" :label="__('translation.to_date')" value="{{ request('to_date')  }}" mainrows="2" class="flatdatepickr" />
                        <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.requisition_status')" :selected="request('status')" mainrows="2" class="requisition_status"/>
                    </div>
                    {{-- Buttons Row --}}
                    <div class="row mt-2">
                        <div class="col-12 d-flex justify-content-end">
                            <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" value="Filter" class="me-2"/>
                            <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="{{ __('translation.reset') }}" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- LIST -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">
                    {{ $breadcrumb['title'] ?? '' }} {{ __('translation.listing') }}
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
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
                                <th>{{ __('translation.date') }}</th>
                                <th>{{ __('translation.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requisitions as $req)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $req->requisition_no }}</td>
                                    <td>{{ $req->fromWarehouse->name ?? '-' }}</td>
                                    <td>{{ $req->store->name ?? '-' }}</td>
                                    <td>{{ number_format($req->total_qty, 2) }}</td>
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
                                    <td>
                                        <x-href-input action="view" name="view" label="View" class="viewRequisition" data-id="{{ \App\Helpers\Settings::getEncodeCode($req->id) }}" href="javascript:void(0);" />
                                        @if($req->status == 1)
                                            <x-href-input action="cancel" name="cancel" class="cancelRequisition" label="Cancel" href="javascript:void(0);" data-id="{{ \App\Helpers\Settings::getEncodeCode($req->id) }}" />
                                        @endif
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        No requisitions found
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="mt-3">
                    {{ $requisitions->appends(request()->input())->links() }}
                </div>

            </div>

        </div>
    </div>
</div>

@endsection

@section('script')

<script>
validateSelect2Form('requisitionForm', [
    'from_warehouse_id',
    'for_store_id',
    'status'
]);
// =======================
// VIEW MODAL
// =======================
$(document).on('click', '.viewRequisition', function () {

    let id = $(this).data('id');

    $('#requisitionModal').modal('show');
    $('#requisitionModalBody').html('Loading...');

    $.get("{{ route('admin.requisitions.view.ajax', ':id') }}".replace(':id', id), function (res) {
        $('#requisitionModalBody').html(res);
    });

});

// =======================
// CANCEL
// =======================
$(document).on('click', '.cancelRequisition', function () {

    let id = $(this).data('id');

    Swal.fire({
        title: 'Cancel Requisition?',
        text: 'Stock will be reversed!',
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {

        if (result.isConfirmed) {

            $.post("{{ route('admin.requisitions.cancel') }}", {
                _token: "{{ csrf_token() }}",
                id: id
            }, function (res) {

                if (res.success) {
                    Swal.fire('Cancelled!', res.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message, 'error');
                }

            });

        }
    });

});

</script>

@endsection