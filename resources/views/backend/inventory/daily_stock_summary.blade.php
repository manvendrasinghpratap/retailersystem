
@extends('backend.layouts.master-horizontal') {{-- matches your file --}}
@section('title', 'Daily Stock Report')

@section('content')
@include('backend.components.breadcrumb')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title d-inline-block">Filter</h4>
                <div class="d-inline-block">
                    <x-href-input  title="Download as PDF" id="downloaddailyStockSummaryPdf" name="downloaddailyStockSummaryPdf" href="javascript:void(0)"  action="pdf" class="downloaddailyStockSummaryPdf" :data-downloadroutepdf="route('dailyStockSummaryPdf')" />
                </div>
                <div class="d-inline-block">
                    @include('backend.components.exportpdfcsv', [
                    'pdfId' =>'downloaddailyStockSummaryPdf',    
                    'pdfRoute' => route('dailyStockSummaryPdf'),
                    'pdfClass' => 'downloaddailyStockSummaryPdf',
                    'csvId' =>'downloaddailyStockSummaryCsv',    
                    'csvRoute' => route('dailyStockSummaryCsv'),
                    'csvClass' => 'downloaddailyStockSummaryCsv',
                    ])                 
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : 'sales.index') }}" class="mb-3"> 
                    <div class="row">
                         <x-select-dropdown name="product_id" label="{{ __('translation.product') }}" :options="$products" :selected="request('product_id')" class="products" mainrows="4"/>
                         <x-date-input name="date" :label="__('translation.date')" value="{{ request()->get('date')?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickr" mainrows="2"/>
                        <x-button submitText="Filter" resetText="Reset" url="{{ route($breadcrumb['route']??'sales.index') }}" isbutton="1" iscancel="1" mainrows="2"/>                       
                    </div>
                </form>
                {{-- Filter Form End --}}
            </div>
        </div>
    </div>
</div>
<div class="row">
        <div class="col-12">
            <div class="card">
                {{-- <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ array_key_exists('title',$breadcrumb) ? $breadcrumb['title'] : '' }}</h4>
                </div> --}}
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{__('translation.product')}}</th>
                                    <th>{{__('translation.opening').' '. __('translation.stock')}}</th>
                                    <th>{{__('translation.in')}}</th>
                                    <th>{{__('translation.out')}}</th>
                                    <th>{{__('translation.closing').' '. __('translation.stock')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['title'] }}</td>
                                    <td>{{ $row['opening_stock'] }}</td>
                                    <td>{{ $row['today_in'] }}</td>
                                    <td>{{ $row['today_out'] }}</td>
                                    <td>{{ $row['closing_stock'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                </div>
                {{-- <div class="right user-navigation" style="float:right">{!! $report->appends(request()->input())->links() !!}</div> --}}
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloaddailyStockSummaryPdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloaddailyStockSummaryPdf', 'data-downloadroutepdf');
    });
</script>
@endsection
