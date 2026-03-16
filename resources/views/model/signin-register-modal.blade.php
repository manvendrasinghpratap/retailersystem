<div class="modal fade" id="signin-modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon-close"></i></span>
                </button>

                <div class="form-box">
                    <div class="form-tab">

                        <ul class="nav nav-pills nav-fill" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="signin-tab" data-toggle="tab" href="#signin" role="tab" aria-controls="signin" aria-selected="true">Sign In</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="register-tab" data-toggle="tab" href="#register" role="tab" aria-controls="register" aria-selected="false">Register</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="tab-content-5">
                            {{-- SIGN IN --}}
                            <div class="tab-pane fade show active" id="signin" role="tabpanel" aria-labelledby="signin-tab">
                                <form id="loginForm" method="POST" action="{{ route('model.login') }}" autocomplete="off">
                                    @csrf
                                    <div id="login-error" class="text-danger mb-2" style="display:none;"></div>
                                    <x-input-field id="email" :labelstatus="false" type="text" name="login" value="{{ old('email') }}" placeholder="{{ __('translation.emailorusername') }}" autofocus class="form-control" autocomplete="email" />
                                    <x-input-field id="password" :labelstatus="false" type="password" name="password" placeholder="{{ __('translation.enter_password') }}" autocomplete="new-password" />
                                    <x-primary-button class="ms-3 btn btn-outline-primary-2">
                                        @lang('translation.login') <i class="icon-long-arrow-right"></i>
                                    </x-primary-button>

                                    <div class="form-footer">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="signin-remember" name="remember">
                                            <label class="custom-control-label" for="signin-remember">Remember Me</label>
                                        </div>
                                        {{-- <a href="{{ route('password.request') }}" class="forgot-link">Forgot Your Password?</a> --}}
                                        <a href="javascript:void(0);" class="forgot-link" id="openForgotPassword" data-toggle="modal" data-target="#forgot-password-modal" data-dismiss="modal">Forgot Your Password?</a>
                                    </div>
                                </form>
                            </div>

                            {{-- REGISTER --}}
                            <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                                <form method="POST" action="{{ route('register.store') }}" autocomplete="off" id="registration" name="registration">
                                    @csrf

                                    <x-input-field :labelstatus="false" id="name" label="Name" type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('translation.enter_name') }}" class="form-control" autocomplete="new-name" />

                                    <x-input-field :labelstatus="false" id="remail" label="Email" type="email" name="remail" value="{{ old('remail') }}" placeholder="{{ __('translation.enter_email') }}" class="form-control lowercase-input" autocomplete="email" />

                                    <x-input-field :labelstatus="false" id="rpassword" label="Password" type="password" name="rpassword" placeholder="{{ __('translation.password') }}" autocomplete="new-password" />

                                    <x-input-field :labelstatus="false" id="rpassword_confirmation" label="Confirm Password" type="password" name="rpassword_confirmation" placeholder="{{ __('translation.password_confirmation') }}" autocomplete="password_confirmation" />

                                    <x-primary-button class="ms-3 btn btn-outline-primary-2">
                                        @lang('translation.create_account') <i class="icon-long-arrow-right"></i>
                                    </x-primary-button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
