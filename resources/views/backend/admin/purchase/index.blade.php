@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}}
@endsection
@section('content')
    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                    <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                            'showPdf' => true,
                            'showCsv' => true,
                            'pdfId' => 'downloadpurchasespdf',
                            'pdfRoute' => route('admin.purchases.exportPdf'),
                            'pdfClass' => 'downloadpurchasespdf',
                            'csvId' => 'downloadpurchasescsv',
                            'csvRoute' => route('admin.purchases.exportCsv'),
                            'csvClass' => 'downloadpurchasescsv',
                        ])
                    </div>
                </div>
                <div class="card-body">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route(array_key_exists('route2', $breadcrumb) ? $breadcrumb['route2'] : 'admin.purchases.index') }}" class="mb-3">
                        <div class="row">
                            <x-text-input name="purchase_no" label="{{ __('translation.purchase_no')}}" value="{{ request('purchase_no') }}" mainrows="2" />
                            <x-select-dropdown name="vendor_id" label="{{ __('translation.vendor')}}" :options="$vendors" :selected="request('vendor_id') ?? ''" class="supplier" mainrows="2" />
                            <x-select-dropdown name="warehouse_id" label="{{ __('translation.warehouse')}}" :options="$warehouses" :selected="request('warehouse_id') ?? ''" class="warehouse" mainrows="2" />
                            <x-text-input name="from_date" label="{{ __('translation.from_date') }}" value="{{ \App\Helpers\Settings::formatDate(request('from_date') ?? $date ?? '', Config::get('constants.dateformat.slashdmyonly')) }}" class="flatdatepickr" mainrows="2" />
                            <x-text-input name="to_date" label="{{ __('translation.to_date') }}" value="{{ \App\Helpers\Settings::formatDate(request('to_date') ?? $date ?? '', Config::get('constants.dateformat.slashdmyonly')) }}" class="flatdatepickr" mainrows="2" />
                            <x-button submitText="Filter" resetText="{{ __('translation.reset') }}" url="{{ route($breadcrumb['route2'] ?? 'admin.purchases.index') }}" isbutton="1" iscancel="1" mainrows="2" />
                        </div>
                    </form>
                    {{-- Filter Form End --}}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm rounded-2xl">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
                        {{ __('translation.listing') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
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
                                    <th>{{ __('translation.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $row)
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
                                        <td>
                                            <x-href-input action="view" name="view" label="View" class="viewPurchase" data-id="{{ \App\Helpers\Settings::getEncodeCode($row->id) }}" href="javascript:void(0);" />
                                            @if($row->status == 1)
                                                <x-href-input action="print_barcode" name="print_barcode" label="Print Barcode" class="printBarcode" href="{{ route('admin.purchases.printBarcode', \App\Helpers\Settings::getEncodeCode($row->id)) }}" />
                                                <x-href-input action="cancel" name="cancel" class="cancelPurchase" label="Cancel" href="javascript:void(0);" data-id="{{ \App\Helpers\Settings::getEncodeCode($row->id) }}" />
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
                    </div>
                    <div class="right user-navigation" style="float:right">{!! $purchases->appends(request()->input())->links() !!}</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        /* Cancel purchase using ajax */
        $(document).on('click', '.cancelPurchase', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will reverse stock, ledger & vendor balance!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, Cancel It!',
                cancelButtonText: 'No'
            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }
                $.ajax({
                    url: "{{ route('admin.purchases.cancel') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Please Wait...',
                            text: 'Cancelling Purchase...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (res) {
                        Swal.close();
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cancelled!',
                                text: res.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Cannot Cancel Purchase',
                                html: res.message
                            });
                        }
                    },
                    error: function () {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again.'
                        });
                    }
                });
            });
        });
        /* View purchase using ajax */
        $(document).on('click', '.viewPurchase', function () {
            let id = $(this).data('id');
            $('#purchaseModal').modal('show');
            $('#purchaseDetails').html('<div class="text-center">Loading...</div>');
            $.get("{{ route('admin.purchases.view.ajax', ':id') }}".replace(':id', id), function (res) {
                $('#purchaseDetails').html(res);
            });
        });

        $(document).ready(function () {
            setupPdfDownload('.downloadpurchasespdf', 'data-downloadroutepdf');
            setupPdfDownload('.downloadpurchasescsv', 'data-downloadroutecsv');
        });
    </script>
@endsection