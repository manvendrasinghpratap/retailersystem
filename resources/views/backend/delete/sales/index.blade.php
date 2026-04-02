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
                    'pdfId' =>'salespdf',    
                    'pdfRoute' => route('sales.salespdf'),
                    'pdfClass' => 'salespdf',
                    'csvId' =>'salescsv',    
                    'csvRoute' => route('sales.salescsv'),
                    'csvClass' => 'salescsv',
                    ])                 
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : 'sales.index') }}" class="mb-3"> 
                    <div class="row">
                        <x-select-dropdown name="user_id" label="{{ __('translation.sales_executive') }}" :options="$users" :selected="request('user_id')" class="sales_executive" mainrows="3"/>
                        {{-- <x-date-input name="date" :label="__('translation.date')" value="{{ request()->get('date')?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickr" mainrows="2"/> --}}
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
                                <th>{{ __('translation.staff') }}/{{ __('translation.customer') }}</th>
                                <th>{{ __('translation.product') }}</th>
                                <th>{{ __('translation.taken') }}</th>
                                <th>{{ __('translation.incentive') }}</th>
                                <th>{{ __('translation.sold') }}</th>
                                <th>{{ __('translation.returned') }}</th>
                                <th>{{ __('translation.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($finalRecords as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ \App\Helpers\Settings::getFormattedDate($row['date']) }}</td>
                                    <td>{{ $row['staff'] }}
                                        @if(!empty($row['order_id']))
                                            <span class="badge bg-success">From Order #{{ $row['order_id'] }}</span>
                                        @else
                                            <span class="badge bg-secondary">Manual Sale</span>
                                        @endif</td>
                                    <td>{{ $row['products'] }}</td>
                                    <td>{{ $row['taken'] }}</td>
                                    <td>{{ $row['incentive'] }}</td>
                                    <td>{{ $row['sold'] }}</td>
                                    <td>{{ $row['returned'] }}</td>
                                    <td>
                                        {{-- Show origin --}}
                                        @if(!empty($row['order_id']))
                                        @else
                                            {{-- ✅ Actions only if manual (no order_id) --}}
                                            @if($row['sold'] == 0 && $row['returned'] == 0)
                                                <x-href-input  
                                                    name="edit"  
                                                    href="{!! route('sales.stockloading') . '?' . http_build_query([ 
                                                        'user_id' => $row['user_id'],
                                                        'date' => \App\Helpers\Settings::getFormattedDate($row['date']) 
                                                    ]) !!}"  
                                                    action="edit" 
                                                /> 
                                            @endif  

                                            @php
                                                $recordDate = \Carbon\Carbon::parse($row['date']);
                                                $diffInDays = $recordDate->diffInDays(\Carbon\Carbon::now());
                                            @endphp 

                                            @if ($diffInDays <= 2)
                                                <x-href-input 
                                                    name="view" 
                                                    href="{!! route('sales.unsoldstockreturn') . '?' . http_build_query([
                                                        'user_id' => $row['user_id'], 
                                                        'date' => \App\Helpers\Settings::getFormattedDate($row['date'])
                                                    ]) !!}" 
                                                    action="update" 
                                                />       
                                            @else
                                                <x-href-input name="Expired" href="javascript:void(0)" action="lock" /> 
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">{{ __('translation.no_sales_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if(!empty($totals) && ($totals['taken'] > 0 || $totals['sold'] > 0 || $totals['returned'] > 0))
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total</strong></td>
                                    <td><strong>{{ $totals['taken'] }}</strong></td>
                                    <td><strong>{{ $totals['incentive_taken'] }}</strong></td>
                                    <td><strong>{{ $totals['sold'] }}</strong></td>
                                    <td><strong>{{ $totals['returned'] }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>

                </div>
                {{-- Pagination --}}
                {{-- <div class="right user-navigation" style="float:right"> {!! $records->appends(request()->input())->links() !!}</div> --}}
                {{-- Pagination End --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        setupPdfDownload('.salespdf', 'data-downloadroutepdf');
        setupPdfDownload('.salescsv', 'data-downloadroutepdf');
    });
</script>
@endsection
