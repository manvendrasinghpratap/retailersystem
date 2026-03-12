function validatedata() {
    let valid = true;

    // clear previous errors
    $('.error-color').html('').hide();
    $('.input-error').removeClass('input-error');

    function checkField(selector, errorClass) {
        const value = $(selector).val();
        if (!value) {
            $(errorClass).html("Required.").addClass("error-color").show();
            $(selector).addClass("input-error");

            if (valid) {
                $(selector).focus();
            }

            valid = false;
        }
    }

    checkField('.suffix', '.error_suffix');
    checkField('.state', '.error_state');
    checkField('.country', '.error_country');
    checkField('.local_government', '.error_local_government');

    const password = $('#password').val();
    const confirmPassword = $('#password_confirmation').val();

    if (password && password !== confirmPassword) {
        $(".error_password")
            .html("* Passwords do not match.")
            .addClass("error-color")
            .show();

        $("#password, #password_confirmation").addClass("input-error");

        if (valid) {
            $("#password").focus();
        }

        $('#password').val('');
        $('#password_confirmation').val('');

        valid = false;
    }

    return valid;
}


// Auto generate username
$('.setusername').on('keyup', function () {
    const firstname = $('#first_name').val()
        .trim()
        .replace(/\s+/g, '')
        .toLowerCase();

    const cell_phone = $('#cell_phone').val()
        .trim()
        .replace(/\s+/g, '');

    const username = (firstname + '.' + cell_phone)
        .replace(/\s+/g, '')
        .toLowerCase();

    $('.username').val(username);
});