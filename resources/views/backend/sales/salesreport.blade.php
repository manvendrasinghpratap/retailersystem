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
                    'pdfId' =>'downloaddailysalespdf',    
                    'pdfRoute' => route('report.daywisesales'),
                    'pdfClass' => 'downloaddailysalespdf',
                    'csvId' =>'downloaddailysalescsv',    
                    'csvRoute' => route('report.daywisesalesCsv'),
                    'csvClass' => 'downloaddailysalescsv',
                    ])                 
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : 'sales.index') }}" class="mb-3"> 
                    <div class="row">
                        <x-date-input name="from_date" :label="__('translation.from_date')" value="{{ request()->get('from_date')?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickr" mainrows="2"/>
                        <x-date-input name="to_date" :label="__('translation.to_date')" value="{{ request()->get('to_date')?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickrto" mainrows="2" data-mindate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-1 month'))) }}" data-maxdate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('0 days'))) }}" />
                        <x-select-dropdown name="user_id" label="{{ __('translation.staff') }}" :options="$users" :selected="request('user_id')" class="staff" mainrows="3"/>
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
                                <th>{{ __('translation.staff') }}</th>
                                <th>{{ __('translation.product') }}</th>
                                <th>{{ __('translation.ngn').' '.__('translation.price') }}</th>
                                <th>{{ __('translation.taken') }}</th>
                                <th>{{ __('translation.sold') }}</th>
                                <th>{{ __('translation.ngn').' '.__('translation.amount') }}</th>
                                <th>{{ __('translation.returned') }}</th>
                                <th>{{ __('translation.remarks') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $ii = 0; $sum = []; @endphp
                            @forelse($records as $record)
                                <tr>
                                    <td>{{ ++$ii }}</td>
                                    <td>{{ \App\Helpers\Settings::getFormattedDate($record->date) }}</td>
                                    <td>{{ $record->user->name ?? '-' }}</td>
                                    <td>{{ $record->menu->title ?? '-' }}</td>
                                    <td>{{ __('translation.ngn').' '. \App\Helpers\Settings::getcustomnumberformat($record->price) }}</td>
                                    <td>{{ $record->quantity_taken }}</td>
                                    <td>{{ $record->quantity_sold }}</td>
                                    <td>{{ __('translation.ngn').' '. \App\Helpers\Settings::getcustomnumberformat($record->quantity_sold * $record->price) }}@php($sum[] = $record->quantity_sold * $record->price)</td>
                                    <td>{{ $record->quantity_returned }}</td>
                                    <td>{{ $record->remarks }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">{{ __('translation.no_sales_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($records->isNotEmpty())
                        <tfoot>
                            <tr class="font-weight-bold bg-light">
                                <td colspan="5"><strong> Total </strong></td>
                                <td>{{ $totals['taken'] }}</td>
                                <td>{{ $totals['sold'] }}</td>
                                <td>{{ __('translation.ngn').' '. \App\Helpers\Settings::getcustomnumberformat(array_sum($sum)) }}</td>
                                <td>{{ $totals['returned'] }}</td>
                                <td></td>
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

@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloaddailysalespdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloaddailysalescsv', 'data-downloadroutepdf');
    });
</script>
@endsection
