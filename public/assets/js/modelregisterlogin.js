// modelpopup.js

$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Safe, customizable alert helper (falls back to window.alert when SweetAlert2 isn't loaded)
    
    // Reset forms and validation when modal opens
    $('#signin-modal').on('shown.bs.modal', function () {
        let form = this.querySelector('form');
        form.reset();
        $(form).find('.is-invalid').removeClass('is-invalid');
        $(form).find('.error').remove();

        if ($('#registration').data('validator')) {
            $('#registration').validate().resetForm();
        }
        $('#login-email').trigger('focus');
    });

    // Convert inputs to lowercase
    $('input.lowercase-input').on('input', function () {
        this.value = this.value.toLowerCase();
    });

    // Registration form validation
    $('#registration').validate({
        rules: {
            name: { required: true, minlength: 3 },
            remail: { 
                required: true, 
                email: true,
                remote: {
                    url: "/check-email", // example route
                    type: "post",
                    data: {
                        email: function() { return $('[name="remail"]').val(); },
                    }
                }
            },
            rpassword: { required: true, minlength: 8 },
            rpassword_confirmation: { required: true, equalTo: '[name="rpassword"]' }
        },
        messages: {
            name: { required: "Name is required", minlength: "Minimum 3 characters" },
            remail: { required: "Email required", email: "Invalid email", remote: "Email already exists" },
            rpassword: { required: "Password required", minlength: "Minimum 8 characters" },
            rpassword_confirmation: { required: "Confirm password", equalTo: "Passwords do not match" }
        },
        errorElement: 'div',
        errorClass: 'text-danger mt-0',
        errorPlacement: function(error, element) { error.insertAfter(element); },
        highlight: function(element) { $(element).addClass('is-invalid'); },
        unhighlight: function(element) { $(element).removeClass('is-invalid'); },
        onkeyup: function(element) { this.element(element); },
        onfocusout: function(element) { this.element(element); }
    });

    // Instant password match validation
    $('[name="rpassword_confirmation"]').on('input', function() {
        $('#registration').validate().element($(this));
    });

    // Login AJAX
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        $('#login-error').hide().text('');
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                window.location.href = response.redirect;
            },
            error: function (xhr) {
                let message = xhr.responseJSON?.message || 'Invalid email or password';
                $('#login-error').text(message).show();
            }
        });
    });
    // =======================================
// HARD STOP: Disable Bootstrap FocusTrap
// =======================================
$(document).off('focusin.bs.modal');
$('.modal').modal({ focus: false });

    $('.close').on('click', function () {
        $('#signin-modal').modal('hide');
        //$('#forgot-password-modal').modal('hide');
    });

    $('#openForgotPassword').on('click', function (e) {
        e.preventDefault();
        $('#forgot-password-form-error').hide().text('');
        $('[name="email"]').val('');
        const $signin = $('#signin-modal');
        const $forgot = $('#forgot-password-modal');

        // Remove focus before switching
        if (document.activeElement instanceof HTMLElement) {
            document.activeElement.blur();
        }

        $signin.one('hidden.bs.modal', function () {
            $forgot.modal({
                backdrop: 'static',
                keyboard: false,
                focus: false // 🔥 prevents FocusTrap loop
            });
        });

        $signin.modal('hide');
    });



    $('a[data-toggle="tab"], a[data-bs-toggle="tab"],a[data-toggle="modal"]').on('shown.bs.tab', function (e) {
        const targetTab = $(e.target).attr('href'); // activated tab pane

        // Reset any form inside the newly opened tab
        const $form = $(targetTab).find('form');

        if ($form.length) {
            // Reset form fields
            $form[0].reset();

            // Remove validation UI
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.error, .text-danger').remove();

            // Reset jQuery Validation state (if attached)
            if ($form.data('validator')) {
                $form.validate().resetForm();
            }           

        }
    });

    // Forgot password AJAX
    $('[name="forgot-password-form"]').on('submit', function (e) {
        e.preventDefault();
        $('#forgot-password-form-error').hide().text('');
        // alert($(this).attr('action')); return;
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(), // send full form (includes _token and email)
            success: function(res) {
                $('#forgot-password-form-error').hide().text(res.message).show();
                showAlert('Success', res.message, 'success');
                $('.close').trigger('click');
            },
            error: function(xhr) {
                // validation errors
                if (xhr.status === 422 && xhr.responseJSON) {
                    const json = xhr.responseJSON;
                    const msg = json.message || (json.errors ? Object.values(json.errors).flat()[0] : json.message);
                    $('#forgot-password-form-error').hide().text(msg).show();
                    showAlert('Failed', msg, 'error');
                    $('.close').trigger('click');
                } else {
                showAlert('Error', 'Unexpected error. Check console/server logs.', 'error');
                }
            },
            complete: function () {
                $form.find('button[type="submit"]').prop('disabled', false);    
        }
        });
    });


    


});
