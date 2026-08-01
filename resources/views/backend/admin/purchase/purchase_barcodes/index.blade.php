@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
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
                            'showPdf' => false,
                            'showCsv' => false,
                            'pdfId' => '',
                            'pdfRoute' => '',
                            'pdfClass' => '',
                            'csvId' => '',
                            'csvRoute' => '',
                            'csvClass' => '',
                        ])
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="purchase_no" label="{{ __('translation.purchase_no') }}" value="{{ request('purchase_no') }}" mainrows="2" />
                            <x-select-dropdown name="vendor_id" label="{{ __('translation.vendor') }}" :options="$vendors" :selected="request('vendor_id')" class="supplier" mainrows="2" />
                            <x-select-dropdown name="warehouse_id" label="{{ __('translation.warehouse') }}" :options="$warehouses" :selected="request('warehouse_id')" class="warehouse" mainrows="2" />
                            <x-text-input name="from_date" label="{{ __('translation.from_date') }}" value="{{ \App\Helpers\Settings::formatDate(request('from_date') ?? $today ?? '', Config::get('constants.dateformat.slashdmyonly')) }}" class="flatdatepickr" mainrows="2" />
                            <x-text-input name="to_date" label="{{ __('translation.to_date') }}" value="{{ request('to_date') }}" class="flatdatepickr" mainrows="2" />
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" />
                                    <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" />
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm rounded-2xl">
                <div class="card-header">
                    <h4 class="card-title">{{ __('translation.barcodes') }}</h4>
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
                                    <th>{{ __('translation.product') }}</th>
                                    <th>{{ __('translation.quantity') }}</th>
                                    <th>{{ __('translation.tracking') }}</th>
                                    <th>{{ __('translation.date') }}</th>
                                    <th>{{ __('translation.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td> {{ $row->purchase->purchase_no ?? '-' }}</td>
                                        <td> {{ $row->purchase->vendor->company_name ?? '-' }}</td>
                                        <td> {{ $row->purchase->warehouse->name ?? '-' }}</td>
                                        <td> {{ $row->masterItem->name ?? '-' }}</td>
                                        <!-- <td>{{$row->trackings->count() }} <br><small>({{ $row->quantity }})</small></td> -->
                                        <td>{{$row->trackings->count()}}</td>
                                        <td><span class="badge bg-info">{{ \App\Helpers\Settings::getDataTitle($row->tracking_type) }}</span></td>
                                        <td>{{ \App\Helpers\Settings::formatDate($row->purchase->created_at, Config::get('constants.dateformat.slashdmy')) }}</td>
                                        <td>
                                            @if($row->trackings->count() > 0) <x-href-input action="print_barcode" name="print_barcode" label="Print Barcode" href="{{ route('admin.purchases.printBarcode', \App\Helpers\Settings::getEncodeCode($row->purchase->id)) }}" /> @else N/A @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan=" 10" class="text-center">{{ __('translation.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="right user-navigation" style="float:right">{!! $items->appends(request()->input())->links() !!}</div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')

    <script>

        $(function () {

            $('.flatdatepickr').flatpickr({
                dateFormat: 'd/m/Y'
            });

        });

    </script>

@endsection