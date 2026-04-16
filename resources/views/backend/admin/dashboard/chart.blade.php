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
                                        <h4 class="mb-0">{{ __('translation.ngn') }} {{ number_format($totalRevenue, 2) }}
                                        </h4>
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

        <!-- CHART -->
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">{{ __('translation.hourly_sales') }}</h5>
                        <small class="text-muted">{{ __('translation.date') }}: {{ request('date') ? App\Helpers\Settings::formatDate(request('date'), \Config::get('constants.dateformat.slashdmyonly')) : App\Helpers\Settings::getFormattedDate(date('Y-m-d'))  }}</small>
                    </div>
                    <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-center">
                        <!-- Date -->
                        <div class="col-auto">
                            <x-text-input mainrows="12" :islabel="false" name="date" :label="__('translation.date')" value="{{request('date') ?? App\Helpers\Settings::getFormattedDate(date('Y-m-d'))  }}" class="flatdatepickr form-control" />
                        </div>
                        <!-- Filter Button -->
                        <div class="col-auto top">
                            <x-filter-submit-button name="submit" label="Filter" value="Filter" class="btn btn-primary" />
                        </div>
                        <!-- Reset Button -->
                        <div class="col-auto top">
                            <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" class="btn btn-secondary" />
                        </div>

                    </form>
                </div>

                <div id="chart" style="height:400px;"></div>
                <!-- PRODUCT CHARTS -->
                <div class="row mt-3">

                    <!-- DAILY -->
                    <div class="col-md-12">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <h6 class="mb-3">Product Sales (Daily)</h6>
                                <div id="dailyChart" style="height:300px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- WEEKLY -->
                    <div class="col-md-12">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <h6 class="mb-3">Product Sales (Weekly)</h6>
                                <div id="weeklyChart" style="height:300px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- MONTHLY -->
                    <div class="col-md-12">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <h6 class="mb-3">Product Sales (Monthly)</h6>
                                <div id="monthlyChart" style="height:300px;"></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

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
                    type: 'column',
                    backgroundColor: 'transparent'
                },

                title: { text: '' },

                xAxis: {
                    categories: hours
                },

                yAxis: {
                    title: { text: 'Sales' }
                },

                tooltip: {
                    pointFormat: '<b>{{ __('translation.ngn') }} {point.y}</b>'
                },

                legend: { enabled: false },

                series: [
                    {
                        name: 'Sales',
                        data: coloredData,
                        borderRadius: 6
                    },
                    {
                        type: 'line',
                        data: totals,
                        marker: { enabled: false }
                    }
                ]

            });

            // =============================
            // DAILY
            // =============================
            renderDonut('dailyChart', @json($productDaily), 'Daily Sales');

            // =============================
            // WEEKLY
            // =============================
            renderDonut('weeklyChart', @json($productWeekly), 'Weekly Sales');

            // =============================
            // MONTHLY
            // =============================
            renderDonut('monthlyChart', @json($productMonthly), 'Monthly Sales');
        });

        function renderDonut(id, data, title) {

            let chartData = data.map(item => ({
                name: item.name.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()), // ✅ convert to uppercase
                y: parseFloat(item.total_items_sold)
            }));

            Highcharts.chart(id, {
                chart: {
                    type: 'pie'
                },

                title: {
                    text: title.toUpperCase() // optional
                },

                plotOptions: {
                    pie: {
                        innerSize: '65%',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name} # {point.y}' // ✅ your format
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


        function renderDonutold(id, data, title) {

            let chartData = data.map(item => ({
                name: item.name,
                y: parseFloat(item.amount)
            }));

            Highcharts.chart(id, {
                chart: {
                    type: 'pie'
                },

                title: {
                    text: title
                },

                plotOptions: {
                    pie: {
                        innerSize: '65%', // makes it DONUT
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.percentage:.1f}%'
                        }
                    }
                },

                tooltip: {
                    pointFormat: '<b>{{ __('translation.ngn') }} {point.y}</b>'
                },

                series: [{
                    name: 'Sales',
                    data: chartData
                }]
            });
        }
    </script>

@endsection