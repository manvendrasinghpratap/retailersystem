



<!----- Login Popup Begin ----->
<div class="modal fade" id="login-sign-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered max-width-per-50" role="document">
    <div class="modal-content">

      <div class="modal-header border-0 pb-4">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body px-3 pt-0">
        <div class="row">
          <div class="col-12 authfy-panel-right">
            <div class="authfy-login">

              <!-- LOGIN PANEL -->
              <div class="authfy-panel panel-login text-center active">
                <div class="authfy-heading">
                  <h3 class="auth-title">Login to your account</h3>
                  <p>
                    Don’t have an account?
                    <a class="lnk-toggler" data-panel=".panel-signup" href="javascript:void(0)">Sign Up Free!</a>
                  </p>
                </div>

                <form id="loginForm" class="loginform" method="POST" autocomplete="off">
                  @csrf
                  <div class="form-group">
                    <input type="text" class="form-control" name="username" placeholder="Username">
                  </div>

                  <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Password">
                  </div>

                  <div class="form-group text-right">
                    <a href="javascript:void(0)" class="lnk-toggler forgot-link" data-panel=".panel-forgot-phone">Forgot password?</a>
                  </div>

                  <div class="form-group">
                    <input type="submit" value="Login" class="btn btn-default btn-lg btn-block" title="Login">
                  </div>
                </form>
              </div>
              <!-- ./LOGIN -->

              <!-- SIGNUP PANEL -->
              <div class="authfy-panel panel-signup text-center" style="display:none;">
                <div class="authfy-heading">
                  <h3 class="auth-title">New Customers</h3>
                  <p>Create an account below</p>
                  <p class="error-text">Your username will be your primary phone number.</p>
                </div>

                <form id="signupForm" method="POST" autocomplete="off">
                  @csrf
                  <div class="row">
                    <div class="form-group col-md-6">
                      <input type="text" class="form-control" name="first_name" placeholder="First name">
                    </div>
                    <div class="form-group col-md-6">
                      <input type="text" class="form-control" name="last_name" placeholder="Last name">
                    </div>

                    <div class="form-group col-md-6">
                      <select class="form-control" name="gender">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>

                    <div class="form-group col-md-6">
                      <input type="email" class="form-control" name="email" placeholder="Email address">
                    </div>

                    <div class="form-group col-md-6">
                      <input type="text" class="form-control onlyinteger phonenumber" name="mobile_no" id="mobile_no"
                        placeholder="Primary Phone No.">
                    </div>

                    <div class="form-group col-md-6">
                      <input type="text" class="form-control" name="business_name"
                        placeholder="Business or Personal Name">
                    </div>

                    <div class="form-group col-md-6">
                      <input type="text" class="form-control onlyinteger phonenumber" name="username" id="username"
                        placeholder="Username" readonly>
                    </div>

                    <div class="form-group col-md-6">
                      <input type="password" class="form-control" name="registrationpassword" placeholder="Password">
                    </div>

                    <div class="form-group col-md-6">
                      <input type="password" class="form-control" name="registrationpassword_confirmation"
                        placeholder="Confirm Password">
                    </div>
                  </div>

                  <div class="form-group">
                    <button class="btn btn-lg btn-blue-sml btn-block" type="submit">Create Account</button>
                  </div>

                  <a class="lnk-toggler" data-panel=".panel-login" href="javascript:void(0)">Already have an account?</a>
                </form>
              </div>
              <!-- ./SIGNUP -->

              <!-- STEP 1: FORGOT PASSWORD - SEND CODE -->
              <div class="authfy-panel panel-forgot-phone text-center" style="display:none;">
                <div class="authfy-heading">
                  <h3 class="auth-title">Forgot Password</h3>
                  <p>Enter your registered phone number to receive a verification code.</p>
                </div>

                <form id="forgotPhoneForm" method="POST" autocomplete="off">
                  @csrf
                  <div class="form-group">
                    <input type="text" class="form-control onlyinteger" name="phone" id="forgot_phone"
                      placeholder="Enter Phone Number">
                  </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn-lg btn-blue-sml btn-block">Send Code</button>
                  </div>

                  <a class="lnk-toggler" data-panel=".panel-login" href="javascript:void(0)">Back to Login</a>
                </form>
              </div>

              <!-- STEP 2: VERIFY CODE -->
              <div class="authfy-panel panel-verify-code text-center" style="display:none;">
                <div class="authfy-heading">
                  <h3 class="auth-title">Verify Code</h3>
                  <p>Enter the 6-digit code sent to your phone.</p>
                </div>

                <form id="verifyCodeForm" method="POST" autocomplete="off">
                  @csrf
                  <div class="form-group">
                    <input type="hidden" name="verifyPhone" id="verifyPhone"> <!-- Store the phone used earlier -->
                    <input type="text" class="form-control onlyinteger" name="verification_code"
                      placeholder="Enter Verification Code">
                  </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn-lg btn-blue-sml btn-block">Verify Code</button>
                  </div>

                  <a class="lnk-toggler" data-panel=".panel-forgot-phone" href="javascript:void(0)">Resend Code</a>
                </form>
              </div>

              <!-- STEP 3: RESET PASSWORD -->
              <div class="authfy-panel panel-reset-password text-center" style="display:none;">
                <div class="authfy-heading">
                  <h3 class="auth-title">Reset Password</h3>
                  <p>Enter your new password below.</p>
                </div>

                <form id="resetPasswordForm" method="POST" autocomplete="off">
                  @csrf
                  <div class="form-group">
                    <input type="password" class="form-control" name="new_password" placeholder="New Password">
                  </div>

                  <div class="form-group">
                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password">
                  </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn-lg btn-blue-sml btn-block">Reset Password</button>
                  </div>

                  <a class="lnk-toggler" data-panel=".panel-login" href="javascript:void(0)">Back to Login</a>
                </form>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-------- Login Popup End ------->

<!-- jQuery Script for Panel Toggle + Forgot Password Flow -->
<script>
$(document).ready(function() {
  // Toggle between panels
  $(".lnk-toggler").on("click", function() {
    const target = $(this).data("panel");
    $('#forgot_phone').val('');
    $(".authfy-panel").hide();
    $(target).show();
  });

  // Step 1: Send reset code
  $("#forgotPhoneForm").on("submit", function(e) {
    e.preventDefault();

    let phone = $("#forgot_phone").val().trim();

    if (!phone) {
        Swal.fire({
            icon: "warning",
            title: "Phone Required",
            text: "Please enter your phone number",
        });
        return;
    }

    $.ajax({
        url: '{{ route("user.send-reset-code") }}',
        type: "POST",
        data: {
            phone: phone,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            Swal.fire({
                icon: "success",
                title: "Code Sent",
                text: "Verification code sent to your phone.",
            }).then(() => {
                $(".authfy-panel").hide();
                $(".panel-verify-code").show();
                $("input[name='verifyPhone']").val(phone);
            });
        },
        error: function(xhr) {
            let message = "Error sending code. Please try again.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: "error",
                title: "Failed",
                text: message,
            });
        }
    });
});

  // $("#forgotPhoneForm").on("submit", function(e) {
  //   e.preventDefault();
  //   let phone = $("#forgot_phone").val().trim();

  //   if (!phone) {
  //     alert("Please enter your phone number");
  //     return;
  //   }

  //   $.ajax({
  //     url: '{{ route("user.send-reset-code") }}',
  //     type: "POST",
  //     data: {
  //       phone: phone,
  //       _token: "{{ csrf_token() }}"
  //     },
  //     success: function(response) {
  //       alert("Verification code sent to your phone");
  //       $(".authfy-panel").hide();
  //       $(".panel-verify-code").show();
  //       $("input[name='verifyPhone']").val(phone).trim();
  //     },
  //     error: function(xhr) {
  //       alert("Error sending code. Please try again.");
  //     }
  //   });
  // });

  // Step 2: Verify code
  // $("#verifyCodeForm").on("submit", function(e) {
  //   e.preventDefault();
  //   let code  = $("input[name='verification_code']").val().trim();
  //   let phone = $("input[name='verifyPhone']").val().trim();

  //   if (code.length !== 6) {
  //     alert("Please enter a valid 6-digit code");
  //     return;
  //   }

  //   $.ajax({
  //     url: '{{ route("user.send-reset-code") }}',
  //     url: "/verify-reset-code",
  //     type: "POST",
  //     data: {
  //       code: code,
  //       phone:phone,
  //       _token: "{{ csrf_token() }}"
  //     },
  //     success: function(response) {
  //       alert("Code verified! Please reset your password.");
  //       $(".authfy-panel").hide();
  //       $(".panel-reset-password").show();
  //     },
  //     error: function(xhr) {
  //       alert("Invalid or expired code.");
  //     }
  //   });
  // });

  $("#verifyCodeForm").on("submit", function(e) {
    e.preventDefault();

    let code  = $("input[name='verification_code']").val().trim();
    let phone = $("input[name='verifyPhone']").val().trim();

    if (code.length !== 6) {
        Swal.fire({
            icon: "warning",
            title: "Invalid Code",
            text: "Please enter a valid 6-digit code",
        });
        return;
    }

    $.ajax({
        url: "/verify-reset-code",
        type: "POST",
        data: {
            code: code,
            phone: phone,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            Swal.fire({
                icon: "success",
                title: "Code Verified!",
                text: "Please reset your password.",
            }).then(() => {
                $(".authfy-panel").hide();
                $(".panel-reset-password").show();
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: "error",
                title: "Invalid or Expired",
                text: "Invalid or expired code.",
            });
        }
    });
  });


  // Step 3: Reset password
  $("#resetPasswordForm").on("submit", function(e) {
    e.preventDefault();

    let password = $("input[name='new_password']").val().trim();
    let confirm = $("input[name='confirm_password']").val().trim();
     let phone = $("input[name='verifyPhone']").val().trim();

    if (password === "" || confirm === "") {
        Swal.fire({
            icon: "warning",
            title: "Missing Fields",
            text: "Please fill out both password fields",
        });
        return;
    }

    if (password !== confirm) {
        Swal.fire({
            icon: "error",
            title: "Mismatch",
            text: "Passwords do not match",
        });
        return;
    }

    $.ajax({
        url: "/reset-password",
        type: "POST",
        data: {
            password: password,
            phone:phone,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: "Password successfully reset!",
            }).then(() => {
                $(".authfy-panel").hide();
                $(".panel-login").show();
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Error resetting password. Try again.",
            });
        }
    });
});

  // $("#resetPasswordForm").on("submit", function(e) {
  //   e.preventDefault();
  //   let password = $("input[name='new_password']").val().trim();
  //   let confirm = $("input[name='confirm_password']").val().trim();

  //   if (password === "" || confirm === "") {
  //     alert("Please fill out both password fields");
  //     return;
  //   }

  //   if (password !== confirm) {
  //     alert("Passwords do not match");
  //     return;
  //   }

  //   $.ajax({
  //     url: "/reset-password",
  //     type: "POST",
  //     data: {
  //       password: password,
  //       _token: "{{ csrf_token() }}"
  //     },
  //     success: function(response) {
  //       alert("Password successfully reset!");
  //       $(".authfy-panel").hide();
  //       $(".panel-login").show();
  //     },
  //     error: function(xhr) {
  //       alert("Error resetting password. Try again.");
  //     }
  //   });
  // });
});
</script>



<style>
	.form-control {
    padding: 0.25rem 1.1rem !important;
}
.btn-blue-sml {
    padding: 0.35rem 0 !important;
				/* color: #fff; */
}
.login-link-btn-small{
				padding: 0.35rem 0 !important;
				/* color: #fff; */
}
.login-link-btn-small:hover{
	color: #fff;
}
.authfy-panel {
  display: none;
  transition: all 0.3s ease;
}
.authfy-panel.active {
  display: block;
}
.is-invalid {
  border-color: #dc3545 !important;
}
.invalid-feedback {
  color: #dc3545;
  font-size: 13px;
  margin-top: 4px;
}
</style>

<script>
$(document).ready(function() {

		// $(document).on('click', '.lnk-toggler', function(e) {
  //   e.preventDefault();

  //   const targetPanel = $(this).data('panel'); // Get target panel selector
  //   const currentPanel = $(this).closest('.authfy-panel');

  //   // Hide current panel
  //   currentPanel.hide().removeClass('active');

  //   // Reset form and validation inside the current panel
  //   const form = currentPanel.find('form')[0];
  //   if (form) form.reset();
  //   currentPanel.find('.is-invalid').removeClass('is-invalid');
  //   currentPanel.find('.invalid-feedback').hide();

  //   // Show the target panel
  //   $(targetPanel).fadeIn(200).addClass('active');
  // });

  // Optional: reset when any close button is clicked (if you have a close icon)
  $(document).on('click', '.close', function(e) {
    const modal = $(this).closest('.modal');
    const forms = modal.find('form');
    forms.each(function() {
      this.reset();
      $(this).find('.is-invalid').removeClass('is-invalid');
      $(this).find('.invalid-feedback').hide();
    });
  });

  /** -------- Switch between Login and Signup -------- **/
  $(document).on('click', '.lnk-toggler', function(e) {
    e.preventDefault();
    const targetPanel = $(this).data('panel');
    $('.authfy-panel').removeClass('active').hide();
    $(targetPanel).addClass('active').fadeIn(200);
  });

  /** -------- Login Form Submit -------- **/
  $('#loginForm').on('submit', function(e) {
    e.preventDefault();

    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').hide().text('');

    const username = $('input[name="username"]', this).val().trim();
    const password = $('input[name="password"]', this).val().trim();
    let hasError = false;

    if (!username) {
      $('input[name="username"]').addClass('is-invalid')
        .next('.invalid-feedback').show().text('Username is required.');
      hasError = true;
    }
    if (!password) {
      $('input[name="password"]').addClass('is-invalid')
        .next('.invalid-feedback').show().text('Password is required.');
      hasError = true;
    }
    if (hasError) return;

    const $form = $(this);
    const formData = $form.serialize();
    const csrfToken = $('input[name="_token"]').val();
				return;
    $.ajax({
      url: '/login',
      method: 'POST',
      data: formData,
      headers: {
        'X-CSRF-TOKEN': csrfToken
      },
      success: function() {
        window.location.reload();
      },
      error: function(xhr) {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
          const errors = xhr.responseJSON.errors;
          if (errors.username) {
            $('input[name="username"]').addClass('is-invalid')
              .next('.invalid-feedback').show().text(errors.username[0]);
          }
          if (errors.password) {
            $('input[name="password"]').addClass('is-invalid')
              .next('.invalid-feedback').show().text(errors.password[0]);
          }
        } else {
          const msg = xhr.responseJSON?.message || 'Login failed. Please check your credentials.';
          $('input[name="password"]').addClass('is-invalid')
            .next('.invalid-feedback').show().text(msg);
        }
      }
    });
  });


  /** -------- Signup Form Validation -------- **/
    $('#signupForm').on('submit', function(e) 
    {
          e.preventDefault(); // ✅ Only once!
          const $form = $(this);
          $form.find('.form-control').removeClass('is-invalid');
          $form.find('.invalid-feedback').hide().text('');
          // Reset validation
          $('#signupForm .form-control').removeClass('is-invalid');
          $('#signupForm .invalid-feedback').hide().text('');

          let hasError = false;

          const first_name = $('input[name="first_name"]').val().trim();
          const last_name = $('input[name="last_name"]').val().trim();
          const gender = $('select[name="gender"]').val().trim();
          const mobile_no = $('input[name="mobile_no"]').val().trim();
          const password = $('input[name="registrationpassword"]').val().trim();
          const password_confirmation = $('input[name="registrationpassword_confirmation"]').val().trim();

          // ---- Field Validations ----
          // basic required checks (shortened for brevity)
          if (!first_name) { $form.find('input[name="first_name"]').addClass('is-invalid').closest('.form-group').find('.invalid-feedback-').show().text('First name is required.'); hasError = true; }
          if (!last_name)  { $form.find('input[name="last_name"]').addClass('is-invalid').closest('.form-group').find('.invalid-feedback-').show().text('Last name is required.'); hasError = true; }
          if (!gender)     { $form.find('select[name="gender"]').addClass('is-invalid').closest('.form-group').find('.invalid-feedback-').show().text('Please select your gender.'); hasError = true; }
          if (!mobile_no)   { $form.find('input[name="mobile_no"]').addClass('is-invalid').closest('.form-group').find('.invalid-feedback-').show().text('Phone number is required.'); hasError = true; }

        // Password presence checks
        if (!password) {
            $form.find('input[name="registrationpassword"]').addClass('is-invalid').closest('.form-group').find('.invalid-feedback-').show().text('Password is required.');
            hasError = true;
        }
        if (!password_confirmation) {
            $form.find('input[name="registrationpassword_confirmation"]').addClass('is-invalid').closest('.form-group').find('.invalid-feedback-').show().text('Please confirm your password.');
            hasError = true;
        }

  // Password match check (robust)
        if (password && password_confirmation && password !== password_confirmation) {
            // Use closest .form-group to display message reliably
            $form.find('input[name="registrationpassword_confirmation"]')
                .addClass('is-invalid')
                .closest('.form-group')
                .find('.invalid-feedback')
                .show()
                .text('Passwords do not match.');

                  // optionally also highlight password field
                  $form.find('input[name="registrationpassword"]').addClass('is-invalid');

                  hasError = true;
                  console.log('Validation: passwords do not match'); // debug
        }

        if (hasError) {
            // focus the first invalid field for UX
            $form.find('.is-invalid').first().focus();
            return;
        }
        if (hasError) return; // Stop if any validation failed
        // ---- Perform AJAX Signup ----
        const formData = $(this).serialize();
        const csrfToken = $('meta[name="csrf-token"]').attr('content'); // ✅ Get token safely

        $.ajax({
                url: '{{ route("user.register") }}',
                type: 'POST',
                data: formData,
                headers: { 'X-CSRF-TOKEN': csrfToken }, // ✅ Include CSRF header
                success: function(response) {
                        Swal.fire('Success', response.message || 'Account created successfully!', 'success')
                                .then(() => {
                                        location.reload();
                                });
                },
                error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                // Show first validation error message
                                for (const key in errors) {
                                        Swal.fire('Error', errors[key][0], 'error');
                                        break;
                                }
                        } else {
                                Swal.fire('Error', xhr.responseJSON?.message || 'An error occurred.', 'error');
                        }
                }
        });
		});

});

  
document.addEventListener('DOMContentLoaded', function() {
  const mobileInput = document.getElementById('mobile_no');
  const usernameInput = document.getElementById('username');

  mobileInput.addEventListener('input', function() {
    // Keep only digits
    let cleaned = mobileInput.value.replace(/\D/g, '');

    // Limit to 11 digits max
    if (cleaned.length > 11) {
      cleaned = cleaned.substring(0, 11);
    }

    // Set cleaned value back to input
    mobileInput.value = cleaned;

    // Copy to username
    usernameInput.value = cleaned;
  });
});
</script>
