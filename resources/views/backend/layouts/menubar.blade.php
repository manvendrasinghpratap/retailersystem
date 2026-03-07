<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content" aria-controls="topnav-menu-content" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>

    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">

            <li class="nav-item">
                <a href="{{ route('dashboard') }}" id="topnav-dashboard" role="button" class="nav-link dropdown-toggle arrow-none {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i data-feather="home"></i><span data-key="t-dashboard">@lang('translation.dashboard')</span></a>
            </li>

            {{-- <li class="nav-item">
                <a href="{{ route('order.index') }}" id="topnav-orders" role="button" class="nav-link dropdown-toggle arrow-none {{ request()->routeIs('order.index') ? 'active' : '' }}"><i data-feather="shopping-bag"></i><span data-key="t-orders">@lang('translation.orders')</span></a>
            </li> --}}

            {{-- @if(Auth::user()->user_type < 3 && Auth::user()->designation_id != 12 && config('app.env') === 'local')
                <li class="nav-item">
                    <a href="{{ route('customer.orders') }}" id="topnav-customer-orders" role="button" class="nav-link dropdown-toggle arrow-none {{ request()->routeIs('customer.orders') ? 'active' : '' }}"><i data-feather="clipboard"></i><span data-key="t-customer_orders">@lang('translation.customer') @lang('translation.orders')</span></a>
                </li>
            @endif --}}

            {{-- @php $salesRoutes = ['sales.index','sales.stockloading','sales.unsoldstockreturn']; @endphp
            @if(Auth::user()->user_type < 3 && Auth::user()->designation_id != 12)
                <li class="nav-item dropdown {{ in_array(Route::currentRouteName(), $salesRoutes) ? 'active' : '' }}">
                    <a href="javascript:void(0);" id="topnav-sales" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="nav-link dropdown-toggle arrow-none"><i data-feather="dollar-sign"></i><span data-key="t-sales">@lang('translation.sales')</span><div class="arrow-down"></div></a>
                    <div class="dropdown-menu" aria-labelledby="topnav-sales">
                        <a href="{{ route('sales.index') }}" class="dropdown-item">@lang('translation.sales') @lang('translation.listing')</a>
                        <a href="{{ route('sales.stockloading') }}" class="dropdown-item">@lang('translation.stock_loading')</a>
                        <a href="{{ route('sales.unsoldstockreturn') }}" class="dropdown-item">@lang('translation.unsold_stock_return')</a>
                    </div>
                </li>
            @endif

            @php $stockRoutes = ['stock.index','adjustmentstock.index']; @endphp
            @if(Auth::user()->user_type < 3 && Auth::user()->designation_id != 12)
                <li class="nav-item dropdown {{ in_array(Route::currentRouteName(), $stockRoutes) ? 'active' : '' }}">
                    <a href="javascript:void(0);" id="topnav-stock" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="nav-link dropdown-toggle arrow-none"><i data-feather="package"></i><span data-key="t-stock">@lang('translation.stock')</span><div class="arrow-down"></div></a>
                    <div class="dropdown-menu" aria-labelledby="topnav-stock">
                        <a href="{{ route('stock.index') }}" class="dropdown-item">@lang('translation.add_stock')</a>
                        <a href="{{ route('adjustmentstock.index') }}" class="dropdown-item">@lang('translation.add_adjustment_stock')</a>
                    </div>
                </li>
            @endif --}}

            {{-- <li class="nav-item">
                <a href="{{ route('customer.index') }}" id="topnav-customers" role="button" class="nav-link dropdown-toggle arrow-none {{ request()->routeIs('customer.index') ? 'active' : '' }}"><i data-feather="users"></i><span data-key="t-customers">@lang('translation.customers')</span></a>
            </li> --}}

            @php $managementRoutes = ['staff','menuitems','categories.index','bulk-offers.index']; @endphp
            @if(Auth::user()->user_type < 3 && Auth::user()->designation_id != 12)
                <li class="nav-item dropdown {{ in_array(Route::currentRouteName(), $managementRoutes) ? 'active' : '' }}">
                    <a href="javascript:void(0);" id="topnav-management" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="nav-link dropdown-toggle arrow-none"><i data-feather="settings"></i><span data-key="t-management">@lang('translation.management')</span><div class="arrow-down"></div></a>
                    <div class="dropdown-menu" aria-labelledby="topnav-management">
                        <a href="{{ route('categories.index') }}" class="dropdown-item">@lang('translation.categoriesmanagement')</a>
                    </div>
                </li>
            @endif

            {{-- @php $reportRoutes = ['report.sales','inventory.index','report.productSalesReport','inventory.summary']; @endphp
            @if(Auth::user()->user_type < 3 && Auth::user()->designation_id != 12)
                <li class="nav-item dropdown {{ in_array(Route::currentRouteName(), $reportRoutes) ? 'active' : '' }}">
                    <a href="javascript:void(0);" id="topnav-report" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="nav-link dropdown-toggle arrow-none"><i data-feather="bar-chart-2"></i><span data-key="t-report">@lang('translation.report')</span><div class="arrow-down"></div></a>
                    <div class="dropdown-menu" aria-labelledby="topnav-report">
                        <a href="{{ route('report.sales') }}" class="dropdown-item">@lang('translation.dailysales')</a>
                        <a href="{{ route('inventory.summary') }}" class="dropdown-item">@lang('translation.transaction')</a>
                        <a href="{{ route('inventory.index') }}" class="dropdown-item">@lang('translation.daily_stock_report')</a>
                        <a href="{{ route('report.productSalesReport') }}" class="dropdown-item">@lang('translation.product_sales_report')</a>
                    </div>
                </li>
            @endif --}}

        </ul>
    </div>
</nav>
