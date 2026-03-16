@php
$redirectUrl = auth()->check() && auth()->user()->user_type_id == 1 ? 'administrator.' : 'admin.';
@endphp
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Left: Brand / Logo -->
        <div class="navbar-left width20per">
            <a class="navbar-brand fw-bold text-primary fs-25px" href="{{ url('/') }}">
                <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name', 'Retailer	System') }}" class="img-fluid logowidth">
                {{ config('app.name', 'Retailer	System') }}
            </a>
        </div>

        <!-- Right: Menu Items -->
        <div class="navbar-right">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonials">Customers</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>                  
                    @auth
                        <!-- Dropdown submenu for logged-in users -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn btn-primary text-white ms-lg-3" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name ?? 'Account' }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" style="width: 100%;">
                                <li><a class="dropdown-item" href="{{ route($redirectUrl.'dashboard') }}">@lang('translation.dashboard')</a></li>
                                <li><a class="dropdown-item" href="{{ route($redirectUrl.'profile') }}">@lang('translation.myaccount')</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item" type="submit">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="btn btn-primary ms-lg-3" href="#signin-modal" data-bs-toggle="modal">Login</a></li>
                    @endauth
                </ul>
            </div>
        </div>

    </div>
</nav>
