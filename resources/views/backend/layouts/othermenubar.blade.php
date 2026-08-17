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
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content" aria-controls="topnav-menu-content" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">

            {{-- 1. DASHBOARD --}}
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ $isActive('dashboard') ? 'active' : '' }}">
                    <i data-feather="home"></i>
                    <span>@lang('translation.dashboard')</span>
                </a>
            </li>

            {{-- 2. SYSTEM & MASTER SETUP --}}
            <li class="nav-item dropdown {{ $isActive([$role . '.payment-types.*', $role . '.credit-durations.*', $role . '.account-settings.*', $role . '.designations.*', $role . '.modules.*']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i data-feather="settings"></i><span>@lang('translation.master')</span>
                </a>
                <div class="dropdown-menu">
                    <h6 class="dropdown-header">@lang('translation.system_configurations')</h6>
                    <a href="{{ route($role . '.account-settings.index') }}" class="dropdown-item"><i data-feather="sliders" class="menu-icon-sm"></i>@lang('translation.account_settings')</a>
                    <a href="{{ route($role . '.credit-durations.index') }}" class="dropdown-item"><i data-feather="calendar" class="menu-icon-sm"></i>@lang('translation.credit_duration')</a>
                    {{--
                    @if($role === 'admin')
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">@lang('translation.admin_controls')</h6>
                    <a href="{{ route($role . '.payment-types.index') }}" class="dropdown-item"><i data-feather="credit-card" class="menu-icon-sm"></i>@lang('translation.payment_types')</a>
                    <a href="{{ route($role . '.designations.index') }}" class="dropdown-item"><i data-feather="briefcase" class="menu-icon-sm"></i>@lang('translation.designations')</a>
                    <a href="{{ route($role . '.modules.index') }}" class="dropdown-item"><i data-feather="box" class="menu-icon-sm"></i>@lang('translation.modules')</a>
                    @endif
                    --}}
                </div>
            </li>

            {{-- 3. POINT OF SALE (POS) --}}
            {{--
            <li class="nav-item dropdown {{ $isActive([$role . '.sales-barcode', $role . '.terminal.*']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i data-feather="monitor"></i><span>@lang('translation.pos_terminal')</span>
                </a>
                <div class="dropdown-menu">
                    <h6 class="dropdown-header">@lang('translation.checkout')</h6>
                    <a href="{{ route($role . '.sales-barcode') }}" class="dropdown-item"><i data-feather="shopping-bag" class="menu-icon-sm"></i>@lang('translation.register_pos')</a>
                </div>
            </li>
            ---}}

            {{-- 4. PRODUCT CATALOG --}}
            <li class="nav-item dropdown {{ $isActive([$role . '.products*', $role . '.categories*', $role . '.master_items.*']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i data-feather="grid"></i><span>@lang('translation.catalog')</span>
                </a>
                <div class="dropdown-menu">
                    <h6 class="dropdown-header">@lang('translation.product_management')</h6>
                    <a href="{{ route($role . '.master_items.index') }}" class="dropdown-item"><i data-feather="layers" class="menu-icon-sm"></i>@lang('translation.master_items')</a>
                    <a href="{{ route($role . '.products') }}" class="dropdown-item"><i data-feather="box" class="menu-icon-sm"></i>@lang('translation.products')</a>
                    <a href="{{ route($role . '.categories.index') }}" class="dropdown-item"><i data-feather="list" class="menu-icon-sm"></i>@lang('translation.categories')</a>
                </div>
            </li>

            {{-- 5. STOCK & INVENTORY OPERATIONS --}}
            <li class="nav-item dropdown {{ $isActive([$role . '.inventory*', $role . '.warehouses.*', $role . '.requisitions.*', $role . '.damage-barcode', $role . '.deduct-barcode', $role . '.return-barcode']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i data-feather="package"></i><span>@lang('translation.inventory')</span>
                </a>
                <div class="dropdown-menu">
                    <h6 class="dropdown-header">@lang('translation.stock_control')</h6>
                    <a href="{{ route($role . '.inventory') }}" class="dropdown-item"><i data-feather="database" class="menu-icon-sm"></i>@lang('translation.stock_levels')</a>
                    <a href="{{ route($role . '.warehouses.index') }}" class="dropdown-item"><i data-feather="archive" class="menu-icon-sm"></i>@lang('translation.warehouses')</a>

                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">@lang('translation.stock_movements')</h6>
                    <a href="{{ route($role . '.requisitions.index') }}" class="dropdown-item"><i data-feather="repeat" class="menu-icon-sm"></i>@lang('translation.requisitions')</a>
                    <a href="{{ route($role . '.requisitions.pending.posting') }}" class="dropdown-item"><i data-feather="plus-square" class="menu-icon-sm"></i>@lang('translation.add_update_stock')</a>

                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">@lang('translation.adjustments')</h6>
                    <a href="{{ route($role . '.damage-barcode') }}" class="dropdown-item"><i data-feather="alert-triangle" class="menu-icon-sm"></i>@lang('translation.damage_stock')</a>
                    <a href="{{ route($role . '.deduct-barcode') }}" class="dropdown-item"><i data-feather="minus-circle" class="menu-icon-sm"></i>@lang('translation.deduct_stock')</a>
                </div>
            </li>

            {{-- 6. PURCHASING & PROCUREMENT --}}
            <li class="nav-item dropdown {{ $isActive([$role . '.purchases.*', $role . '.stock_returns.*', $role . '.vendors.*']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i data-feather="truck"></i><span>@lang('translation.purchasing')</span>
                </a>
                <div class="dropdown-menu">
                    <h6 class="dropdown-header">@lang('translation.vendors')</h6>
                    <a href="{{ route($role . '.vendors.index') }}" class="dropdown-item"><i data-feather="users" class="menu-icon-sm"></i>@lang('translation.suppliers_list')</a>

                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">@lang('translation.orders_and_returns')</h6>
                    <a href="{{ route($role . '.purchases.index') }}" class="dropdown-item"><i data-feather="file-text" class="menu-icon-sm"></i>@lang('translation.purchase_orders')</a>
                    <a href="{{ route($role . '.stock_returns.index') }}" class="dropdown-item"><i data-feather="corner-up-left" class="menu-icon-sm"></i>@lang('translation.purchase_returns')</a>
                </div>
            </li>

            {{-- 7. SALES & CUSTOMERS --}}
            <li class="nav-item dropdown {{ $isActive([$role . '.sales.*', $role . '.customers.*', $role . '.coupons.*']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i data-feather="shopping-cart"></i><span>@lang('translation.sales_crm')</span>
                </a>
                <div class="dropdown-menu">
                    <h6 class="dropdown-header">@lang('translation.sales')</h6>
                    <a href="{{ route($role . '.sales.index') }}" class="dropdown-item"><i data-feather="bar-chart" class="menu-icon-sm"></i>@lang('translation.sales_records')</a>

                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">@lang('translation.crm')</h6>
                    <a href="{{ route($role . '.customers.index') }}" class="dropdown-item"><i data-feather="user-check" class="menu-icon-sm"></i>@lang('translation.customers')</a>
                    <a href="{{ route($role . '.coupons.index') }}" class="dropdown-item"><i data-feather="tag" class="menu-icon-sm"></i>@lang('translation.coupons_promotions')</a>
                </div>
            </li>

            {{-- 8. STAFF & HR --}}
            <li class="nav-item dropdown {{ $isActive([$role . '.staff.*', 'attendance.*']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i data-feather="users"></i><span>@lang('translation.staff')</span>
                </a>
                <div class="dropdown-menu">
                    <h6 class="dropdown-header">@lang('translation.team_management')</h6>
                    <a href="{{ route($role . '.staff.index') }}" class="dropdown-item"><i data-feather="user" class="menu-icon-sm"></i>@lang('translation.employee_list')</a>
                    <a href="{{ route('attendance.report') }}" class="dropdown-item"><i data-feather="clock" class="menu-icon-sm"></i>@lang('translation.attendance')</a>
                </div>
            </li>

            {{-- 9. REPORTS & ANALYTICS --}}
            <li class="nav-item dropdown {{ $isActive(['reports.*', $role . '.requisitions.pending.posting.history.report*']) ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i data-feather="pie-chart"></i><span>@lang('translation.reports')</span>
                </a>
                <div class="dropdown-menu">
                    <h6 class="dropdown-header">@lang('translation.analytics')</h6>
                    <a href="{{ route('reports.daily.sales') }}" class="dropdown-item"><i data-feather="activity" class="menu-icon-sm"></i>@lang('translation.daily_sales_report')</a>
                    <a href="{{ route($role . '.requisitions.pending.posting.history.report') }}" class="dropdown-item"><i data-feather="rotate-ccw" class="menu-icon-sm"></i>@lang('translation.posting_history_report')</a>
                </div>
            </li>

            {{-- 10. BILLING --}}
            <li class="nav-item">
                <a href="{{ route('billing.index') }}" class="nav-link {{ $isActive('billing.*') ? 'active' : '' }}">
                    <span class="me-1">@lang('translation.naira')</span>
                    <span>@lang('translation.billing')</span>
                </a>
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