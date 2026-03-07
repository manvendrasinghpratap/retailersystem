	function validatedata(){
            var valid = true;
            var menucatstatus = $(".accountstatus").val();
             $(".error_status").html("").removeClass("error-color").hide();
             $(".accountstatus").removeClass("input-error");
            if (menucatstatus == "") {
                $(".error_status").html("* Required.").addClass("error-color").show();
                $(".accountstatus").addClass("input-error").focus();
                valid = false;
            }
	        return valid;
        }
		$(document).ready(function () {
				$('#productcatform').on('submit', function (e) {
					const is_image_exists = $('#is_image_exists').val();
					let isValid = true;

					if (is_image_exists == 0) {
						isValid = validateImageInput('input[name="image"]', '.error_image');
					}

					if (!isValid) {
						e.preventDefault();
					}
				});
			});