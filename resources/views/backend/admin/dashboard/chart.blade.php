@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? __('translation.dashboard') }}
@endsection

@section('content')

    @include('backend.components.breadcrumb')

    {{-- ========================================================= --}}
    {{-- SUMMARY CARDS --}}
    {{-- ========================================================= --}}
    <div class="row">

        {{-- Revenue --}}
        <div class="col-md-3 mb-3">
            <div class="card text-white border-0 shadow gradient1 h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-white">@lang('translation.total_revenue')</h3>
                        <h4>
                            @lang('translation.ngn') {{ number_format($totalRevenue, 2) }}
                        </h4>
                    </div>
                    <div class="icon naira-icon">@lang('translation.ngn')</div>
                </div>
            </div>
        </div>

        {{-- Orders --}}
        <div class="col-md-3 mb-3">
            <div class="card text-white border-0 shadow gradient2 h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-white">@lang('translation.total_orders')</h3>
                        <h4 class="mb-0">{{ $totalOrders }}</h4>
                    </div>
                    <div class="icon">🧾</div>
                </div>
            </div>
        </div>

        {{-- Customers --}}
        <div class="col-md-3 mb-3">
            <div class="card text-white border-0 shadow gradient3 h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-white">@lang('translation.total_customers')</h3>
                        <h4 class="mb-0">{{ $totalCustomers }}</h4>
                    </div>
                    <div class="icon">👥</div>
                </div>
            </div>
        </div>

        {{-- Products --}}
        <div class="col-md-3 mb-3">
            <div class="card text-white border-0 shadow gradient4 h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-white">@lang('translation.total_products')</h3>
                        <h4 class="mb-0">{{ $totalProducts }}</h4>
                    </div>
                    <div class="icon">📦</div>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================= --}}
    {{-- SALES ANALYTICS --}}
    {{-- ========================================================= --}}
    <div class="card shadow border-0">
        <div class="card-body">

            {{-- FILTER BAR --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">

                <div>
                    <h5 class="mb-1">@lang('translation.sales_dashboard')</h5>
                    <small class="text-muted">
                        @lang('translation.date') :
                        {{ request('date') ? App\Helpers\Settings::formatDate(request('date'), Config::get('constants.dateformat.slashdmyonly')) : App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}
                    </small>
                </div>

                <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2 flex-wrap">

                    <x-text-input :islabel="false" name="date" value="{{ request('date') ?? App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickr form-control ml-5" mainrows="5" />
                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" class="btn btn-primary height-30" />
                    <x-filter-href-button name="reset" href="{{ route($breadcrumb['route2']) }}" label="{{ __('translation.reset') }}" class="btn btn-secondary height-30" />

                </form>

            </div>

            {{-- ========================================================= --}}
            {{-- DAILY REPORT --}}
            {{-- ========================================================= --}}
            <div class="row">

                <div class="col-12 mb-3">
                    <div class="report-title gradient1">
                        @lang('translation.daily_sales_report') ( {{ request('date') ? App\Helpers\Settings::formatDate(request('date'), Config::get('constants.dateformat.slashdmyonly')) : App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }} )
                    </div>
                </div>

                <div class="col-md-7 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div id="chart" style="height:400px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div id="dailyChart" style="height:400px;"></div>
                        </div>
                    </div>
                </div>

                {{-- WEEKLY --}}
                <div class="col-12 mb-3">
                    <div class="report-title gradient2">
                        @lang('translation.weekly_sales_report') ( {{ request('date') ? App\Helpers\Settings::formatDate(request('date'), Config::get('constants.dateformat.slashdmyonly')) : App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }} )
                    </div>
                </div>

                <div class="col-md-7 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div id="weeklySalesChart" style="height:400px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div id="weeklyChart" style="height:400px;"></div>
                        </div>
                    </div>
                </div>

                {{-- MONTHLY --}}
                <div class="col-12 mb-3">
                    <div class="report-title gradient3">
                        @lang('translation.monthly_sales_report') ( {{ request('date') ? App\Helpers\Settings::formatDate(request('date'), Config::get('constants.dateformat.slashdmyonly')) : App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }} )
                    </div>
                </div>

                <div class="col-md-7 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div id="monthlySalesChart" style="height:400px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div id="monthlyChart" style="height:400px;"></div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- STYLES --}}
    {{-- ========================================================= --}}
    <style>
        .report-title {
            padding: 12px;
            border-radius: 8px;
            color: #fff;
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }
    </style>

    {{-- ========================================================= --}}
    {{-- CHARTS --}}
    {{-- ========================================================= --}}
    <script src="https://code.highcharts.com/highcharts.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // DAILY
            let hours = @json($hours);
            let totals = @json($totals);

            let max = Math.max(...totals);
            let peakIndex = totals.indexOf(max);

            let coloredData = totals.map((val, i) => ({
                y: val,
                color: i === peakIndex ? '#ff4d4f' : '#4f46e5'
            }));

            Highcharts.chart('chart', {
                chart: { type: 'column' },
                title: { text: 'Hourly Sales' },
                xAxis: { categories: hours },
                yAxis: { title: { text: 'Sales' } },
                legend: { enabled: false },
                tooltip: { pointFormat: '<b>₦ {point.y}</b>' },
                series: [{
                    name: 'Sales',
                    data: coloredData,
                    borderRadius: 6
                }, {
                    type: 'line',
                    data: totals,
                    marker: { enabled: false }
                }]
            });

            // WEEKLY
            Highcharts.chart('weeklySalesChart', {
                chart: { type: 'line' },
                title: { text: 'Weekly Sales' },
                xAxis: { categories: @json($weekLabels) },
                yAxis: { title: { text: 'Sales' } },
                series: [{
                    name: 'Weekly Sales',
                    data: @json($weeklyTotals)
                }]
            });

            // MONTHLY
            Highcharts.chart('monthlySalesChart', {
                chart: { type: 'area' },
                title: { text: 'Monthly Sales' },
                xAxis: { categories: @json($monthLabels) },
                yAxis: { title: { text: 'Sales' } },
                series: [{
                    name: 'Monthly Sales',
                    data: @json($monthlyTotals)
                }]
            });

            // DONUTS
            renderDonut('dailyChart', @json($productDaily), 'Breakdown of Daily Products Sales');
            renderDonut('weeklyChart', @json($productWeekly), 'Breakdown of Weekly Products Sales');
            renderDonut('monthlyChart', @json($productMonthly), 'Breakdown of Monthly Products Sales');

        });

        function renderDonut(id, data, title) {
            let chartData = data.map(item => ({
                name: item.name,
                y: parseFloat(item.total_items_sold)
            }));

            Highcharts.chart(id, {
                chart: { type: 'pie' },
                title: { text: title },
                plotOptions: {
                    pie: {
                        innerSize: '65%',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.y}'
                        }
                    }
                },
                series: [{
                    name: 'Items',
                    data: chartData
                }]
            });
        }
    </script>

@endsection