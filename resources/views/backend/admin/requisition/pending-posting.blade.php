@extends('backend.layouts.master-horizontal')
@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection
@section('content')
@include('backend.components.breadcrumb')

<!-- FILTER -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="card-title">
                    {{ __('translation.filter') }}
                </h4>              
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="row">
                        <x-text-input name="requisition_no" label="{{ __('translation.requisition_no') }}" :value="request('requisition_no')" mainrows="2"/>
                        <x-select-dropdown name="from_warehouse_id" label="{{ __('translation.from_warehouse') }}" :options="$warehouses" :selected="request('from_warehouse_id')" mainrows="2" class="warehouse"/>
                        <x-select-dropdown name="for_store_id" label="{{ __('translation.for_store') }}" :options="$stores" :selected="request('for_store_id')" mainrows="2" class="store"/>
                        <x-text-input name="from_date" label="{{ __('translation.from_date') }}" value="{{ request('from_date') }}" mainrows="2" class="flatdatepickr"/>
                        <x-text-input name="to_date" label="{{ __('translation.to_date') }}" value="{{ request('to_date') }}" mainrows="2" class="flatdatepickr"/>
                    </div>
                    <!-- Buttons -->
                    <div class="row mt-2">
                        <div class="col-12 d-flex justify-content-end">
                            <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" value="Filter" class="me-2"/>
                            <x-filter-href-button name="reset" href="{!! route('admin.requisitions.pending.posting') !!}" label="{{ __('translation.reset') }}"/>
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
                    {{ $breadcrumb['title'] ?? '' }}
                    {{ __('translation.listing') }}
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
                                <th>{{ __('translation.product') }}</th>
                                <th>{{ __('translation.quantity') }}</th>
                                <th>{{ __('translation.status') }}</th>
                                <th>{{ __('translation.date') }}</th>
                                <th>{{ __('translation.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->requisition->requisition_no ?? '-' }}</td>
                                    <td>{{ $item->requisition->fromWarehouse->name ?? '-' }}</td>
                                    <td>{{ $item->requisition->store->name ?? '-' }}</td>
                                    <td>{{ $item->masterItem->name ?? '-' }}</td>
                                    <td>{{ number_format($item->qty, 2) }}</td>
                                    <td>
                                        @if($item->accepted_by)
                                            <span class="badge bg-success">{{ __('translation.posted') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ __('translation.pending_posting') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ \App\Helpers\Settings::getFormattedDatetime($item->created_at) }}</td>

                                    <td>
                                    {{-- ACTIVE & PENDING --}}
                                    @if($item->status == 1 && is_null($item->accepted_by))
                                    <x-href-input action="no-barcode" name="no-barcode" label="" class="no-barcode" data-id="{{ \App\Helpers\Settings::getEncodeCode($item->id) }}" href="javascript:void(0);"/>
                                    <x-href-input action="barcode" name="barcode" label="" class="barcode" data-id="{{ \App\Helpers\Settings::getEncodeCode($item->id) }}" href="javascript:void(0);"/>
                                    <x-href-input action="cancel" name="cancel" label="" class="cancelItem" href="javascript:void(0);" data-id="{{ \App\Helpers\Settings::getEncodeCode($item->id) }}"/>
                                    {{-- CANCELLED --}}
                                    @elseif($item->status == 0)
                                    <span class="badge bg-danger">{{ __('translation.cancelled') }}</span>
                                    {{-- ACCEPTED --}}
                                    @elseif(!is_null($item->accepted_by))
                                    <span class="badge bg-success">{{ __('translation.accepted') }}</span>
                                    @endif
                                    </td>




                                    <!-- <td>
                                        @if(is_null($item->accepted_by))
                                            <x-href-input action="no-barcode" name="no-barcode" label="" class="no-barcode" data-id="{{ \App\Helpers\Settings::getEncodeCode($item->id) }}" href="javascript:void(0);"/>
                                            <x-href-input action="barcode" name="barcode" label="" class="barcode" data-id="{{ \App\Helpers\Settings::getEncodeCode($item->id) }}" href="javascript:void(0);"/>                                       
                                        @endif
                                        @if($item->status == 1 && is_null($item->accepted_by))

                                    <x-href-input 
                                        action="cancel"
                                        name="cancel"
                                        label="{{ __('translation.cancel') }}"
                                        class="cancelItem"
                                        href="javascript:void(0);"
                                        data-id="{{ \App\Helpers\Settings::getEncodeCode($item->id) }}"
                                    />

                                @endif
                                    </td> -->
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">
                                        {{ __('translation.no_data_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- PAGINATION -->
                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
// ===============================
// NO BARCODE
// ===============================
    $(document).on('click', '.no-barcode', function () {

        let id = $(this).data('id');

        Swal.fire({
            title: 'Brand Without Barcode?',
            text: 'This item will use auto generated barcode.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Redirecting...',
                    text: 'Opening in new tab',
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                });

                // redirect to another URL in new tab
                window.open(
                    "{{ route('admin.no-barcode') }}?requisition_item_id=" + id,
                    '_blank'
                );
            }
        });
    });


// ===============================
// WITH BARCODE
// ===============================
    $(document).on('click', '.barcode', function () {

        let id = $(this).data('id');

        Swal.fire({
            title: 'Brand With Barcode?',
            text: 'Barcode labels will be printed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Redirecting...',
                    text: 'Opening in new tab',
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                });

                // redirect to another URL in new tab
                window.open(
                    "{{ route('admin.barcode') }}?requisition_item_id=" + id,
                    '_blank'
                );
            }
        });
    });

    $(document).on('click', '.cancelItem', function () {

        let id = $(this).data('id');

        Swal.fire({
            title: 'Cancel Item?',
            text: 'This item stock will be reversed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ route('admin.requisitions.cancel.item') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },

                    success: function (res) {

                        if (res.success) {

                            Swal.fire(
                                'Success',
                                res.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });

                        } else {

                            Swal.fire(
                                'Error',
                                res.message,
                                'error'
                            );
                        }
                    },

                    error: function (xhr) {

                        Swal.fire(
                            'Error',
                            xhr.responseJSON?.message || 'Something went wrong',
                            'error'
                        );
                    }
                });
            }
        });
    });

</script>
@endsection