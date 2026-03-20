<x-guest-layout>
    <!-- main-signin-wrapper -->
    <div class="my-auto page page-h">
        <div class="main-signin-wrapper error-wrapper">
            <div class="main-card-signin d-md-flex wd-100p">
                <!-- Left Side Image -->
               <div class="wd-md-50p login d-none d-md-block page-signin-style p-5 text-white per-40 mt-per-80">
                    <div class="my-auto authentication-pages">
                        <div>
                            <img src="{{ asset('assets/img/brand/logo.png') }}" class=" m-0 mb-4" alt="logo @lang('translation.webname')">
                            <p class="mb-1 text-center">Welcome to @lang('translation.webname')</p>
                            <p class="mb-2 text-center">
                                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Login Form Slot -->

                <div class="p-5 wd-md-50p per-60">
                    <div class="main-signin-header">

                        <h2>@lang('translation.reset_password')</h2>
                         <p class="mb-0 text-center">
                                {{ __('') }}
                            </p>
                        <x-auth-session-status class="mb-4" :status="session('status')" />
                        <form method="POST" action="{{ route('password.store') }}">
                                @csrf

                                <!-- Password Reset Token -->
                                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                                <!-- Email Address -->
                                <div>
                                    <x-input-field id="email" label="Email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('translation.enter_email') }}" autofocus class="form-control" autocomplete="new-email" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <!-- Password -->
                                <div class="mt-2">
                                    <x-input-field id="password" label="Password" type="password" name="password" placeholder="{{ __('translation.enter_password') }}" autocomplete="new-password"/>
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <!-- Confirm Password -->
                                <div class="mt-2">
                                    <x-input-field id="password_confirmation" label="Confirm Password" type="password" name="password_confirmation" placeholder="{{ __('translation.enter_password') }}" autocomplete="new-password"/>
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <x-primary-button>
                                        {{ __('Reset Password') }}
                                    </x-primary-button>
                                </div>
                        </form>
                    </div>
                    <div class="main-signin-footer mt-3 mg-t-5">
                        <p>@lang('translation.already_have_an_account') <x-href-input action='login' name="login" label="Sign In"  href="{{ route('login') }}" class="btn- btn-secondary- error changepassword" /></p>
                        <p>@lang('translation.Dont_have_an_account') <a href="{{ route('register') }}">@lang('translation.create_account')</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>





<x-guest-layout>
    
</x-guest-layout>
