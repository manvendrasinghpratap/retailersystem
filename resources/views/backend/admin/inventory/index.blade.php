@extends('backend.layouts.master-horizontal') {{-- matches your file --}}
@section('title', 'Stock Management')

@section('content')
    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                     <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloadinventorypdf',    
                        'pdfRoute' => route('admin.inventory.exportPdf'),
                        'pdfClass' => 'downloadinventorypdf',
                        'csvId' =>'downloadinventorycsv',    
                        'csvRoute' => route('admin.inventory.exportCsv'),
                        'csvClass' => 'downloadinventorycsv',
                        ])                 
                    </div>      
                </div>
                <div class="card-body">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route(array_key_exists('route2', $breadcrumb) ? $breadcrumb['route2'] : 'admin.inventory') }}" class="mb-3">
                        <div class="row">
                            <x-text-input name="name" label="{{ __('translation.product_name')}}" value="{{ request('name') }}" mainrows="3" />
                            <x-select-dropdown name="category_id" label="{{ __('translation.category')}}" :options="$categories" :selected="request()->get('category_id') ?? ''" class="category" mainrows="2" />
                            <x-button submitText="Filter" resetText="{{ __('translation.reset') }}" url="{{ route($breadcrumb['route2'] ?? 'admin.inventory') }}" isbutton="1" iscancel="1" mainrows="2" />
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
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.category_name')}}</th>
                                    <th>{{ __('translation.product_name')}}</th>
                                    <th>{{ __('translation.sku')}}</th>
                                    <th>{{ __('translation.barcode')}}</th>
                                    <th>{{ __('translation.stock')}}</th>
                                    <th>{{ __('translation.low_alert')}}</th>
                                    <th>{{ __('translation.status')}}</th>
                                    <th>{{ __('translation.action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventory as $stock)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $stock->product->category->name ?? '' }}</td>
                                        <td>{{ $stock->product->name ?? '' }}</td>
                                        <td>{{ $stock->product->sku ?? '' }}</td>
                                        <td>{!! DNS1D::getBarcodeSVG($stock->product->barcode, 'C128') !!}</td>
                                        <td>{{ $stock->stock }}</td>
                                        <td>{{ $stock->low_stock_alert }}</td>
                                        <td>{{ $stock->isLowStock() ? __('translation.low_stock') : __('translation.normal_stock') }}</td>
                                        <td>
                                            <x-href-input action="print_barcode" data-route="{{ route('barcode.form', \App\Helpers\Settings::getEncodeCodeWithHashids($stock->product_id)) }}" data-id="{{ \App\Helpers\Settings::getEncodeCodeWithHashids($stock->product_id) }}"  name="print" label="" href="javascript:void(0);" class="btn btn-sm barcodeBtn" icon="fa fa-print" :nohref="true"  text="Print Barcode" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            @lang('translation.no_data_found')
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="right user-navigation" style="float:right">{!! $inventory->appends(request()->input())->links() !!}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            setupPdfDownload('.downloadinventorypdf', 'data-downloadroutepdf');
            setupPdfDownload('.downloadinventorycsv', 'data-downloadroutepdf');
        });
    </script>
    <script>
$(document).on('click','.barcodeBtn',function(){
    let id = $(this).data('id');
    let url = $(this).data('route');
    $('#barcodeModal').modal('show');
    $('#barcodeModalBody').load(url);
});
</script>
@endsection