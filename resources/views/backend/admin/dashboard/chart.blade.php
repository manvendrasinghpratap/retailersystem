@extends('backend.layouts.master-horizontal')

@section('title')
    {{ Config::get('constants.admin') || $breadcrumb['title'] ?? __('translation.dashboard') }}
@endsection
<style>
    .naira-icon {
        font-size: 17px !important;
        font-weight: bold;
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 12px;
        border-radius: 10px;
    }
</style>
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
                        <small class="text-muted">{{ __('translation.date') }}: {{ request('date') ? App\Helpers\Settings::formatDate(request('date'), \Config::get('constants.dateformat.slashdmyonly')) : App\Helpers\Settings::getFormattedDate(date(\Config::get('constants.dateformat.slashdmyonly')))  }}</small>
                    </div>
                    <form method="GET" action="{{ route('graph') }}" class="row g-2 align-items-center">
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

            </div>
        </div>

    </div>

    <!-- Styles -->
    <style>
        .gradient1 {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .gradient2 {
            background: linear-gradient(135deg, #43cea2, #185a9d);
        }

        .gradient3 {
            background: linear-gradient(135deg, #f7971e, #ffd200);
        }

        .gradient4 {
            background: linear-gradient(135deg, #ff6a00, #ee0979);
        }

        .icon {
            font-size: 28px;
        }

        .card {
            border-radius: 15px;
        }

        .card:hover {
            transform: translateY(-4px);
            transition: 0.3s;
        }
    </style>

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
                    pointFormat: '<b>₹ {point.y}</b>'
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

        });
    </script>

@endsection