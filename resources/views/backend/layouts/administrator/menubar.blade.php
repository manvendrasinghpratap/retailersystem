<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            @if (Auth::user()->user_type_id ==1) {{-- SuperAdmin --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle arrow-none 
                    {{(
                    request()->route()->getName() === 'administrator.dashboard' || 
                    request()->route()->getName() === 'administrator.subscription.add' || 
                    request()->route()->getName() === 'administrator.subscription.edit' || 
                    request()->route()->getName() === 'administrator.subscription' 
                    ) ? 'active':''}}" 
                    
                    href="{{ route('administrator.subscription') }}" id="topnav-order"
                        role="button">
                        <i data-feather="gift"></i><span data-key="t-order"> @lang('translation.subscription') </span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle arrow-none
                    {{(
                    request()->route()->getName() === 'administrator.account.add' || 
                    request()->route()->getName() === 'administrator.subscribe' || 
                    request()->route()->getName() === 'administrator.account.edit' ||
                    request()->route()->getName() === 'administrator.accounts'
                    ) ? 'active':''}}
                    " href="{{ route('administrator.accounts') }}" id="topnav-order"
                        role="button">
                        <i data-feather="book"></i><span data-key="t-order">@lang('translation.accounts')</span>
                    </a>
                </li>
        @endif
        </ul>
    </div>
</nav>
