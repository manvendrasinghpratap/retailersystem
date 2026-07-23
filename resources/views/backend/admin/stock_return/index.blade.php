@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}}
@endsection
@section('content')
    @include('backend.components.breadcrumb')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                    <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'showPdf' => true,
                        'showCsv' => true,
                        'pdfId' =>'downloadstockreturnpdf',
                        'pdfRoute' => route('admin.stock_returns.exportpdf'),
                        'pdfClass' => 'downloadstockreturnpdf',
                        'csvId' =>'downloadstockreturncsv',
                        'csvRoute' => route('admin.stock_returns.exportcsv'),
                        'csvClass' => 'downloadstockreturncsv',
                        ])                 
                    </div>      
                </div>  
                
                <div class="card-body">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route(array_key_exists('route2', $breadcrumb) ? $breadcrumb['route2'] : 'admin.stock_returns.index') }}" class="mb-3">
                        <div class="row">
                            <x-text-input name="return_no" label="{{ __('translation.return_no')}}" value="{{ request('return_no') }}" mainrows="2" />
                            <x-select-dropdown name="vendor_id" label="{{ __('translation.vendor')}}" :options="$vendors" :selected="request('vendor_id') ?? ''" class="supplier" mainrows="2" />  
                            <x-select-dropdown name="warehouse_id" label="{{ __('translation.warehouse')}}" :options="$warehouses" :selected="request('warehouse_id') ?? ''" class="warehouse" mainrows="2" />
                            <x-text-input name="from_date" label="{{ __('translation.from_date') }}" value="{{ \App\Helpers\Settings::formatDate(request('from_date') ?? $date ?? '', Config::get('constants.dateformat.slashdmyonly')) }}" class="flatdatepickr" mainrows="2" />
                            <x-text-input name="to_date" label="{{ __('translation.to_date') }}" value="{{ \App\Helpers\Settings::formatDate(request('to_date') ?? $date ?? '', Config::get('constants.dateformat.slashdmyonly')) }}" class="flatdatepickr" mainrows="2" />
                            <x-button submitText="Filter" resetText="{{ __('translation.reset') }}" url="{{ route($breadcrumb['route2'] ?? 'admin.stock_returns.index') }}" isbutton="1" iscancel="1" mainrows="2" />
                        </div>
                    </form>
                    {{-- Filter Form End --}}
                </div>

    <div class="card-body table-responsive">

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
                    <th width="10%">{{ __('translation.actions') }}</th>
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
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Cancelled</span>
                            @endif
                    </td>
                    <td>{{ \App\Helpers\Settings::formatDate($row->created_at, Config::get('constants.dateformat.slashdmy')) }}</td> 
                    <td>
                        <x-href-input action="view" name="view" label="View" class="viewReturn" data-route="{{ route('admin.stock_returns.view.ajax', \App\Helpers\Settings::getEncodeCode($row->id)) }}" href="javascript:void(0);" />
                        @if($row->status == 1)
                            <x-href-input action="cancel" name="cancel" class="cancelReturn" label="Cancel" href="javascript:void(0);" data-id="{{ \App\Helpers\Settings::getEncodeCode($row->id) }}" />
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">{{ __('translation.no_data_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="right user-navigation">
            {!! $returns->appends(request()->input())->links() !!}
        </div>
    </div>
</div>


@endsection

@section('script')
<script>

    $(document).on('click', '.viewReturn', function () {
        let route = $(this).attr('data-route');
        $('#stockReturnModal').modal('show');
        $('#stockReturnDetails').html('<div class="text-center">Loading...</div>');        
        $.get(route, function (res) {
            $('#stockReturnDetails').html(res);
        });
    }); 

    $(document).on('click', '.cancelReturn', function () 
    {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Cancel Stock Return?',
            text: "This will reverse stock & vendor balance!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No'
    }).then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({
            url: "{{ route('admin.stock_returns.cancel') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },

            beforeSend: function () {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },

            success: function (res) {

                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelled!',
                        text: res.message
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Something went wrong'
                    });
                }

            },

            error: function (xhr) {

                let msg = 'Something went wrong';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg
                });
            }
        });
        });
    });
    $(document).ready(function () {
        setupPdfDownload('.downloadstockreturnpdf', 'data-downloadroutepdf');
        setupPdfDownload('.downloadstockreturncsv', 'data-downloadroutepdf');
    });
    </script>
@endsection
