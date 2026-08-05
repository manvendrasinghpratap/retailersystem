@extends('backend.layouts.master-horizontal')
@section('title'){{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}@endsection
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
                            'pdfId' => 'downloadstocklistingpdf',
                            'pdfRoute' => route('admin.warehouses.exportstocklistingPdf'),
                            'pdfClass' => 'downloadstocklistingpdf',
                            'csvId' => 'downloadstocklistingcsv',
                            'csvRoute' => route('admin.warehouses.exportstocklistingCsv'),
                            'csvClass' => 'downloadstocklistingcsv',
                        ])
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            {{-- WAREHOUSE --}}
                            <x-select-dropdown name="warehouse_id" label="{{ __('translation.warehouse') }}" :options="$warehouses" :selected="request('warehouse_id')" mainrows="3" class="warehouse" />
                            {{-- PRODUCT NAME --}}
                            <x-text-input name="product_name" label="{{ __('translation.product') }}" value="{{ request('product_name') }}" mainrows="3" />
                            {{-- BUTTONS --}}
                            <div class="col-xl-3 col-md-3">
                                <div class="form-group mb-3">
                                    <label class="d-block">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" value="Filter" />
                                        <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="{{ __('translation.reset') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- LISTING SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        {{ $breadcrumb['title'] ?? '' }}
                        {{ __('translation.listing') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>{{ __('translation.warehouse') }}</th>
                                    <th>{{ __('translation.product') }}</th>
                                    <th>{{ __('translation.available_qty') }}</th>
                                    <th>{{ __('translation.last_updated') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stocks as $stock)
                                    <tr>
                                        <td>{{ $stocks->firstItem() + $loop->index }}</td>
                                        <td>{{ $stock->warehouse->name ?? '-' }}</td>
                                        <td>{{ $stock->masterItem->name ?? '-' }}</td>
                                        <td>{{ $stock->stock }}</td>
                                        <td>
                                            {{ \App\Helpers\Settings::getFormattedDatetime($stock->updated_at) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            {{ __('translation.no_stock_available') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div class="mt-3 d-flex justify-content-end">
                        {!! $stocks->appends(request()->input())->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $(document).ready(function () {
            setupPdfDownload('.downloadstocklistingpdf', 'data-downloadroutepdf');
            setupPdfDownload('.downloadstocklistingcsv', 'data-downloadroutepdf');
        });
    </script>
@endsection