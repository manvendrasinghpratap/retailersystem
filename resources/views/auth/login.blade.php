<x-guest-layout>
    <!-- main-signin-wrapper -->
    <div class="my-auto page page-h">
        <div class="main-signin-wrapper error-wrapper">
            <div class="main-card-signin d-md-flex wd-100p">

                <!-- Left Side -->
                <div class="wd-md-50p login d-none d-md-block page-signin-style p-5 text-white per-40 mt-per-80">
                    <div class="my-auto authentication-pages">

                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ asset('assets/images/logo.png') }}" 
                                 class="width30per me-2" 
                                 alt="logo @lang('translation.webname')">

                            <h5 class="mb-0">@lang('translation.webname')</h5>
                        </div>

                        <p class="mb-1">@lang('translation.welcome_to') @lang('translation.webname')</p>

                    </div>
                </div>

                <!-- Login Form -->
                <div class="p-5 wd-md-50p per-60">
                    <div class="main-signin-header">

                        <h2>@lang('translation.welcomeback')</h2>
                        <h4>@lang('translation.please_sign_in_to_continue')</h4>

                        <form method="POST" action="{{ route('login') }}" autocomplete="off">
                            @csrf

                            <x-input-field 
                                id="email"
                                label="Email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="{{ __('translation.enter_email') }}"
                                autofocus
                                class="form-control"
                                autocomplete="new-email"
                            />

                            <x-input-field 
                                id="password"
                                label="Password"
                                type="password"
                                name="password"
                                placeholder="{{ __('translation.enter_password') }}"
                                autocomplete="new-password"
                            />

                            <x-primary-button class="mt-3">
                                @lang('translation.login')
                            </x-primary-button>

                        </form>

                    </div>

                    <div class="main-signin-footer mt-3 mg-t-5">
                        <p>
                            <a href="{{ route('password.request') }}">
                                @lang('translation.forgot_password')
                            </a>
                        </p>

                        <p>
                            @lang('translation.Dont_have_an_account')
                            <a href="{{ route('register') }}">
                                @lang('translation.create_account')
                            </a>
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-guest-layout>