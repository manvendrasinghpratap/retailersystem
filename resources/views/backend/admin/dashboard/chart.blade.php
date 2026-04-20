@extends('backend.layouts.master-horizontal')

@section('title')
    {{ Config::get('constants.admin') || $breadcrumb['title'] ?? __('translation.dashboard') }}
@endsection

@section('content')
    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <div class="row g-4">

                        <!-- Revenue -->
                        <div class="col-md-3">
                            <div class="card text-white shadow border-0 gradient1 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <small>{{ __('translation.total_revenue') }}</small>
                                        <h4 class="mb-0">{{ __('translation.ngn') }} {{ number_format($totalRevenue, 2) }}</h4>
                                    </div>
                                    <div class="icon naira-icon">{{ __('translation.ngn') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Orders -->
                        <div class="col-md-3">
                            <div class="card text-white shadow border-0 gradient2 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <small>{{ __('translation.sales_orders') }}</small>
                                        <h4 class="mb-0">{{ $totalOrders }}</h4>
                                    </div>
                                    <div class="icon">🧾</div>
                                </div>
                            </div>
                        </div>

                        <!-- Customers -->
                        <div class="col-md-3">
                            <div class="card text-white shadow border-0 gradient3 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <small>{{ __('translation.customers') }}</small>
                                        <h4 class="mb-0">{{ $totalCustomers }}</h4>
                                    </div>
                                    <div class="icon">👥</div>
                                </div>
                            </div>
                        </div>

                        <!-- Products -->
                        <div class="col-md-3">
                            <div class="card text-white shadow border-0 gradient4 h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <small>{{ __('translation.products') }}</small>
                                        <h4 class="mb-0">{{ $totalProducts }}</h4>
                                    </div>
                                    <div class="icon">📦</div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-3">

        <div class="card shadow border-0">
            <div class="card-body">

                <!-- FILTER -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">Sales Dashboard</h5>
                        <small class="text-muted">
                            {{ __('translation.date') }}:
                            {{ request('date') ? App\Helpers\Settings::formatDate(request('date'), \Config::get('constants.dateformat.slashdmyonly')) : App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}
                        </small>
                    </div>

                    <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-center">

                        <div class="col-auto">
                            <x-text-input mainrows="12" :islabel="false" name="date" :label="__('translation.date')" value="{{ request('date') ?? App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickr form-control" />
                        </div>

                        <div class="col-auto top">
                            <x-filter-submit-button name="submit" label="Filter" value="Filter" class="btn btn-primary" />
                        </div>

                        <div class="col-auto top">
                            <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" class="btn btn-secondary" />
                        </div>

                    </form>
                </div>
                <!-- PRODUCT CHARTS -->
                <div class="row">
                    <!-- DAILY GRAPH -->
                    <!--  -->
                    <div class="col-12 mb-3 text-center border border-secondary rounded py-2 card text-white shadow border-0 gradient{{ rand(1, 4) }} h-100">
                        <label class="fw-bold text-white fs-2 mb-0">
                            {{ __('translation.daily_sales_report') }}
                        </label>
                    </div>
                    <div class="col-md-7">
                        <div class="card shadow border-0 mb-3">
                            <div class="card-body">
                                <!-- <h6 class="mb-3">{{ __('translation.daily_sales') }} {{ __('translation.graph') }}</h6> -->
                                <div id="chart" style="height:400px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card shadow border-0 mb-3">
                            <div class="card-body">
                                <!-- <h6 class="mb-3 text-center">{{ __('translation.product_sales') }}</h6> -->
                                <div id="dailyChart" style="height:400px;"></div>
                            </div>
                        </div>
                    </div>


                    <!-- WEEKLY GRAPH -->
                    <div class="col-12 mb-3 text-center border border-secondary rounded py-2 card text-white shadow border-0 gradient{{ rand(1, 4) }} h-100">
                        <label class="fw-bold text-white fs-2 mb-0">
                            {{ __('translation.weekly_sales_report') }}
                        </label>
                    </div>
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <!-- <h5 class="mb-3">Weekly Sales Graph</h5> -->
                                <div id="weeklySalesChart" style="height:400px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card shadow border-0 mb-3">
                            <div class="card-body">
                                <!-- <h6 class="mb-3">Product Sales (Weekly)</h6> -->
                                <div id="weeklyChart" style="height:400px;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- MONTHLY GRAPH -->
                    <div class="col-12 mb-3 text-center border border-secondary rounded py-2 card text-white shadow border-0 gradient{{ rand(1, 4) }} h-100">
                        <label class="fw-bold text-white fs-2 mb-0">
                            {{ __('translation.monthly_sales_report') }}
                        </label>
                    </div>
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <!-- <h5 class="mb-3">Monthly Sales Graph</h5> -->
                                <div id="monthlySalesChart" style="height:400px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <!-- <h6 class="mb-3">Product Sales (Monthly)</h6> -->
                                <div id="monthlyChart" style="height:400px;"></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

    <!-- HIGHCHARTS -->
    <script src="https://code.highcharts.com/highcharts.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ==================================================
            // DAILY SALES GRAPH (EXISTING)
            // ==================================================
            let hours = @json($hours);
            let totals = @json($totals);

            let max = Math.max(...totals);
            let peakIndex = totals.indexOf(max);

            let coloredData = totals.map((val, i) => ({
                y: val,
                color: i === peakIndex ? '#ff4d4f' : '#4f46e5'
            }));

            Highcharts.chart('chart', {
                chart: {
                    type: 'column'
                },
                title: { text: '' },
                xAxis: {
                    categories: hours
                },
                yAxis: {
                    title: { text: 'Sales' }
                },
                tooltip: {
                    pointFormat: '<b>{{ __("translation.ngn") }} {point.y}</b>'
                },
                legend: { enabled: false },
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

            // ==================================================
            // WEEKLY SALES GRAPH
            // ==================================================
            Highcharts.chart('weeklySalesChart', {
                chart: { type: 'line' },
                title: { text: '' },
                xAxis: {
                    categories: @json($weekLabels)
                },
                yAxis: {
                    title: { text: 'Sales' }
                },
                tooltip: {
                    pointFormat: '<b>{{ __("translation.ngn") }} {point.y}</b>'
                },
                series: [{
                    name: 'Weekly Sales',
                    data: @json($weeklyTotals)
                }]
            });

            // ==================================================
            // MONTHLY SALES GRAPH
            // ==================================================
            Highcharts.chart('monthlySalesChart', {
                chart: { type: 'area' },
                title: { text: '' },
                xAxis: {
                    categories: @json($monthLabels)
                },
                yAxis: {
                    title: { text: 'Sales' }
                },
                tooltip: {
                    pointFormat: '<b>{{ __("translation.ngn") }} {point.y}</b>'
                },
                series: [{
                    name: 'Monthly Sales',
                    data: @json($monthlyTotals)
                }]
            });

            // PRODUCT DONUTS
            renderDonut('dailyChart', @json($productDaily), "{{ __('translation.daily_sales_report') }}");
            renderDonut('weeklyChart', @json($productWeekly), "{{ __('translation.weekly_sales_report') }}");
            renderDonut('monthlyChart', @json($productMonthly), "{{ __('translation.monthly_sales_report') }}");

        });

        function renderDonut(id, data, title) {
            let chartData = data.map(item => ({
                name: item.name.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()),
                y: parseFloat(item.total_items_sold)
            }));

            Highcharts.chart(id, {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: title.toUpperCase()
                },
                plotOptions: {
                    pie: {
                        innerSize: '65%',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name} # {point.y}'
                        }
                    }
                },
                tooltip: {
                    pointFormat: '<b>{point.name} # {point.y}</b>'
                },
                series: [{
                    name: 'Items Sold',
                    data: chartData
                }]
            });
        }
    </script>

@endsection