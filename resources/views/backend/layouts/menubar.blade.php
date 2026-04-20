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
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content" aria-controls="topnav-menu-content" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="topnav-menu-content">
    <ul class="navbar-nav">

      {{-- Dashboard --}}
      <li class="nav-item">
        <a href="{{ route('dashboard') }}" class="nav-link {{ $isActive('dashboard') ? 'active' : '' }}">
          <i data-feather="home"></i>
          <span>@lang('translation.dashboard')</span>
        </a>
      </li>

      {{-- Staff --}}
      <li class="nav-item">
        <a href="{{ route($role . '.staff.index') }}" class="nav-link {{ $isActive($role . '.staff.*') ? 'active' : '' }}">
          <i data-feather="users"></i>
          <span>@lang('translation.staff')</span>
        </a>
      </li>

      {{-- Categories --}}
      <li class="nav-item">
        <a href="{{ route($role . '.categories.index') }}" class="nav-link {{ $isActive($role . '.categories.*') ? 'active' : '' }}">
          <i data-feather="grid"></i>
          <span>@lang('translation.categories')</span>
        </a>
      </li>

      {{-- Inventory --}}
      <li class="nav-item dropdown {{ $isActive([$role . '.products*', $role . '.inventory*', $role . '.barcode']) ? 'active' : '' }}">
        <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
          <i data-feather="package"></i>
          <span>@lang('translation.inventory')</span>
        </a>

        <div class="dropdown-menu">
          <a href="{{ route($role . '.products') }}" class="dropdown-item">
            @lang('translation.product')
          </a>

          <a href="{{ route($role . '.inventory') }}" class="dropdown-item">
            @lang('translation.stock_management')
          </a>
        </div>
      </li>

      {{-- Billing --}}
      <li class="nav-item">
        <a href="{{ route('billing.index') }}" class="nav-link {{ $isActive('billing.*') ? 'active' : '' }}">
          <i data-feather="credit-card"></i>
          <span>@lang('translation.billing')</span>
        </a>
      </li>

      {{-- Sales --}}
      <li class="nav-item">
        <a href="{{ route('admin.sales.index') }}" class="nav-link {{ $isActive(['admin.sales.*']) ? 'active' : '' }}">
          <i data-feather="shopping-cart"></i>
          <span>@lang('translation.sales_record')</span>
        </a>
      </li>

      {{-- Coupons --}}
      <li class="nav-item">
        <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ $isActive(['admin.coupons.*']) ? 'active' : '' }}">
          <i data-feather="tag"></i>
          <span>@lang('translation.coupons')</span>
        </a>
      </li>

      {{-- Customers --}}
      <li class="nav-item">
        <a href="{{ route('admin.customers.index') }}" class="nav-link {{ $isActive(['admin.customers.*']) ? 'active' : '' }}">
          <i data-feather="user"></i>
          <span>@lang('translation.customers')</span>
        </a>
      </li>

      {{-- Reports --}}
      <li class="nav-item dropdown {{ $isActive(['reports.*']) ? 'active' : '' }}">
        <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
          <i data-feather="bar-chart-2"></i>
          <span>@lang('translation.reports')</span>
        </a>

        <div class="dropdown-menu">
          <a href="{{ route('reports.daily.sales') }}" class="dropdown-item">
            @lang('translation.daily_sales')
          </a>
        </div>
      </li>

      {{-- POS --}}
      <li class="nav-item dropdown {{ $isActive([
  $role . '.pos',
  $role . '.no-barcode',
  $role . '.barcode',
  $role . '.sales-barcode',
  $role . '.return-barcode',
  $role . '.damage-barcode',
  $role . '.deduct-barcode'
]) ? 'active' : '' }}">

        <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
          <i data-feather="monitor"></i>
          <span>@lang('translation.pos')</span>
        </a>

        <div class="dropdown-menu">
          <a href="{{ route($role . '.no-barcode') }}" class="dropdown-item">
            @lang('translation.add_product_without_barcode')
          </a>

          <a href="{{ route($role . '.barcode') }}" class="dropdown-item">
            @lang('translation.add_update_stock')
          </a>

          <a href="{{ route($role . '.sales-barcode') }}" class="dropdown-item">
            @lang('translation.sale_stock')
          </a>

          <a href="{{ route($role . '.return-barcode') }}" class="dropdown-item">
            @lang('translation.return_stock')
          </a>

          <a href="{{ route($role . '.damage-barcode') }}" class="dropdown-item">
            @lang('translation.damage_stock')
          </a>

          <a href="{{ route($role . '.deduct-barcode') }}" class="dropdown-item">
            @lang('translation.deduct_stock')
          </a>
        </div>
      </li>

    </ul>
  </div>
</nav>