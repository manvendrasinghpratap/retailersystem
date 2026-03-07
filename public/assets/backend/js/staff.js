	$(document).ready(function() {
								$(".designation_id").select2({
										placeholder: "Select Designation",
										allowClear: true
								});
								$(".staff_suffix").select2({
										placeholder: "Select",
										allowClear: true
								});
								$(".local_government").select2({
										placeholder: "Select Local Government",
										allowClear: true
								});
								$(".country_of_origin").select2({
										placeholder: "Select Country of Origin",
										allowClear: true
								});
								$(".state_of_origin").select2({
										placeholder: "Select State of Origin",
										allowClear: true
								});
								$(".emergency_relationship").select2({
										placeholder: "Select Relationship",
										allowClear: true
								});
								$(".staffstatus").select2({
										placeholder: "Select Status",
										allowClear: true
								});

								$(".alphanumeric").on("keypress", function(event) {
									let regex = /^[A-Za-z ]+$/;
									let key = String.fromCharCode(event.which);
									
									if (!regex.test(key)) {
										event.preventDefault(); // Prevent invalid characters
									}
								});

								


								$('.staffname, .email, .password_confirmation, .password').bind("cut copy paste", function(e) {
									e.preventDefault();
								});

								$('.staffname').keyup(function() {
									var firstname = $('#first_name').val().trim().replace(/\s+/g, '').toLowerCase();
									var lastname = $('#last_name').val().trim().replace(/\s+/g, '').toLowerCase();
									var name = firstname +'.'+ lastname;
									var newusername = name.trim().replace(/\s+/g, '').toLowerCase();
									$('.username').val(newusername);
								});

	   
	});
	
	
	function validatedata(){
		var valid = true;
		var designationid = $("#designation_id").val();
		if (designationid == "") {
			$(".error_designation_id").html("Required.").addClass("error-color").show();
			$(".designation_id").addClass("input-error").focus();
			valid = false;
		}  
		var local_government = $("#local_government").val();
		if (local_government == "") {
			$(".error_local_government").html("Required.").addClass("error-color").show();
			$(".local_government").addClass("input-error").focus();
			valid = false;
		}    
		var country_of_origin = $("#country_of_origin").val();
		if (country_of_origin == "") {
			$(".error_country_of_origin").html("Required.").addClass("error-color").show();
			$(".country_of_origin").addClass("input-error").focus();
			valid = false;
		}  

		var state_of_origin = $("#state_of_origin").val();
		if (state_of_origin == "") {
			$(".error_state_of_origin").html("Required.").addClass("error-color").show();
			$(".state_of_origin").addClass("input-error").focus();
			valid = false;
		}  
		var emergency_relationship = $("#emergency_relationship").val();
		if (emergency_relationship == "") {
			$(".error_emergency_relationship").html("Required.").addClass("error-color").show();
			$(".emergency_relationship").addClass("input-error").focus();
			valid = false;
		}  
		var staffstatus = $("#staffstatus").val();
		if (staffstatus == "") {
			$(".error_staffstatus").html("Required.").addClass("error-color").show();
			$(".staffstatus").addClass("input-error").focus();
			valid = false;
		}  
		return valid;
	}
	
	
// 	$('form').on('submit', function (e) {
//     var isimagechanged = $('.is_image_changed').val();
//     const file = $('#avatar')[0].files[0];
//     const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
//     const maxSize = 2 * 1024 * 1024;
//     let isValid = true;

//     $('.error_avatar').text('');
//     if(isimagechanged == 1){
//         if (!file) {
//             $('.error_avatar').text('Please upload an image.');
//             isValid = false;
//         } else {
//             if (!allowedTypes.includes(file.type)) {
//                 $('.error_avatar').text('Only JPG, JPEG, or PNG files are allowed.');
//                 isValid = false;
//             } else if (file.size > maxSize) {
//                 $('.error_avatar').text('Image size must not exceed 2MB.');
//                 isValid = false;
//             }
//         }
//     }

//     if (!isValid && isimagechanged == 1) {
//         e.preventDefault(); // Prevent form from submitting
//     }
// });

						$(document).ready(function () {
										$('#addstaffform').on('submit', function (e) {
											const is_image_exists = $('#is_image_exists').val();
											let isValid = true;

											if (is_image_exists == 0) {
												isValid = validateImageInput('input[name="avatar"]', '.error_avatar');
											}

											if (!isValid) {
												e.preventDefault();
											}
										});
						});