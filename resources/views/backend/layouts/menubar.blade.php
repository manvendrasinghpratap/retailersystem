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
      <!---- Staff -->
      <li class="nav-item">
        <a class="nav-link dropdown-toggle arrow-none  {{ request()->routeIs(App\Helpers\Settings::getUserRole() . '.staff.*') ? 'active' : '' }}" href="{{ route(App\Helpers\Settings::getUserRole() . '.staff.index') }}" id="topnav-staff" role="button">
          <i data-feather="home"></i>
          <span data-key="t-staff">@lang('translation.staff')</span>
        </a>
      </li>
      <!---- Categories -->
      <li class="nav-item">
        <a class="nav-link dropdown-toggle arrow-none  {{ request()->routeIs(App\Helpers\Settings::getUserRole() . '.categories.*') ? 'active' : '' }}" href="{{ route(App\Helpers\Settings::getUserRole() . '.categories.index') }}" id="topnav-categories" role="button">
          <i data-feather="home"></i>
          <span data-key="t-categories">@lang('translation.categories')</span>
        </a>
      </li>

      @php $inventoryRoutes = [App\Helpers\Settings::getUserRole() . '.barcode', App\Helpers\Settings::getUserRole() . '.products', App\Helpers\Settings::getUserRole() . '.inventory']; @endphp
      <li class="nav-item dropdown {{ in_array(Route::currentRouteName(), $inventoryRoutes) ? 'active' : '' }}">
        <a class="nav-link dropdown-toggle arrow-none" href="javascript:void(0);" id="topnav-inventory" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i data-feather="settings"></i>
          <span data-key="t-inventory">@lang('translation.inventory')</span>
          <div class="arrow-down"></div>
        </a>
        <div class="dropdown-menu" aria-labelledby="topnav-inventory">
          <a href="{{ route(App\Helpers\Settings::getUserRole() . '.products') }}" class="dropdown-item" data-key="t-products">@lang('translation.product')</a>
          <a href="{{ route(App\Helpers\Settings::getUserRole() . '.inventory') }}" class="dropdown-item" data-key="t-sale_stock">@lang('translation.stock_management')</a>
          <!-- <a href="{{ route(App\Helpers\Settings::getUserRole() . '.inventory') }}" class="dropdown-item fifth-nav-item" data-key="t-in_stock">@lang('translation.in_stock')</a>
          <a href="{{ route(App\Helpers\Settings::getUserRole() . '.barcode') }}" class="dropdown-item fourth-nav-item" data-key="t-add_stock">@lang('translation.add_update_stock')</a> -->
        </div>
      </li>




      @php $posRoutes = [App\Helpers\Settings::getUserRole() . '.pos']; @endphp


      <li class="nav-item dropdown {{ in_array(Route::currentRouteName(), $posRoutes) ? 'active' : '' }}">
        <a class="nav-link dropdown-toggle arrow-none" href="javascript:void(0);" id="topnav-pos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i data-feather="settings"></i>
          <span data-key="t-pos">@lang('translation.pos')</span>
          <div class="arrow-down"></div>
        </a>
        <div class="dropdown-menu" aria-labelledby="topnav-inventory">
          <!-- <a href="{{ route(App\Helpers\Settings::getUserRole() . '.inventory') }}" class="dropdown-item fifth-nav-item" data-key="t-in_stock">@lang('translation.in_stock')</a> -->
          <a href="{{ route(App\Helpers\Settings::getUserRole() . '.no-barcode') }}" class="dropdown-item fourth-nav-item" data-key="t-add_stock">@lang('translation.add_product_without_barcode')</a>
          <a href="{{ route(App\Helpers\Settings::getUserRole() . '.barcode') }}" class="dropdown-item fourth-nav-item" data-key="t-add_stock">@lang('translation.add_update_stock')</a>
          <a href="{{ route(App\Helpers\Settings::getUserRole() . '.sales-barcode') }}" class="dropdown-item fourth-nav-item" data-key="t-add_stock">@lang('translation.sale_stock')</a>
          <a href="{{ route(App\Helpers\Settings::getUserRole() . '.return-barcode') }}" class="dropdown-item first-nav-item" data-key="t-return_stock">@lang('translation.return_stock')</a>
          <a href="{{ route(App\Helpers\Settings::getUserRole() . '.damage-barcode') }}" class="dropdown-item second-nav-item" data-key="t-damage_stock">@lang('translation.damage_stock')</a>
          <a href="{{ route(App\Helpers\Settings::getUserRole() . '.deduct-barcode') }}" class="dropdown-item third-nav-item" data-key="t-deduct_stock">@lang('translation.deduct_stock')</a>
        </div>
      </li>







    </ul>
  </div>
</nav>