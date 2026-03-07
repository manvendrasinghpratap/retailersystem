/*----------------Menu-----------------*/

jQuery(document).ready(function () {
	
	$('.default-zero').on('focus', function () {
	if ($.trim($(this).val()) === '0') {
		$(this).val('');
	}
	});

	$('.default-zero').on('blur', function () {
	if ($.trim($(this).val()) === '') {
		$(this).val('0');
	}
	});

    

    $('.securitynumber').on("cut copy paste",function(e) {

      e.preventDefault();

   });

    $(".securitynumber").keyup(function(){

        if (/\D/g.test($(this).val($(this).val()) )){$(this).val($(this).val().replace(/\D/g,''));}

   });

    $("input[name='ssn']").keyup(function() {

    $(this).val($(this).val().replace(/^(\d{3})(\d{2})(\d+)$/, "$1-$2-$3"));

    });

	$(".securitynumber").keyup(function() {

    $(this).val($(this).val().replace(/^(\d{3})(\d{2})(\d+)$/, "$1-$2-$3"));

    });
    $('.onlyalpha').on('input', function () {
            let cleaned = $(this).val().replace(/[^a-zA-Z ]/g, '');
            $(this).val(cleaned);
        });
    $(".onlyinteger").keyup(function () {

        if (/\D/g.test($(this).val($(this).val()))) { $(this).val($(this).val().replace(/\D/g, '')); }

    });

    $(".phoneUS").keyup(function () {

        $(".phoneUS").attr("minlength", "12");

        $(".phoneUS").attr("maxlength", "12");

        if (/\D/g.test($(this).val($(this).val()))) { $(this).val($(this).val().replace(/\D/g, '')); }

        if (($(this).val().length < 12) && $(this).val().length != 0) {

            $(this).val($(this).val().replace(/^(\d{3})(\d{3})(\d+)$/, "$1-$2-$3"));

        }

    });

    $("input[name='phone']").keyup(function () {

        $(".phone").remove();

        $("input[name='phone']").attr("minlength", "12");

        $("input[name='phone']").attr("maxlength", "12");

        $("input[name='phone']").attr("aria-invalid", "true");

        var l = $("input[name='phone']").val().length;

        if (l > 12) return false;



    });



    $(".phonenumber").on('input', function () {

        let value = $(this).val();

        

        if (value.length > 11) {

            $(this).val(value.slice(0, 11)); // trim input to max 12 digits

        }

    });



    $("input[name='phone']").blur(function () {

        $(".phone").remove();

        var l = $("input[name='phone']").val().length;

        if ((l < 12) && l != 0) {

            //$("input[name='phone']").after('<span class="wpcf7-not-valid-tip phone" aria-hidden="true">The field is too short.</span></span>');

        }

    });

    $(".deletecurrentrow").on('click', function () {

        var deleteRow = $(this)

        deleteRow.parents('.deleterow').remove();

        //deleteRow.parents('.row').fadeOut(200);

    });

		$('.nocutcopypaste').bind("cut copy paste", function (e) {

			e.preventDefault();

		});

		$('.auto_remove_space').on('keyup',function(e) {
			$( this ).val($( this ).val().replace(/\s/g, ''));
		});
	
		$('.minmax').on('input', function () {
			let max = parseInt($(this).data('max'));
			let min = parseInt($(this).data('min'));
			let val = parseInt($(this).val());

			if (val > max) $(this).val(0);
			if (val < min) $(this).val(0);
		});
	

		$('.allowintegerdigit').on('input', function () {
			this.value = this.value.replace(/\D/g, '').slice(0, 10);
		});
		$(".onlynumberdecimal").on("keypress", function (e) {
			let allowDecimal = 2;
			if ($(this).data('allowdecimal') !== undefined) {
			allowDecimal = parseInt($(this).data('allowdecimal'));
			}

			const char = String.fromCharCode(e.which);
			const val = $(this).val();

			// Allow control keys
			if (e.which === 0 || e.ctrlKey || e.metaKey || e.which < 32) {
			return;
			}

			// Allow only digits and one dot
			if (!char.match(/[0-9.]/)) {
			return false;
			}

			// Prevent multiple decimals
			if (char === '.' && val.includes('.')) {
			return false;
			}

			// Enforce digits before and after decimal
			const parts = val.split('.');
			const beforeDecimal = parts[0];
			const afterDecimal = parts[1] || '';

			const isTypingAfterDecimal = val.includes('.') && this.selectionStart > val.indexOf('.');

			// Limit digits before decimal
			if (!isTypingAfterDecimal && beforeDecimal.length >= 15 && char !== '.') {
			return false;
			}

			// Limit digits after decimal
			if (isTypingAfterDecimal && afterDecimal.length >= allowDecimal) {
			return false;
			}
		});



    $(".checkreqquantityandinstck").on('keyup', function (e) {

        var reqval = $(this).val();

        var instock = $(this).data('instock');

        var errormsg = $(this).data('errormsg');

        if (reqval > instock) {

            alert(errormsg);

            $(this).val(0);

            return false;

        }

        return true;

    });

    $(".savefoodpopupdata").click(function (e) {

        e.preventDefault();

        var form = $(this);

        console.log(form);

        $.ajax({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

            url: "savefoodtemdata",

            data: form.serialize(),

            type: 'POST',

            success: function (response) {

                $('.foofitemlist').html(response);

                selectfunction();



            },

            error: function (xhr) {

                if (xhr.status === 422) {

                    var errors = xhr.responseJSON.errors;

                    for (var error in errors) {

                        toastr.error(errors[error][0]);

                    }

                } else {

                    toastr.error('An error occurred.');

                }

            }

        });

    });

});



$('.deletetempdata').on('click', function () {

    var item_id = $(this).data('itemid');

    var packed_as = $(this).data('packed_as');

    var invoice_id = $(this).data('invoice_id');

    var wh_stock_id = $(this).data('wh_stock_id');

    if (confirm("Are you sure you want to delete?")) {

        var deleteRow = $(this)

        deleteRow.parents('.deleterow').fadeOut(200);

        $.ajax({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

            url: "deletetempdata",

            data: {

                'wh_stock_id': wh_stock_id,

                'item_id': item_id,

                'packed_as': packed_as,

                'invoice_id': invoice_id,

            },

            type: 'POST',

            success: function (response) {

                //alert(response);



            },

            error: function (xhr) {

                if (xhr.status === 422) {

                    var errors = xhr.responseJSON.errors;

                    for (var error in errors) {

                        toastr.error(errors[error][0]);

                    }

                } else {

                    toastr.error('An error occurred.');

                }

            }

        });

    }

});






function addMoreButton(className){

	$('input.validate-field').css( "border-color","#d5d5d5" );

	$("."+className+":last").clone().insertAfter("."+className+":last");

	$("."+className+":last").find("input").val("");

}

function addMoreMedicationList(className){

	var number = Date.now();

	$('input.validate-field').css( "border-color","#d5d5d5" );

	$("."+className+":last").clone().insertAfter("."+className+":last");

	$("."+className+":last").find("input").val("");

	$("."+className+":last").find("input.datepicker").removeClass("hasDatepicker").datepicker({dateFormat:'dd/mm/yy'});

	$("."+className+":last").find("input.datepicker").removeClass("hasDatepicker").each(function(){

		$(this).val('').attr('id', function(_, id) {

      return id + '_'+number;

    });

	});


}








