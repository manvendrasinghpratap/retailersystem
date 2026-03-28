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
                    {{-- <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'categorypdf',
                        'pdfRoute' => route('menu.category.pdf'),
                        'pdfClass' => 'categorypdf',
                        'csvId' =>'categorycsv',
                        'csvRoute' => route('menu.category.csv'),
                        'csvClass' => 'categorycsv',
                        ])
                    </div> --}}
                </div>
                <div class="card-body">
                    <form name="cartlistingform" id="cartlistingform" method="GET">
                        <div class="row">
                            {{-- Product Name --}}
                            <x-select-dropdown name="category_id" label="Category" :options="$categories ?? []" :selected="request('category_id')" mainrows="3" class="category" />
                            <x-text-input name="name" label="Product Name" value="{{ request('name') }}" mainrows="3" />
                            <x-select-dropdown name="is_active" label="Status" :options="config('constants.accountstatus') ?? []" :selected="request()->get('is_active') ?? ''" class="is_active accountstatus" mainrows="2" />
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="Filter" value="Filter" class="" />
                                    <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" class="" />
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
                <div class="card-header">
                    <h4 class="card-title">
                        {{array_key_exists('route1Title', $breadcrumb) ? $breadcrumb['route1Title'] : ''}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.category')}}</th>
                                    <th>{{ __('translation.product_name')}}</th>
                                    <th>{{ __('translation.cost_price') }}</th>
                                    <th>{{ __('translation.selling_price') }}</th>
                                    <th>{{ __('translation.barcode')}}</th>
                                    <th>{{ __('translation.sku')}}</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($products))
                                    @foreach($products as $key => $p)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $p->category->name ?? '-' }}</td>
                                            <td>{{ $p->name }}</td>
                                            <td>{{ $p->cost_price }}</td>
                                            <td>{{ $p->selling_price }}</td>
                                            <td>{!! DNS1D::getBarcodeSVG($p->barcode, 'C128') !!}</td>
                                            <td>{{ $p->sku }}</td>
                                            <td>
                                                @if($p->status == 1)
                                                    <span class="badge bg-success">{{ __('translation.active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('translation.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <x-href-input name="edit" label="Edit" required href="{{ route('admin.products.edit', ['id' => \App\Helpers\Settings::getEncodeCode($p->id)]) }}" />
                                                <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($p->id) }}" data-routeurl="{{ route('admin.products.softdelete', $p->id) }}" />
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center">No Product Available</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if(!empty($products) && $products->count() > 0)
                        <div class="right user-navigation right">{!! $products->appends(request()->input())->links() !!}</div>
                    @endif
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            setupPdfDownload('.categorypdf', 'data-downloadroutepdf');
            setupPdfDownload('.categorycsv', 'data-downloadroutepdf');
        });
    </script>
@endsection