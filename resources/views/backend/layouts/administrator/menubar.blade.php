<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    @php($ik = 10)
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            {{-- {{request()->route()->getName()}} --}}
            @if (Auth::user()->user_type ==1) {{-- SuperAdmin --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle arrow-none 
                    {{(
                    request()->route()->getName() === 'administrator.dashboard' || 
                    request()->route()->getName() === 'subscription.add' || 
                    request()->route()->getName() === 'subscription.edit' 
                    ) ? 'active':''}}" 
                    
                    href="{{ route('subscription') }}" id="topnav-order"
                        role="button">
                        <i data-feather="gift"></i><span data-key="t-order">@lang('translation.subscription')</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle arrow-none
                    {{(
                    request()->route()->getName() === 'account.add' || 
                    request()->route()->getName() === 'subscribe' || 
                    request()->route()->getName() === 'account.edit' 
                    ) ? 'active':''}}
                    " href="{{ route('accounts') }}" id="topnav-order"
                        role="button">
                        <i data-feather="book"></i><span data-key="t-order">@lang('translation.accounts')</span>
                    </a>
                </li>
        @endif
        </ul>
    </div>
</nav>
