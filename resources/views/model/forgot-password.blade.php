<div class="modal fade" id="forgot-password-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon-close"></i></span>
                </button>

                <div class="form-box">
                    <div class="form-tab">

                        <h4 class="text-center mb-3">Forgot Password</h4>
                        <p class="text-center mb-4">
                            Enter your email address and we’ll send you a password reset link.
                        </p>

                        <form method="POST" action="{{ route('password.email.model') }}" autocomplete="off" name="forgot-password-form" id="forgot-password-form">
                            @csrf
                            <div id="forgot-password-form-error" class="text-danger mb-2" style="display:none;"></div>
                             <x-input-field id="email" :labelstatus="false" type="email" name="email" placeholder="{{ __('translation.enter_email') }}" autofocus class="form-control" autocomplete="email" required />
                            <x-primary-button class="btn btn-outline-primary-2 btn-block">Send Reset Link <i class="icon-long-arrow-right"></i></x-primary-button>
                            <div class="text-right mt-1">
                                <a href="#" data-toggle="modal" data-target="#signin-modal" data-dismiss="modal" class="forgot-link">Back to Sign In</a>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
