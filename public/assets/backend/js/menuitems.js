	function validatedata(){
            var valid = true;
            var accountstatus = $(".accountstatus").val();
            if (accountstatus == "") {
                $(".error_accountstatus").html("* Required.").addClass("error-color").show();
                $(".accountstatus").addClass("input-error").focus();
                valid = false;
            }
			var menucategory = $(".menucategory").val();
            if (menucategory == "") {
                $(".error_menu_type_id").html("* Required.").addClass("error-color").show();
                $(".menucategory").addClass("input-error").focus();
                valid = false;
            }
	        return valid;
        }
			/*$(document).ready(function () {
				$('#menuitemform').on('submit', function (e) {
					let isValid = true;
					const imageInput = $('input[name="image"]')[0]; // raw DOM element
					const errorContainer = $('.error_image');
					const file = imageInput.files[0];
					const is_image_exists = $('#is_image_exists').val();
					const maxSize = 2 * 1024 * 1024; // 2MB in bytes

					if (is_image_exists == 0) {
						if (!file) {
							errorContainer.html("* Required.").addClass("error-color").show();
							isValid = false;
						} else if (!file.type.startsWith('image/')) {
							errorContainer.html("* Only valid image files are allowed (JPG, PNG, etc).").addClass("error-color").show();
							isValid = false;
						} else if (file.size > maxSize) {
							errorContainer.html("* Maximum image size is 2MB.").addClass("error-color").show();
							isValid = false;
						} else {
							errorContainer.hide();
						}

						if (!isValid) {
							e.preventDefault(); // Stop form submission
						}
					}
				});
			});*/
			
			$(document).ready(function () {
				$('#menuitemform').on('submit', function (e) {
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


		 $(document).ready(function () {
                $("#addBtn").on("click", function () {
                    var inputRow = $(".inputRow").first().clone();
                    inputRow.find("input").val("");
                    $("#inputContainer").append(inputRow);
                    inputRow.find(".deleteBtn").on("click", function () {
                        var inputRows = $(".inputRow");
                        if (inputRows.length > 1) {
                            $(this).closest(".inputRow").remove();
                        }
                    });
                });
                $(document).on("click", ".deleteBtn", function () {
                    var inputRows = $(".inputRow");
                    if (inputRows.length > 1) {
                        $(this).closest(".inputRow").remove();
                    }
                });
            });
			
			