$(document).ready(function() {
    // 1) Capture a clean template row BEFORE initialising Select2
    //    This must be done before calling selectfunction() so the template
    //    doesn't include Select2 wrappers.
    var templateRow = $('.product-row:first').clone();

    // Remove labels in cloned rows (keep labels on first row only)
    templateRow.find('.field-label').remove();

    // Reset inputs/selects in the template
    templateRow.find('input').val('0');
    templateRow.find('select').val('');

    // 2) Initialise Select2 for existing selects (first row and any others already present)
    selectfunction();

    // 3) Delegated handlers for dynamic rows -------------------------------------------------

    // When product select changes, set price for that row and recalc
    $(document).on('change', '.setrateonchange', function() {
        var row = $(this).closest('.product-row');
        var selectedOption = $(this).find('option:selected');
        var price = selectedOption.data('price') || 0;
        row.find('.price').val(price);
        calculateAmount(row);
    });

    // When quantity or price input changes, recalc that row
    $(document).on('input', '.quantity, .price', function() {
        var row = $(this).closest('.product-row');
        calculateAmount(row);
    });

    // Add new product row: clone the CLEAN template and init Select2 on the new select only
    $('.add-more-product').on('click', function() {
        var newRow = templateRow.clone();

        // Ensure inputs are reset (defensive)
        newRow.find('input').val('0');
        newRow.find('select').val('');

        // Show remove button on cloned row
        newRow.find('.remove-product').removeClass('d-none');

        // Append new row after the last product-row
        $('.product-row:last').after(newRow);

        // Initialize Select2 only on the new row's selects
        newRow.find('.products').select2({
            placeholder: "Select Product",
            allowClear: true,
            width: '100%'
        });

        // If you want to auto-focus the new select:
        // newRow.find('.products').select2('open');
    });

    // Remove a product row (delegated)
    $(document).on('click', '.remove-product', function() {
        $(this).closest('.product-row').remove();
    });

    // Ensure first row calculations run on page load in case of prefilled values
    $('.product-row').each(function() {
        calculateAmount($(this));
    });
});

// Function to calculate amount for a specific row
function calculateAmount(row) {
    var price = parseFloat(row.find('.price').val()) || 0;
    var quantity = parseFloat(row.find('.quantity').val()) || 0;
    var amount = price * quantity;
    row.find('.amount').val(amount.toFixed(2));
}

// Init Select2 for all current .products selects (used on initial page load)
function selectfunction() {
    // If select2 already initialized on some selects, calling .select2() again is OK for most versions.
    // This initialises Select2 for selects that are not initialized yet.
    $(".products").each(function() {
        // Only initialize if not already initialized (select2 adds class 'select2-hidden-accessible')
        if (!$(this).next().hasClass('select2-container')) {
            $(this).select2({
                placeholder: "Select Product",
                allowClear: true,
                width: '100%'
            });
        }
    });
}