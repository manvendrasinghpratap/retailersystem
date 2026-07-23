@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection

@section('content')

    @include('backend.components.breadcrumb')
    {{-- FILTER SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                     <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloadwarehouseproductpdf',    
                        'pdfRoute' => route('admin.warehouses.warehouseproductPdf',['id'=>App\Helpers\Settings::getEncodeCode($warehouse->id)]),
                        'pdfClass' => 'downloadwarehouseproductpdf',
                        'csvId' =>'downloadwarehouseproductcsv',    
                        'csvRoute' => route('admin.warehouses.warehouseproductCsv',['id'=>App\Helpers\Settings::getEncodeCode($warehouse->id)]),
                        'csvClass' => 'downloadwarehouseproductcsv',
                        ])                 
                    </div>      
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="item_name" label="{{ __('translation.product_name') }}" value="{{request('item_name')}}" mainrows="3" placeholder="{{ __('translation.product_name') }}" />
                            <!-- <x-select-dropdown name="stock_filter" label="{{ __('translation.stock_filter') }}" :options="config('constants.productstockstatus')" :selected="request('stock_filter')" mainrows="3" class="stock_filter"/> -->
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" value="Filter" class="" />
                                    <x-filter-href-button name="reset" href="{{ route('admin.warehouses.products', request()->route('id')) }}" label="Reset" class="" />
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.warehouse_products') }}:</strong><strong>{{ $warehouse->name }}</strong></h4>
                    <div class="d-inline-block"></div>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.product_name') }}</th>
                                    <th>{{ __('translation.available_stock') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $key => $product)
                                    <tr>
                                        <td>{{ $items->firstItem() + $key }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ optional($product->stocks->first())->stock ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">{{ __('translation.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div class="right user-navigation">
                        {!! $items->appends(request()->input())->links() !!}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            setupPdfDownload('.downloadwarehouseproductpdf', 'data-downloadroutepdf');
            setupPdfDownload('.downloadwarehouseproductcsv', 'data-downloadroutepdf');
        });
    </script>
@endsection