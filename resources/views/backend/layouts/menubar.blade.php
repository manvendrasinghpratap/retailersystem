<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content"
    aria-controls="topnav-menu-content" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="topnav-menu-content">
    <ul class="navbar-nav">

      <!-- 1️⃣ Dashboard -->
      <li class="nav-item">
        <a class="nav-link dropdown-toggle arrow-none {{ request()->routeIs('dashboard') ? 'active' : '' }}"
          href="{{ route('dashboard') }}" id="topnav-dashboard" role="button">
          <i data-feather="home"></i>
          <span data-key="t-dashboard">@lang('translation.dashboard')</span>
        </a>
      </li>

     @php $managementRoutes = ['admin.staff','admin.menuitems','admin.products']; @endphp
      @if(Auth::user()->user_type_id < 3)
        <li class="nav-item dropdown {{ in_array(Route::currentRouteName(), $managementRoutes) ? 'active' : '' }}">
          <a class="nav-link dropdown-toggle arrow-none" href="javascript:void(0);" id="topnav-management"
            role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i data-feather="settings"></i>
            <span data-key="t-management">@lang('translation.management')</span>
            <div class="arrow-down"></div>
          </a>
          <div class="dropdown-menu" aria-labelledby="topnav-management">
            <a href="{{ route(App\Helpers\Settings::getUserRole().'.staff') }}" class="dropdown-item" data-key="t-staff">@lang('translation.staffmanagement')</a>
            <a href="{{ route(App\Helpers\Settings::getUserRole().'.categories') }}" class="dropdown-item" data-key="t-categories">@lang('translation.categoriesmanagement')</a>
           <a href="{{ route(App\Helpers\Settings::getUserRole().'.products') }}" class="dropdown-item" data-key="t-products">@lang('translation.productmanagement')</a>
          </div>
        </li>
      @endif
      @php $inventoryRoutes = [App\Helpers\Settings::getUserRole().'.inventory',App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getUserRole().'.inventory']; @endphp
      <li class="nav-item dropdown {{ in_array(Route::currentRouteName(), $inventoryRoutes) ? 'active' : '' }}">
          <a class="nav-link dropdown-toggle arrow-none" href="javascript:void(0);" id="topnav-inventory"
            role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i data-feather="settings"></i>
            <span data-key="t-inventory">@lang('translation.inventory')</span>
            <div class="arrow-down"></div>
          </a>
          <div class="dropdown-menu" aria-labelledby="topnav-inventory">
            <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory') }}" class="dropdown-item" data-key="t-in_stock">@lang('translation.in_stock')</a>
            <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(1)) }}" class="dropdown-item" data-key="t-add_stock">@lang('translation.add_stock')</a>
            <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(2)) }}" class="dropdown-item" data-key="t-sale_stock">@lang('translation.sale_stock')</a>
            <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(3)) }}" class="dropdown-item" data-key="t-return_stock">@lang('translation.return_stock')</a>
            <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(4)) }}" class="dropdown-item" data-key="t-damage_stock">@lang('translation.damage_stock')</a>
            <a href="{{ route(App\Helpers\Settings::getUserRole().'.inventory.manage',App\Helpers\Settings::getEncodeCode(5)) }}" class="dropdown-item" data-key="t-deduct_stock">@lang('translation.deduct_stock')</a>
          </div>
      </li>







    </ul>
  </div>
</nav>
