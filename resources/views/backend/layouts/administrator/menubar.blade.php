<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            @if (Auth::user()->user_type_id == 1) {{-- SuperAdmin --}}

                        <!-- 1️⃣ Dashboard -->
                        <li class="nav-item">
                            <a class="nav-link dropdown-toggle arrow-none {{ request()->routeIs('administrator.dashboard') ? 'active' : '' }}"
                                href="{{ route(App\Helpers\Settings::getUserRole() . '.dashboard') }}" id="topnav-dashboard"
                                role="button">
                                <i data-feather="home"></i>
                                <span data-key="t-dashboard">@lang('translation.dashboard')</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none {{
                    (request()->route()->getName() === App\Helpers\Settings::getUserRole() . '.subscription.add' ||
                        request()->route()->getName() === App\Helpers\Settings::getUserRole() . '.subscription.edit' ||
                        request()->route()->getName() === App\Helpers\Settings::getUserRole() . '.subscription'
                    ) ? 'active' : ''}}" href="{{ route(App\Helpers\Settings::getUserRole() . '.subscription') }}"
                                id="topnav-order" role="button">
                                <i data-feather="gift"></i><span data-key="t-order"> @lang('translation.subscription') </span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    {{(
                    request()->route()->getName() === App\Helpers\Settings::getUserRole() . '.account.add' ||
                    request()->route()->getName() === App\Helpers\Settings::getUserRole() . '.subscribe' ||
                    request()->route()->getName() === App\Helpers\Settings::getUserRole() . '.account.edit' ||
                    request()->route()->getName() === App\Helpers\Settings::getUserRole() . '.accounts'
                ) ? 'active' : ''}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            "
                                href="{{ route(App\Helpers\Settings::getUserRole() . '.accounts') }}" id="topnav-order"
                                role="button">
                                <i data-feather="book"></i><span data-key="t-order">@lang('translation.accounts')</span>
                            </a>
                        </li>
            @endif
        </ul>
    </div>
</nav>