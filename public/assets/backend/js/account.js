function validatedata(){
	var valid = true;
	var suffix = $(".suffix").val();
	if (suffix == "") {
		$(".error_suffix").html("Required.").addClass("error-color").show();
		$(".suffix").addClass("input-error").focus();
		valid = false;
	} 
	var state = $(".state").val();
	if (state == "") {
		$(".error_state").html("Required.").addClass("error-color").show();
		$(".state").addClass("input-error").focus();
		valid = false;
	}  
	var country = $(".country").val();
	if (country == "") {
		$(".error_country").html("Required.").addClass("error-color").show();
		$(".country").addClass("input-error").focus();
		valid = false;
	}  
	var local_government = $(".local_government").val();
	if (local_government == "") {
		$(".error_local_government").html("Required.").addClass("error-color").show();
		$(".local_government").addClass("input-error").focus();
		valid = false;
	}   
	const password = $('#password').val();
	const confirmPassword = $('#password_confirmation').val();
	if (password != confirmPassword) {
		$(".error_password").html("* Passwords do not match.").addClass("error-color").show();
		$(".password").addClass("input-error").focus();
		$('#password').val('');
		$('#password_confirmation').val('');
		valid = false;
	} 
	return valid;
 }
$('.setusername').keyup(function() {
	var firstname = $('#first_name').val().trim().replace(/\s+/g, '').toLowerCase();
	var cell_phone = $('#cell_phone').val().trim().replace(/\s+/g, '').toLowerCase();
	var name = firstname +'.'+ cell_phone;
	var newusername = name.trim().replace(/\s+/g, '').toLowerCase();
	$('.username').val(newusername);
});