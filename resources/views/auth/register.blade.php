<x-guest-layout>
    <!-- Right Section / Register Form -->
    <div class="my-auto page page-h">
        <div class="main-signin-wrapper error-wrapper">
            <div class="main-card-signin d-md-flex wd-100p">
                <!-- Left Side Image -->
               <div class="wd-md-50p login d-none d-md-block page-signin-style p-5 text-white per-40 mt-per-80">
                    <div class="my-auto authentication-pages">
                        <div>
                            <img src="{{ asset('assets/img/brand/logo.png') }}" class=" m-0 mb-4" alt="logo">
                            <p class="mb-5 text-center" >Welcome to {{ config('app.name') }}</p>
                        </div>
                    </div>
                </div>
                <!-- Login Form Slot -->
                <div class="p-5 wd-md-50p per-60">
                    <div class="main-signin-header">
                        <h2>Welcome!</h2>
                        <h4>Please Register Below</h4>
                        <!-- Laravel Register Form -->
                        <form method="POST" action="{{ route('register') }}" autocomplete="off">
                            @csrf                
                            <x-input-field id="name" label="Name" type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('translation.enter_name') }}" autofocus class="form-control" autocomplete="new-name" />                           
                            <x-input-field id="email" label="Email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('translation.enter_email') }}" autofocus class="form-control" autocomplete="new-email" />
                            <x-input-field id="password" label="Password" type="password" name="password" placeholder="{{ __('translation.password') }}"  autocomplete="new_password"/>
                            <x-input-field id="password_confirmation" label="Confirm Password" type="password" name="password_confirmation" placeholder="{{ __('translation.password_confirmation') }}"  autocomplete="password_confirmation"/>
                            <x-primary-button class="ms-3">@lang('translation.create_account')</x-primary-button>
                        </form>
                    </div>

                    <!-- Footer: Login Redirect -->
                    <div class="main-signup-footer mg-t-10">
                        <p>@lang('translation.already_have_an_account') <x-href-input action='login' name="login" label="Sign In"  href="{{ route('login') }}" class="btn- btn-secondary- error changepassword" /></p>
                    </div>
                 <!-- /main-signin-wrapper -->
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

