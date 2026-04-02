@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? __('translation.sales_record_listing') }}
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
                    'pdfRoute' => route('report.productSalesReportPdf'),
                    'pdfClass' => 'downloadproductSalesReportPdf',
                    'csvRoute' => route('report.productSalesReportCSV'),
                    'csvClass' => 'downloadproductSalesReportCSV',
                    ])                 
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : 'sales.index') }}" class="mb-3"> 
                    <div class="row">
                        <x-select-dropdown name="product_id" label="{{ __('translation.products') }}" :options="$products" :selected="request('product_id')" class="products" mainrows="3"/>
                        <x-date-input name="from_date" :label="__('translation.from_date')" value="{{ request()->get('from_date')?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickr" mainrows="2"/>
                        <x-date-input name="to_date" :label="__('translation.to_date')" value="{{ request()->get('to_date')?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickrto" mainrows="2" data-mindate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-1 month'))) }}" data-maxdate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('0 days'))) }}" />
                        <x-button submitText="Filter" resetText="Reset" url="{{ route($breadcrumb['route']??'sales.index') }}" isbutton="1" iscancel="1" mainrows="2"/>                       
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
                {{-- Sales Table --}}
                <div class="table-responsive overflowx">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('translation.date') }}</th>
                                <th>{{ __('translation.product') }}</th>
                                <th>{{ __('translation.ngn') .' '.__('translation.price')}}</th>
                                <th>{{ __('translation.sold').' '. __('translation.quantity')}}</th>
                                <th>{{ __('translation.ngn').' '.__('translation.amount')}}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($records as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ App\Helpers\Settings::getFormattedDate($row->date) }}</td>
                                    <td>{{ $row->product_name }}</td>
                                    <td>{{ __('translation.ngn').' '. \App\Helpers\Settings::getcustomnumberformat($row->unit_price) }}</td>
                                    <td>{{ $row->total_sold }}</td>
                                    <td>{{ __('translation.ngn').' '.\App\Helpers\Settings::getcustomnumberformat($row->total_amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No records found</td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if($records->isNotEmpty())
                            <tfoot>
                                <tr class="font-weight-bold bg-light text-end">
                                    <td colspan="4" class="text-start"><strong>{{ __('translation.total') }}</strong></td>
                                    <td>
                                       <strong>{{ $records->sum('total_sold') }}</strong>
                                    </td>
                                    <td>
                                        <strong>{{ __('translation.ngn').' '.\App\Helpers\Settings::getcustomnumberformat($records->sum('total_amount')) }}</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>

                </div>
                {{-- Pagination --}}
                 <div class="right user-navigation" style="float:right">{!! $records->appends(request()->input())->links() !!}</div>
                {{-- Pagination End --}}
            </div>
        </div>
    </div>
</div>
@endsection
