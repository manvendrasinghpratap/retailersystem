<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content" aria-controls="topnav-menu-content" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">

            <!-- 1️⃣ Dashboard -->
            <li class="nav-item">
                <a class="nav-link dropdown-toggle arrow-none {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" id="topnav-dashboard" role="button">
                    <i data-feather="home"></i>
                    <span data-key="t-dashboard">@lang('translation.dashboard')</span>
                </a>
            </li>
            <!---- orders -->
            <li class="nav-item">
                <a class="nav-link dropdown-toggle arrow-none {{ request()->routeIs('billing.index') ? 'active' : '' }}" href="{{ route('billing.index') }}" id="topnav-orders" role="button">
                    <i data-feather="order"></i>
                    <span data-key="t-order">@lang('translation.billing')</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle arrow-none {{ request()->routeIs('admin.sales.index') || request()->routeIs('admin.sales.show') ? 'active' : '' }}" href="{{ route('admin.sales.index') }}" id="topnav-sales" role="button">
                    <i data-feather="order"></i>
                    <span data-key="t-sales">@lang('translation.my_sales')</span>
                </a>
            </li>
        </ul>
    </div>
</nav>