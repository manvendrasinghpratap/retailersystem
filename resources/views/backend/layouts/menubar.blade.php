@php
    use App\Helpers\Settings;

    $role = Settings::getUserRole();
    $currentRoute = Route::currentRouteName();

    $isActive = function ($routes) use ($currentRoute) {
        foreach ((array) $routes as $route) {
            if (Str::is($route, $currentRoute)) {
                return true;
            }
        }
        return false;
    };
@endphp



<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    {{-- MOBILE TOGGLE --}}
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            {{-- ===================================================== --}}
            {{-- DASHBOARD --}}
            {{-- ===================================================== --}}
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ $isActive('dashboard') ? 'active' : '' }}">
                    <i data-feather="home"></i>
                    <span>@lang('translation.dashboard')</span>
                </a>
            </li>

            {{-- POS --}}
            {{-- ===================================================== --}}
            <li class="nav-item dropdown {{ $isActive(['admin.requisitions.pending.posting', $role . '.sales-barcode', $role . '.return-barcode', $role . '.damage-barcode', $role . '.deduct-barcode']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i data-feather="monitor"></i><span>@lang('translation.pos')</span></a>
                <div class="dropdown-menu">
                    {{-- STOCK OPERATIONS --}}
                    <h6 class="dropdown-header">@lang('translation.stock_operations') </h6>
                    <a href="{{ route('admin.requisitions.pending.posting') }}" class="dropdown-item"><i data-feather="plus-square" class="menu-icon-sm"></i>@lang('translation.add_update_stock')</a>
                    <!-- <a href="{{ route($role.'.sales-barcode') }}" class="dropdown-item"><i data-feather="shopping-cart" class="menu-icon-sm"></i>@lang('translation.sale_stock')</a> -->
                    <div class="dropdown-divider"></div>
                    {{-- STOCK ADJUSTMENTS --}}
                    <h6 class="dropdown-header">@lang('translation.stock_adjustments') </h6>
                    <a href="{{ route($role . '.return-barcode') }}" class="dropdown-item"><i data-feather="rotate-ccw" class="menu-icon-sm"></i>@lang('translation.return_stock')</a>
                    <a href="{{ route($role . '.damage-barcode') }}" class="dropdown-item"><i data-feather="alert-triangle" class="menu-icon-sm"></i>@lang('translation.damage_stock')</a>
                    <a href="{{ route($role . '.deduct-barcode') }}" class="dropdown-item"><i data-feather="minus-circle" class="menu-icon-sm"></i>@lang('translation.deduct_stock')</a>
                </div>
            </li>
            {{-- ===================================================== --}}

            {{-- SALES --}}
            {{-- ===================================================== --}}
            <li class="nav-item dropdown {{ $isActive(['admin.sales.*', 'admin.coupons.*']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i data-feather="shopping-cart"></i><span>@lang('translation.sales')</span></a>
                <div class="dropdown-menu">
                    {{-- SALES --}}
                    <h6 class="dropdown-header">@lang('translation.sales')</h6>
                    <a href="{{ route('admin.sales.index') }}" class="dropdown-item"><i data-feather="bar-chart" class="menu-icon-sm"></i>@lang('translation.sales_record')</a>
                    <div class="dropdown-divider"></div>
                    {{-- MARKETING --}}
                    <h6 class="dropdown-header">@lang('translation.marketing')</h6>
                    <a href="{{ route('admin.coupons.index') }}" class="dropdown-item"><i data-feather="tag" class="menu-icon-sm"></i>@lang('translation.coupons')</a>
                </div>
            </li>

            {{-- ===================================================== --}}
            {{-- INVENTORY --}}
            {{-- ===================================================== --}}
            <li class="nav-item dropdown {{ $isActive([
    $role . '.products*',
    $role . '.categories*',
    'admin.master_items.*',
    'admin.warehouses.*',
    'admin.purchases.*',
    'admin.stock_returns.*',
    'admin.requisitions.*',
    $role . '.inventory*'
]) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i data-feather="package"></i>
                    <span>@lang('translation.inventory')</span>
                </a>
                <div class="dropdown-menu">
                    {{-- PRODUCT SETUP --}}
                    <h6 class="dropdown-header">@lang('translation.product_setup') </h6>
                    <a href="{{ route($role . '.products') }}" class="dropdown-item"><i data-feather="box" class="menu-icon-sm"></i>@lang('translation.products')</a>
                    <a href="{{ route($role . '.categories.index') }}" class="dropdown-item"><i data-feather="grid" class="menu-icon-sm"></i>@lang('translation.categories')</a>
                    <a href="{{ route('admin.master_items.index') }}" class="dropdown-item"><i data-feather="layers" class="menu-icon-sm"></i>@lang('translation.master_items')</a>
                    <div class="dropdown-divider"></div>
                    {{-- STORAGE --}}
                    <h6 class="dropdown-header">@lang('translation.storage') </h6>
                    <a href="{{ route('admin.warehouses.index') }}" class="dropdown-item"><i data-feather="archive" class="menu-icon-sm"></i>@lang('translation.warehouses')</a>
                    <a href="{{ route($role . '.inventory') }}" class="dropdown-item"><i data-feather="database" class="menu-icon-sm"></i>@lang('translation.stock')</a>
                    <div class="dropdown-divider"></div>
                    {{-- PURCHASING --}}
                    <h6 class="dropdown-header">@lang('translation.purchasing') </h6>
                    <a href="{{ route('admin.purchases.index') }}" class="dropdown-item"><i data-feather="shopping-bag" class="menu-icon-sm"></i>@lang('translation.purchases')</a>
                    <a href="{{ route('admin.stock_returns.index') }}" class="dropdown-item"><i data-feather="corner-up-left" class="menu-icon-sm"></i>@lang('translation.purchase_returns')</a>
                    {{-- INVENTORY REQUESTS --}}
                    <h6 class="dropdown-header">@lang('translation.inventory_requests')</h6>
                    <a href="{{ route('admin.requisitions.index') }}" class="dropdown-item"><i data-feather="repeat" class="menu-icon-sm"></i>@lang('translation.requisitions')</a>
                </div>
            </li>
            {{-- ===================================================== --}}


            {{-- ===================================================== --}}
            {{-- PEOPLE --}}
            {{-- ===================================================== --}}
            <li class="nav-item dropdown {{ $isActive([$role . '.staff.*', 'admin.customers.*', 'admin.vendors.*']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i data-feather="users"></i><span>@lang('translation.people')</span></a>
                <div class="dropdown-menu">
                    {{-- INTERNAL --}}
                    <h6 class="dropdown-header">@lang('translation.internal_users')</h6>
                    <a href="{{ route($role . '.staff.index') }}" class="dropdown-item"><i data-feather="user-check" class="menu-icon-sm"></i>@lang('translation.staff')</a>
                    <div class="dropdown-divider"></div>
                    {{-- EXTERNAL --}}
                    <h6 class="dropdown-header">@lang('translation.business_partners')</h6>
                    <a href="{{ route('admin.customers.index') }}" class="dropdown-item"><i data-feather="users" class="menu-icon-sm"></i>@lang('translation.customers')</a>
                    <a href="{{ route('admin.vendors.index') }}" class="dropdown-item"><i data-feather="truck" class="menu-icon-sm"></i>@lang('translation.vendors')</a>
                </div>
            </li>
            {{-- ===================================================== --}}
            {{-- REPORTS --}}
            {{-- ===================================================== --}}
            <li class="nav-item dropdown {{ $isActive(['reports.*']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i data-feather="bar-chart-2"></i><span>@lang('translation.reports')</span></a>
                <div class="dropdown-menu">
                    {{-- SALES REPORTS --}}
                    <h6 class="dropdown-header">@lang('translation.sales_reports')</h6>
                    <a href="{{ route('reports.daily.sales') }}" class="dropdown-item"><i data-feather="activity" class="menu-icon-sm"></i>@lang('translation.daily_sales')</a>
                    <div class="dropdown-divider"></div>
                    {{-- STAFF REPORTS --}}
                    <h6 class="dropdown-header">@lang('translation.staff_reports')</h6>
                    <a href="{{ route('attendance.report') }}" class="dropdown-item"><i data-feather="clock" class="menu-icon-sm"></i>@lang('translation.attendance_report')</a>
                </div>
            </li>
            {{-- ===================================================== --}}
            {{-- BILLING --}}
            {{-- ===================================================== --}}
            <li class="nav-item">
                <a href="{{ route('billing.index') }}" class="nav-link {{ $isActive('billing.*') ? 'active' : '' }}"><i data-feather="credit-card"></i><span>@lang('translation.billing')</span></a>
            </li>

            {{-- ===================================================== --}}
            {{-- Master Setup --}}
            {{-- ===================================================== --}}
            <li class="nav-item dropdown {{ $isActive(['admin.payment-types.*']) ? 'active' : '' }}" style="display: none;">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i data-feather="settings"></i><span>@lang('translation.master')</span></a>
                <div class="dropdown-menu">
                    {{-- Master Entries --}}
                    <h6 class="dropdown-header">@lang('translation.master_entries')</h6>
                    <a href="{{ route('admin.payment-types.index') }}" class="dropdown-item"><i data-feather="credit-card" class="menu-icon-sm"></i>@lang('translation.payment_types')</a>
                    <a href="{{ route('admin.credit-durations.index') }}" class="dropdown-item"><i data-feather="calendar" class="menu-icon-sm"></i>@lang('translation.credit_duration')</a>
                    <a href="{{ route('admin.account-settings.index') }}" class="dropdown-item"><i data-feather="settings" class="menu-icon-sm"></i>@lang('translation.account_settings')</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>