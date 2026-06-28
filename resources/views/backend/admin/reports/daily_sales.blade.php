@extends('backend.layouts.master-horizontal')
@section('title')
    {{Config::get('constants.admin') || $breadcrumb['title'] ?? __('translation.dashboard') }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')

    {{-- ================= FILTER SECTION ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                    <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloadstaffpdf',    
                        'pdfRoute' => route('reports.daily.sales.pdf'),
                        'pdfClass' => 'downloadstaffpdf',
                        'csvId' =>'downloadstaffcsv',    
                        'csvRoute' => route('reports.daily.sales.csv'),
                        'csvClass' => 'downloadstaffcsv',
                        ])                 
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row"> 
                            <x-text-input name="from_date" :label="__('translation.from_date')" value="{{ request('from_date') }}" mainrows="2" class="flatdatepickr" />
                            <x-text-input name="to_date" :label="__('translation.to_date')" value="{{ request('to_date')  }}" mainrows="2" class="flatdatepickr" />
                            <x-text-input name="invoice_no" :label="__('translation.invoice_no')" value="{{ request('invoice_no') }}" mainrows="2" />
                            @if(Auth::user()->hasDesignation())
                                <x-select-dropdown name="staff_id" label="Staff" :options="$staffs ?? []" :selected="request('staff_id') ?? ''" class="staff" mainrows="3" />
                            @endif
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
    {{-- ================= LISTING SECTION ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
                        {{ __('translation.listing') }}
                    </h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.invoice_no') }}</th>
                                    <th>{{ __('translation.customer_name') }}</th>
                                    @if(Auth::user()->hasDesignation())
                                        <th>{{ __('translation.staff_name') }}</th>
                                    @endif
                                    <th>{{ __('translation.payment_type') }}</th>
                                    @foreach($paymentMethods as $method)
                                        <th class="text-center">{{ __('translation.currency') }} {{ $method['name'] }}</th>
                                    @endforeach
                                    <th>{{ __('translation.currency') }} {{ __('translation.total_amount') }}</th>
                                    <th>{{ __('translation.transaction_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    @php
                                        $summary = $sale->payments
                                            ->groupBy('method')
                                            ->map(fn($items) => $items->sum('amount'));
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $sale->invoice_no }}</td>
                                        <td>{{ $sale->customer->name ?? '-' }}</td>
                                        @if(Auth::user()->hasDesignation())
                                            <td>{{ $sale->user->name ?? '-' }}</td>
                                        @endif 
                                        <td>{{ \App\Helpers\Settings::getDataUcfirst($paymentTypes[$sale->payment_type] ?? '') }}</td>
                                        @foreach($paymentMethods as $method)
                                            <td class="text-center"> {{ __('translation.currency') }}{{ \App\Helpers\Settings::getcustomnumberformat($summary[$method['short_name']] ?? 0) }}</td>
                                        @endforeach
                                        <td>{{ __('translation.currency') }}{{ \App\Helpers\Settings::getcustomnumberformat($sale->total) }}</td>
                                        <td>{{ \App\Helpers\Settings::getFormattedDatetime($sale->created_at) }}</td>
                                    </tr>
                                @empty
                                    @php
                                        $columns = 6 + count($paymentMethods) + (Auth::user()->hasDesignation() ? 1 : 0);
                                    @endphp
                                    <tr>
                                        <td colspan="{{ $columns }}" class="text-center"> {{ __('translation.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>    
                            <tfoot>
                                <tr class="fw-bold bg-light">
                                    @if(Auth::user()->hasDesignation())
                                        <td></td>
                                    @endif
                                    <td colspan="4" class="text-end"> {{ __('translation.total') }}</td>
                                    @foreach($paymentMethods as $method)
                                        <td class="text-center">
                                            {{ __('translation.currency') }}{{ \App\Helpers\Settings::getcustomnumberformat($paymentTotals[$method['short_name']] ?? 0) }}
                                        </td>
                                    @endforeach
                                    <td> {{ __('translation.currency') }}{{ \App\Helpers\Settings::getcustomnumberformat($totalSales) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot> 
                        </table> 
                    </div>
                    {{-- Pagination --}}
                    <div class="right user-navigation right">
                        {!! $sales->appends(request()->input())->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloadstaffpdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloadstaffcsv', 'data-downloadroutepdf');
    });
</script>
@endsection