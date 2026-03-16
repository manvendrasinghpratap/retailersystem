<header id="page-topbar">

    @php
        use Illuminate\Support\Facades\File;

        $avatar = Auth::user()->avatar;
        $customAvatarPath = public_path('uploads/staff/small/' . $avatar);
        $defaultAvatarPath = asset('assets/images/' . $avatar);

        if (!empty($avatar) && $avatar !== 'default.png' && File::exists($customAvatarPath)) {
            $avatarUrl = asset('uploads/staff/small/' . $avatar);
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
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" height="50">
                    </span>
                </a>

                <a href="{{ url('/'.App\Helpers\Settings::getUserRole()) }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" height="50">
                    </span>
                </a>

            </div>

        </div>

        <div class="d-flex">

            <div class="dropdown d-none d-sm-inline-block"></div>

            <div class="dropdown d-inline-block">

                <button
                    type="button"
                    class="btn header-item bg-soft-light border-start border-end"
                    id="page-header-user-dropdown"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >

                    <img
                        class="rounded-circle header-profile-user"
                        src="{{ $avatarUrl }}"
                        alt="Header Avatar"
                    >

                    <span class="d-none d-xl-inline-block ms-1 fw-medium">
                        {{ Auth::user()->name }}

                        <br>

                        <small class="text-muted d-block fw-medium">
                            <i>{{ Auth::user()->designation->name ?? 'Admin' }}</i>
                        </small>
                    </span>

                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>

                </button>

                <div class="dropdown-menu dropdown-menu-end">

                    {{-- 
                    <a class="dropdown-item" href="{{ route('setting.index') }}">
                        <i class="mdi mdi-cog font-size-16 align-middle me-1"></i>
                        @lang('translation.Settings')
                    </a>
                    --}}
                    <a class="dropdown-item" href="{{ route(App\Helpers\Settings::getUserRole().'.profile') }}">
                        <i class="mdi mdi-face-profile font-size-16 align-middle me-1"></i>
                        @lang('translation.profile')
                    </a>                    
                    <a class="dropdown-item" href="{{ route(App\Helpers\Settings::getUserRole().'.change-password') }}"><i class="mdi mdi-lock font-size-16 align-middle me-1"></i>Change Password</a>
                   

                    <a class="dropdown-item" href="{{ url('/') }}">
                        <i class="mdi mdi-web font-size-16 align-middle me-1"></i>
                        @lang('translation.frontend')
                    </a>

                    <div class="dropdown-divider"></div>

                    <a
                        class="dropdown-item"
                        href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    >
                        <i class="bx bx-power-off font-size-16 align-middle me-1"></i>
                        <span key="t-logout">@lang('translation.Logout')</span>
                    </a>

                    <form
                        id="logout-form"
                        action="{{ route('logout') }}"
                        method="POST"
                        style="display:none;"
                    >
                        @csrf
                    </form>

                </div>

            </div>

        </div>

    </div>

</header>

<!-- 🔁 AJAX for Live Notifications -->
@push('scripts')

@endpush