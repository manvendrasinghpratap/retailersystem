<header id="page-topbar">
    @php
    use Illuminate\Support\Facades\File;
    $avatar = Auth::user()->avatar;
    $customAvatarPath = public_path('uploads/staff/small/' . $avatar);
    $defaultAvatarPath = URL::asset('assets/images/' . $avatar);

    if (!empty($avatar) && $avatar !== 'default.png' && File::exists($customAvatarPath)) {
    $avatarUrl = URL::asset('uploads/staff/small/' . $avatar);
    } else {
    $avatarUrl = $defaultAvatarPath;
    }
    @endphp
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ url('/'.App\Helpers\Settings::getUserRole()) }}" class="">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('assets/images/logo.png') }}" alt="" height="30"><span class="d-none d-xl-inline-block ms-1 fw-medium" style="color: white; font-size:15px;"> {{ \Config::get('constants.websitename')}}</span>
                    </span>
                </a>

                <a href="{{ url('/'.App\Helpers\Settings::getUserRole()) }}" class="logo logo-light ">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('assets/images/logo.png') }}" alt="" height="30">
                    </span>
                </a>
            </div>
        </div>
        {{-- Attendance Button --}}
        <div class="dropdown d-inline-block me-3">
            <button type="button" class="btn attendance-btn d-flex align-items-center px-3 py-2 text-white bg-soft-dark " data-bs-toggle="modal" data-bs-target="#attendanceModal">
                <span class="attendance-icon me-2">
                    <i class="mdi mdi-clock-check-outline"></i>
                </span>
                <span class="fw-semibold d-none- d-xl-inline-block">
                    {{ __('translation.attendance') }}
                </span>
            </button>
        </div>
        <div class="d-flex">
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="{{ $avatarUrl ?? asset('images/default.png') }}" alt="User Avatar">
                    <span class="d-none d-xl-inline-block ms-1 fw-medium">
                        {{ Auth::user()->name }}
                    </span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item {{ request()->routeIs(App\Helpers\Settings::getUserRole().'.profile') ? 'active' : '' }}" href="{{ route(App\Helpers\Settings::getUserRole().'.profile') }}"><i class="mdi mdi-face-profile font-size-16 align-middle me-1"></i>@lang('translation.profile')</a>
                    <a class="dropdown-item {{ request()->routeIs(App\Helpers\Settings::getUserRole().'.change-password') ? 'active' : '' }}" href="{{ route(App\Helpers\Settings::getUserRole().'.change-password') }}"><i class="mdi mdi-lock font-size-16 align-middle me-1"></i>@lang('translation.change_password')</a>
                    <a class="dropdown-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ url('/') }}"><i class="mdi mdi-web font-size-16 align-middle me-1"></i>@lang('translation.frontend')</a>
                    <a class="dropdown-item {{ request()->routeIs('attendance.report') ? 'active' : '' }}" href="{{ route('attendance.report') }}"><i class="mdi mdi-clock-check-outline font-size-16 align-middle me-1"></i>@lang('translation.attendance_report')</a>
                    <div class="dropdown-divider"></div>

                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">

                        <i class="bx bx-power-off font-size-16 align-middle me-1"></i>
                        <span>@lang('translation.Logout')</span>
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                        @csrf
                    </form>

                </div>
            </div>
        </div>
    </div>
</header>