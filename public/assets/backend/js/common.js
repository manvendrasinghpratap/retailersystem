jQuery(document).ready(function () {
        $('.datepicker').datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
			defaultViewDate: { year: new Date().getFullYear()-20, month: 01, day: 01 },
			yearRange: "-80:+0", 
        }).val();
        $('.downloadpdf').on('click', function() {
            var downloadroutepdf = $(this).attr("data-downloadroutepdf");
            var queryString = new URL(window.location).search;
            var current = downloadroutepdf;
            linkhref =
                `${current}${current.includes('?') ? queryString.replace('?', '&') : queryString}`;
            window.open(linkhref, '_blank');
        });
        $('.calculateage').on('change', function() {
            calculateage();
        });

        function calculateage(){
            var age = 0;
            if ($('#birth_date').length) 
            {
                var start = $('#birth_date').val();
                var dateArray = start.split('-');
                let fyear = parseInt(dateArray[0]);
                var currentYear = new Date().getFullYear();
                if (fyear > 1900 && fyear <= currentYear) {
                    var newdate = dateArray[0] + ' ' + dateArray[1] + ' ' + dateArray[2];
                    var dob = new Date(newdate);
                    var today = new Date();
                    var age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
                    if (age < 0) age = 0;
                }
                $('#age_adult').val(age);
            }
        }

        calculateage();
		$('.close').on('click',function(){
            $('.alert').hide();
        });	
		flatdatepickr();		
			
});

	function flatdatepickr(){	
        $(".flatdatepickr").each(function() {
                const $input = $(this);

                const minDate = $input.attr("data-mindate") || "1970-01-01";
                const maxDate = $input.attr("data-maxdate") || 'today';

                const defaultDateNull = $input.attr("data-defaultdatenull");
                const defaultDate = (defaultDateNull && defaultDateNull.toLowerCase() === "blank")
                ? null
                : $input.val() || null;

                flatpickr(this, {
                dateFormat: "d/m/Y",
                minDate: minDate,
                maxDate: maxDate,
                allowInput: false,
                defaultDate: defaultDate,
                });
        });			
		
		$(".flatdatepickrto").each(function() {			
		var minDate = $(this).attr("data-mindate"); // Get the data-year attribute for each input
		var maxDate = $(this).attr("data-maxdate"); // Get the data-year attribute for each input

		flatpickr(".flatdatepickrto", {
				dateFormat: "d/m/Y", // Flatpickr uses d/m/Y for dd/mm/yy
				maxDate: maxDate,
				minDate: minDate, 
				allowInput: false,
				defaultDate: $(this).val() ? $(this).val() : null, // Set only if value exists
				onOpen: function(selectedDates, dateStr, instance) {
				//console.log("Default Date for this field:", defaultDate); // Debugging
				}
				
			});
		});
	} 
		
		
    $(document).ready(function() {
        $(".suffix").select2({
        placeholder: "Select Title",
        allowClear: true
        });
        $(".state").select2({
        placeholder: "Select State",
        allowClear: true
        });
        $(".country").select2({
        placeholder: "Select Country",
        allowClear: true
        });
        $(".local_government").select2({
        placeholder: "Select Local Government",
        allowClear: true
        });
        $(".account_status").select2({
        placeholder: "Select Account Status",
        allowClear: true
        });
        $(".subscription_id").select2({
        placeholder: "Select Subscription",
        allowClear: true
        });
        $(".accountstatus").select2({
        placeholder: "Select Status",
        allowClear: true
        }); 
        $(".menucategory").select2({
        placeholder: "Select Category",
        allowClear: true
        }); 
        $(".products").select2({
        placeholder: "Select Product",
        allowClear: true
        }); 
        $(".customer").select2({
        placeholder: "Select Customer",
        allowClear: true
        }); 
        $(".staff").select2({
        placeholder: "Select Staff",
        allowClear: true
        }); 
        $(".sales_executive").select2({
        placeholder: "Select Sales Executive",
        allowClear: true
        }); 
        
        $(".payment_way").select2({
        placeholder: "Select Payment Way",
        allowClear: true
        }); 
        $(".designation").select2({
        placeholder: "Select Designation",
        allowClear: true
        }); 
        $(".is_ative").select2({
        placeholder: "Select Status",
        allowClear: true
        }); 
        $(".payment_status").select2({
            placeholder:'Select Payment Status',
            allowClear:true,
        });
        $(".types").select2({
            placeholder:'Select Type',
            allowClear:true,
        });
        $(".customer_payment_method").select2({
            placeholder:'Select Payment Method',
            allowClear:true,
        });
        $(".disabledoption").select2({
        placeholder: "Select Option",
        allowClear: false,
        disabled: true
        });
        
        
        $('.staffname, .email, .password_confirmation, .password').bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $(".changepassword").on("click", function() {
        $('#password').val('');
        $('#password_confirmation').val('');
        var ordid = $(this).attr("data-id");
        $('#changepassworduserid').val(ordid);
        $('#exampleModal').modal('show');
        });	
        setdefaultvaluezero();
    });

    function setdefaultvaluezero(){
    // $('.setdefaultzero').val(0); // Set default value to 0
        $('.setdefaultzero').on('focus', function() {
            if ($(this).val() == '0') {
                $(this).val('');
            }
        });
        $('.setdefaultzero').on('blur', function() {
            if ($(this).val().trim() === '') {
                $(this).val('0');
            }
        });
    }

    function validateImageInput(fileInputSelector, errorContainerSelector, maxSizeMB = 2) {
            const input = $(fileInputSelector)[0]; // raw DOM element
            const file = input.files[0];
            const errorContainer = $(errorContainerSelector);
            const maxSize = maxSizeMB * 1024 * 1024; // Convert MB to bytes

            if (!file) {
                errorContainer.html("* Required.").addClass("error-color").show();
                return false;
            }

            if (!file.type.startsWith('image/')) {
                errorContainer.html("* Only valid image files are allowed (JPG, PNG, etc).").addClass("error-color").show();
                return false;
            }

            if (file.size > maxSize) {
                errorContainer.html(`* Maximum image size is ${maxSizeMB}MB.`).addClass("error-color").show();
                return false;
            }

            errorContainer.hide();
            return true;
    }

    function setupPdfDownload(selector, dataAttr) {
        $(document).off('click', selector).on('click', selector, function(e) {
            e.preventDefault();

            var downloadUrl = $(this).attr(dataAttr);
            if (!downloadUrl) return;

            var queryString = window.location.search;
            var linkHref = downloadUrl + (downloadUrl.includes('?') ? queryString.replace('?', '&') : queryString);
            window.open(linkHref, '_blank');
        });
    }

    /**
 * Initialize Select2 for any given select element
 * @param {jQuery} select - jQuery selector for select element(s)
 */
function initSelect2(select) {
    select.select2({
        placeholder: "Select an option",
        allowClear: true,
        width: '100%'
    });

    // Remove error class when changed
    select.on('change', function() {
        $(this).next('.select2-container')
            .find('.select2-selection')
            .removeClass('error');
    });
}

/**
 * Initialize Select2 for all .required-select on the page
 */
function initAllSelect2() {
    $('.required-select').each(function() {
        initSelect2($(this));
    });
}

/**
 * Validate form required selects with Select2
 * @param {string} formSelector - jQuery form selector
 * @returns {boolean} true if valid, false otherwise
 */
function validateSelect2Form(formSelector) {
    var isValid = true;

    $(formSelector).find('.required-select').each(function() {
        var value = $(this).val();
        var container = $(this).next('.select2-container');

        container.find('.select2-selection').removeClass('error');

        if (!value || value === '') {
            isValid = false;
            container.find('.select2-selection').addClass('error');
        }
    });

    return isValid;
}

/**
 * Common setup to call inside $(document).ready()
 */
function setupSelect2WithValidation(formSelector) {
    initAllSelect2();

    $(formSelector).on('submit', function(e) {
        if (!validateSelect2Form(formSelector)) {
            e.preventDefault(); // stop form submit
        }
    });
}


		