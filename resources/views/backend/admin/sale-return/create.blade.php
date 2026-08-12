@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    <div class="card">
        <div class="card-body">
            {{-- ==========================================================
            SESSION MESSAGES
            =========================================================== --}}
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- ==========================================================
            SALES RETURN FORM
            =========================================================== --}}

            <form action="{{ route('admin.sales-return.store') }}" method="POST" id="salesReturnForm">
                @csrf
                {{-- ======================================================
                SALE INFORMATION
                ======================================================= --}}
                <div class="row">
                    {{-- Invoice Number --}}
                    <x-text-input name="invoice_no" label="{{ __('translation.invoice_no') }}" value="{{ old('invoice_no') }}" required placeholder="Enter invoice number" mainrows="4" id="invoice_no" />
                    {{-- Return Date --}}
                    <x-text-input name="return_date" label="{{ __('translation.return_date') }}" value="{{ old('return_date', date(Config::get('constants.dateformat.slashdmyonly'))) }}" required placeholder="Return Date" readonly mainrows="4" />
                    {{-- Customer --}}
                    <x-text-input name="customer_name" label="{{ __('translation.customer_name') }}" value="{{ old('customer_name') }}" required placeholder="Customer will appear here" mainrows="4" readonly id="customer_name" />
                    {{-- Customer ID --}}
                    <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}">
                    {{-- Sale ID --}}
                    <input type="hidden" name="sale_id" id="sale_id" value="{{ old('sale_id') }}">
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="return_barcode" class="form-label">Scan Product Barcode <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="return_barcode" class="form-control" placeholder="Scan barcode..." autocomplete="off" inputmode="none">
                            <button type="button" class="btn btn-primary" id="scanBarcodeBtn"><i class="ri-barcode-line me-1"></i> Scan</button>
                        </div>
                        <small class="text-muted">Scan the product barcode to add it to the return.</small>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-striped align-middle" id="returnItemsTable">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>{{ __('translation.product') }}</th>
                                <th width="120">{{ __('translation.sold_qty') }}</th>
                                <th width="140">{{ __('translation.returned_qty') }}</th>
                                <th width="140">{{ __('translation.price') }}</th>
                                <th width="150">{{ __('translation.return_amount') }}</th>
                                <th width="100">{{ __('translation.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="returnItemsBody">
                            <tr class="empty-return-row">
                                <td colspan="7" class="text-center text-muted">{{ __('translation.enter_invoice_number_to_load_sale_items') }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">{{ __('translation.total_return_amount') }}</th>
                                <th><input type="text" id="return_total" name="return_total" class="form-control" value="0.00" readonly></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row mt-3">
                    <x-textarea-input name="reason" label="Reason" value="{{ old('reason') }}" rows="1" cols="50" :mainrows="12" required placeholder="Enter return reason" />
                </div>
                <div class="alert alert-info mt-3">
                    <i class="ri-wallet-3-line me-1"></i>
                    <strong>Customer Wallet:</strong>The return amount will be automatically added to the customer's wallet balance.
                </div>
                <div class="row mt-3">
                    <x-form-buttons submitText="Save Customer Return" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
                </div>
            </form>
        </div>
    </div>
    {{-- ==========================================================
    CUSTOMER MODAL
    ========================================================== --}}
    @include('backend.modal.add-customer-modal')
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            let scanProcessing = false;
            let customerSaving = false;
            let returnProcessing = false;
            let rowIndex = 0;
            let pendingCustomerSaleId = null;
            let pendingCustomerBarcode = null;
            const scanBarcodeUrl = "{{ route('admin.sales-return.scan-barcode') }}";
            const assignCustomerUrl = "{{ route('admin.sales-return.assign-customer') }}";
            /*
            |--------------------------------------------------------------------------
            | CUSTOMER MODAL
            |--------------------------------------------------------------------------
            */

            const customerModalElement = document.getElementById('customerModal');

            let customerModal = null;

            if (customerModalElement) {

                customerModal =
                    new bootstrap.Modal(
                        customerModalElement,
                        {
                            backdrop: 'static',
                            keyboard: false
                        }
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | FOCUS BARCODE
            |--------------------------------------------------------------------------
            */

            function focusReturnBarcode() {

                setTimeout(function () {

                    const $barcode =
                        $('#return_barcode');

                    if (
                        $barcode.length &&
                        !$barcode.prop('disabled')
                    ) {

                        $barcode
                            .trigger('focus')
                            .select();

                    }

                }, 250);

            }


            /*
            |--------------------------------------------------------------------------
            | INITIAL FOCUS
            |--------------------------------------------------------------------------
            */

            focusReturnBarcode();


            /*
            |--------------------------------------------------------------------------
            | INVOICE CHANGE
            |--------------------------------------------------------------------------
            */

            $('#invoice_no').on(
                'change',
                function () {

                    $('#customer_id').val('');
                    $('#customer_name').val('');
                    $('#sale_id').val('');

                    pendingCustomerSaleId = null;
                    pendingCustomerBarcode = null;

                    if (customerModal) {
                        customerModal.hide();
                    }

                    $('#returnItemsBody').html(`
                                                        <tr class="empty-return-row">
                                                            <td
                                                                colspan="7"
                                                                class="text-center text-muted"
                                                            >
                                                                Scan a product barcode to add it to the return.
                                                            </td>
                                                        </tr>
                                                    `);

                    rowIndex = 0;

                    calculateReturnTotal();

                    focusReturnBarcode();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | BARCODE ENTER
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'keydown',
                '#return_barcode',
                function (e) {

                    if (
                        e.key !== 'Enter' &&
                        e.which !== 13
                    ) {
                        return;
                    }

                    e.preventDefault();
                    e.stopPropagation();

                    if (scanProcessing) {
                        return;
                    }

                    const barcode =
                        $(this)
                            .val()
                            .trim();

                    if (!barcode) {
                        return;
                    }

                    scanReturnBarcode(barcode);

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SCAN BUTTON
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'click',
                '#scanBarcodeBtn',
                function () {

                    if (scanProcessing) {
                        return;
                    }

                    const barcode =
                        $('#return_barcode')
                            .val()
                            .trim();

                    if (!barcode) {

                        Swal.fire({

                            icon: 'warning',

                            title: 'Barcode Required',

                            text:
                                'Please scan a product barcode.',

                            confirmButtonText: 'OK'

                        }).then(function () {

                            focusReturnBarcode();

                        });

                        return;
                    }

                    scanReturnBarcode(barcode);

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SCAN BARCODE
            |--------------------------------------------------------------------------
            */

            function scanReturnBarcode(barcode) {

                const invoiceNo =
                    $('#invoice_no')
                        .val()
                        .trim();


                /*
                | Invoice required
                */

                if (!invoiceNo) {

                    Swal.fire({

                        icon: 'warning',

                        title: 'Invoice Required',

                        text:
                            'Please enter the invoice number first.',

                        confirmButtonText: 'OK'

                    }).then(function () {

                        $('#invoice_no')
                            .trigger('focus')
                            .select();

                    });

                    return;
                }


                if (scanProcessing) {
                    return;
                }


                scanProcessing = true;


                $('#return_barcode')
                    .prop('disabled', true);


                $.ajax({

                    url: scanBarcodeUrl,

                    type: 'GET',

                    data: {

                        invoice_no: invoiceNo,

                        barcode: barcode

                    },

                    dataType: 'json',


                    /*
                    |--------------------------------------------------------------------------
                    | SUCCESS
                    |--------------------------------------------------------------------------
                    */

                    success: function (response) {

                        console.log(
                            'SCAN RESPONSE:',
                            response
                        );


                        /*
                        | Customer required
                        */

                        if (
                            response &&
                            response.customer_required === true
                        ) {

                            openCustomerRequiredPopup(
                                response,
                                barcode
                            );

                            return;
                        }


                        /*
                        | Backend rejected request
                        */

                        if (
                            !response ||
                            response.success !== true
                        ) {

                            Swal.fire({

                                icon: 'warning',

                                title: 'Cannot Return Product',

                                html:
                                    response?.message ||
                                    'Barcode could not be verified.',

                                confirmButtonText: 'OK',

                                allowOutsideClick: false

                            }).then(function () {

                                focusReturnBarcode();

                            });

                            return;
                        }


                        /*
                        | Save sale ID
                        */

                        if (response.sale_id) {

                            $('#sale_id')
                                .val(response.sale_id);

                        }


                        /*
                        | Customer exists
                        */

                        if (
                            response.customer_id &&
                            parseInt(response.customer_id) > 0
                        ) {

                            $('#customer_id')
                                .val(response.customer_id);


                            $('#customer_name')
                                .val(
                                    response.customer_name || ''
                                );


                            addReturnItem(response);

                            return;
                        }


                        /*
                        | Customer missing
                        */

                        openCustomerRequiredPopup(
                            response,
                            barcode
                        );

                    },


                    /*
                    |--------------------------------------------------------------------------
                    | ERROR
                    |--------------------------------------------------------------------------
                    */

                    error: function (xhr) {

                        console.log(
                            'SCAN ERROR:',
                            xhr.responseJSON
                        );


                        const response =
                            xhr.responseJSON || {};


                        /*
                        | Customer required is a special flow
                        */

                        if (
                            response.customer_required === true
                        ) {

                            openCustomerRequiredPopup(
                                response,
                                barcode
                            );

                            return;
                        }


                        /*
                        | Normal error
                        */

                        Swal.fire({

                            icon: 'error',

                            title: 'Cannot Return Product',

                            html:
                                getAjaxErrorMessage(
                                    xhr,
                                    'Unable to verify barcode.'
                                ),

                            confirmButtonText: 'OK',

                            allowOutsideClick: false

                        }).then(function () {

                            focusReturnBarcode();

                        });

                    },


                    /*
                    |--------------------------------------------------------------------------
                    | COMPLETE
                    |--------------------------------------------------------------------------
                    */

                    complete: function () {

                        scanProcessing = false;


                        $('#return_barcode')
                            .prop('disabled', false)
                            .val('');

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | OPEN CUSTOMER MODAL
            |--------------------------------------------------------------------------
            */

            function openCustomerRequiredPopup(
                response,
                barcode
            ) {

                console.log(
                    'OPEN CUSTOMER MODAL:',
                    response
                );


                pendingCustomerSaleId =
                    response.sale_id || null;

                pendingCustomerBarcode =
                    barcode || null;


                /*
                | Save sale ID
                */

                $('#sale_id')
                    .val(
                        pendingCustomerSaleId || ''
                    );


                $('#customer_modal_sale_id')
                    .val(
                        pendingCustomerSaleId || ''
                    );


                /*
                | Clear previous errors
                */

                clearCustomerModalErrors();


                /*
                | Clear customer inputs
                */

                $('#modal_customer_name')
                    .val('');

                $('#modal_customer_phone')
                    .val('');

                $('#modal_customer_email')
                    .val('');


                /*
                | Invoice
                */

                $('#modal_invoice_no')
                    .val(
                        response.invoice_no ||
                        $('#invoice_no').val()
                    );


                /*
                | Barcode
                */

                $('#modal_barcode')
                    .val(
                        barcode || ''
                    );


                /*
                | Show modal
                */

                if (!customerModal) {

                    console.error(
                        'Customer modal is not initialized.'
                    );

                    Swal.fire({

                        icon: 'error',

                        title: 'Modal Error',

                        text:
                            'Customer modal could not be initialized.',

                        confirmButtonText: 'OK'

                    });

                    return;
                }


                customerModal.show();

            }


            /*
            |--------------------------------------------------------------------------
            | MODAL SHOWN
            |--------------------------------------------------------------------------
            */

            $('#customerModal').on(
                'shown.bs.modal',
                function () {

                    $('#modal_customer_name')
                        .trigger('focus');

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SAVE CUSTOMER BUTTON
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'click',
                '#saveCustomerBtn',
                function () {

                    saveCustomerToInvoice();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | ENTER INSIDE CUSTOMER MODAL
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'keydown',
                '#customerModal input',
                function (e) {

                    if (
                        e.key === 'Enter' ||
                        e.which === 13
                    ) {

                        e.preventDefault();

                        saveCustomerToInvoice();

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SAVE CUSTOMER
            |--------------------------------------------------------------------------
            */

            function saveCustomerToInvoice() {

                if (customerSaving) {
                    return;
                }


                const saleId =
                    pendingCustomerSaleId ||
                    $('#customer_modal_sale_id').val() ||
                    $('#sale_id').val();


                const name =
                    $('#modal_customer_name')
                        .val()
                        .trim();


                const phone =
                    $('#modal_customer_phone')
                        .val()
                        .trim();


                const email =
                    $('#modal_customer_email')
                        .val()
                        .trim();


                clearCustomerModalErrors();


                /*
                | Frontend validation
                */

                let hasError = false;


                if (!name) {

                    showCustomerFieldError(

                        '#modal_customer_name',

                        '#modal_customer_name_error',

                        'Customer name is required.'

                    );

                    hasError = true;

                }


                if (!phone) {

                    showCustomerFieldError(

                        '#modal_customer_phone',

                        '#modal_customer_phone_error',

                        'Phone number is required.'

                    );

                    hasError = true;

                }


                if (
                    email &&
                    !isValidEmail(email)
                ) {

                    showCustomerFieldError(

                        '#modal_customer_email',

                        '#modal_customer_email_error',

                        'Please enter a valid email address.'

                    );

                    hasError = true;

                }


                if (hasError) {
                    return;
                }


                /*
                | Sale ID required
                */

                if (!saleId) {

                    Swal.fire({

                        icon: 'error',

                        title: 'Sale Information Missing',

                        text:
                            'Sale ID is missing. Please scan the barcode again.',

                        confirmButtonText: 'OK'

                    });

                    return;
                }


                /*
                | Processing
                */

                customerSaving = true;


                const $button =
                    $('#saveCustomerBtn');


                const originalButtonHtml =
                    $button.html();


                $button
                    .prop('disabled', true)
                    .html(`
                                                                                                                                                                                                        <span
                                                                                                                                                                                                            class="spinner-border spinner-border-sm me-1"
                                                                                                                                                                                                            role="status"
                                                                                                                                                                                                        ></span>
                                                                                                                                                                                                        Saving...
                                                                                                                                                                                                    `);


                /*
                | AJAX
                */

                $.ajax({

                    url: assignCustomerUrl,

                    type: 'POST',

                    data: {

                        _token: "{{ csrf_token() }}",

                        sale_id: saleId,

                        name: name,

                        phone: phone,

                        email: email

                    },

                    dataType: 'json',


                    /*
                    |--------------------------------------------------------------------------
                    | SUCCESS
                    |--------------------------------------------------------------------------
                    */

                    success: function (response) {

                        console.log(
                            'CUSTOMER RESPONSE:',
                            response
                        );


                        if (
                            !response ||
                            response.success !== true
                        ) {

                            showCustomerError(

                                response?.message ||
                                'Unable to create customer.'

                            );

                            return;
                        }


                        /*
                        | Update customer
                        */

                        $('#customer_id')
                            .val(
                                response.customer_id
                            );


                        $('#customer_name')
                            .val(
                                response.customer_name ||
                                name
                            );


                        /*
                        | Save pending barcode BEFORE clearing state
                        */

                        const barcode =
                            pendingCustomerBarcode;


                        /*
                        | Clear state
                        */

                        pendingCustomerSaleId = null;

                        pendingCustomerBarcode = null;


                        /*
                        | Close modal
                        */

                        if (customerModal) {

                            customerModal.hide();

                        }


                        /*
                        | Re-scan barcode
                        */

                        if (barcode) {

                            setTimeout(function () {

                                scanReturnBarcode(
                                    barcode
                                );

                            }, 500);

                        } else {

                            focusReturnBarcode();

                        }

                    },


                    /*
                    |--------------------------------------------------------------------------
                    | ERROR
                    |--------------------------------------------------------------------------
                    */

                    error: function (xhr) {

                        console.log(
                            'CUSTOMER ERROR:',
                            xhr.responseJSON
                        );


                        if (
                            xhr.responseJSON &&
                            xhr.responseJSON.errors
                        ) {

                            displayCustomerValidationErrors(
                                xhr.responseJSON.errors
                            );

                            return;
                        }


                        showCustomerError(

                            getAjaxErrorMessage(
                                xhr,
                                'Unable to create customer.'
                            )

                        );

                    },


                    /*
                    |--------------------------------------------------------------------------
                    | COMPLETE
                    |--------------------------------------------------------------------------
                    */

                    complete: function () {

                        customerSaving = false;


                        $button
                            .prop('disabled', false)
                            .html(originalButtonHtml);

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | SHOW CUSTOMER FIELD ERROR
            |--------------------------------------------------------------------------
            */

            function showCustomerFieldError(
                inputSelector,
                errorSelector,
                message
            ) {

                $(inputSelector)
                    .addClass('is-invalid');


                $(errorSelector)
                    .text(message);

            }


            /*
            |--------------------------------------------------------------------------
            | CLEAR CUSTOMER ERRORS
            |--------------------------------------------------------------------------
            */

            function clearCustomerModalErrors() {

                $('#customerModal')
                    .find('.is-invalid')
                    .removeClass('is-invalid');


                $('#customerModal')
                    .find('.invalid-feedback')
                    .text('');

            }


            /*
            |--------------------------------------------------------------------------
            | DISPLAY LARAVEL VALIDATION ERRORS
            |--------------------------------------------------------------------------
            */

            function displayCustomerValidationErrors(
                errors
            ) {

                let firstField = null;


                $.each(
                    errors,
                    function (
                        key,
                        messages
                    ) {

                        const message =
                            Array.isArray(messages)
                                ? messages[0]
                                : messages;


                        let inputSelector = null;

                        let errorSelector = null;


                        switch (key) {

                            case 'name':

                                inputSelector =
                                    '#modal_customer_name';

                                errorSelector =
                                    '#modal_customer_name_error';

                                break;


                            case 'phone':

                                inputSelector =
                                    '#modal_customer_phone';

                                errorSelector =
                                    '#modal_customer_phone_error';

                                break;


                            case 'email':

                                inputSelector =
                                    '#modal_customer_email';

                                errorSelector =
                                    '#modal_customer_email_error';

                                break;

                        }


                        if (
                            inputSelector &&
                            errorSelector
                        ) {

                            showCustomerFieldError(

                                inputSelector,

                                errorSelector,

                                message

                            );


                            if (!firstField) {

                                firstField =
                                    inputSelector;

                            }

                        }

                    }
                );


                if (firstField) {

                    setTimeout(function () {

                        $(firstField)
                            .trigger('focus');

                    }, 100);

                } else {

                    showCustomerError(
                        'Please check the customer information and try again.'
                    );

                }

            }


            /*
            |--------------------------------------------------------------------------
            | CUSTOMER ERROR
            |--------------------------------------------------------------------------
            */

            function showCustomerError(message) {

                Swal.fire({

                    icon: 'error',

                    title: 'Customer Error',

                    html: message,

                    confirmButtonText: 'OK',

                    allowOutsideClick: false

                });

            }


            /*
            |--------------------------------------------------------------------------
            | CANCEL CUSTOMER MODAL
            |--------------------------------------------------------------------------
            */

            $('#cancelCustomerModal').on(
                'click',
                function () {

                    if (customerModal) {

                        customerModal.hide();

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | CUSTOMER MODAL CLOSED
            |--------------------------------------------------------------------------
            */

            $('#customerModal').on(
                'hidden.bs.modal',
                function () {

                    if (
                        !$('#customer_id').val()
                    ) {

                        pendingCustomerSaleId = null;

                        pendingCustomerBarcode = null;

                    }


                    focusReturnBarcode();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | ADD RETURN ITEM
            |--------------------------------------------------------------------------
            */

            function addReturnItem(response) {

                const saleItemId =
                    response.sale_item_id;


                const trackingId =
                    response.tracking_id;


                const barcode =
                    response.barcode || '';


                const productId =
                    response.product_id || '';


                const productName =
                    response.product_name ||
                    'Product';


                const price =
                    parseFloat(
                        response.price
                    ) || 0;


                const returnableQty =
                    parseFloat(
                        response.returnable_quantity ??
                        response.quantity ??
                        1
                    ) || 0;


                /*
                | Sale item validation
                */

                if (!saleItemId) {

                    Swal.fire({

                        icon: 'error',

                        title: 'Invalid Sale Item',

                        text:
                            'Sale item ID was not returned by the server.',

                        confirmButtonText: 'OK'

                    });

                    return;
                }


                /*
                | Tracking validation
                */

                if (!trackingId) {

                    Swal.fire({

                        icon: 'error',

                        title: 'Tracking ID Missing',

                        text:
                            'Tracking ID was not returned for this barcode.',

                        confirmButtonText: 'OK'

                    });

                    return;
                }


                /*
                | Duplicate barcode
                */

                const existingTracking =
                    $('#returnItemsBody tr.return-item-row')
                        .filter(function () {

                            return String(
                                $(this)
                                    .attr('data-tracking-id')
                            ) === String(
                                trackingId
                            );

                        });


                if (existingTracking.length) {

                    Swal.fire({

                        icon: 'warning',

                        title: 'Already Scanned',

                        html:

                            '<strong>' +
                            escapeHtml(productName) +
                            '</strong><br><br>' +

                            'Barcode <strong>' +
                            escapeHtml(barcode) +
                            '</strong> has already been scanned.',

                        confirmButtonText: 'OK'

                    }).then(function () {

                        focusReturnBarcode();

                    });

                    return;
                }


                /*
                | Remove empty row
                */

                $('#returnItemsBody')
                    .find('.empty-return-row')
                    .remove();


                /*
                | Form index
                */

                const currentIndex =
                    rowIndex++;


                /*
                | Create row
                */

                const row = `

                                                                                                                                                                                                    <tr
                                                                                                                                                                                                        class="return-item-row table-warning"
                                                                                                                                                                                                        data-sale-item-id="${saleItemId}"
                                                                                                                                                                                                        data-tracking-id="${trackingId}"
                                                                                                                                                                                                        data-barcode="${escapeHtml(barcode)}"
                                                                                                                                                                                                    >

                                                                                                                                                                                                        <td>
                                                                                                                                                                                                            ${$('#returnItemsBody tr.return-item-row').length + 1}
                                                                                                                                                                                                        </td>


                                                                                                                                                                                                        <td>

                                                                                                                                                                                                            <strong>
                                                                                                                                                                                                                ${escapeHtml(productName)}
                                                                                                                                                                                                            </strong>

                                                                                                                                                                                                            <br>

                                                                                                                                                                                                            <small class="text-muted">

                                                                                                                                                                                                                Barcode:
                                                                                                                                                                                                                ${escapeHtml(barcode)}

                                                                                                                                                                                                            </small>

                                                                                                                                                                                                        </td>


                                                                                                                                                                                                        <td>

                                                                                                                                                                                                            <span class="soldQty">
                                                                                                                                                                                                                ${returnableQty}
                                                                                                                                                                                                            </span>

                                                                                                                                                                                                        </td>


                                                                                                                                                                                                        <td>

                                                                                                                                                                                                            <input
                                                                                                                                                                                                                type="number"
                                                                                                                                                                                                                class="form-control returnQty"
                                                                                                                                                                                                                value="1"
                                                                                                                                                                                                                min="1"
                                                                                                                                                                                                                max="1"
                                                                                                                                                                                                                data-sale-item-id="${saleItemId}"
                                                                                                                                                                                                                data-tracking-id="${trackingId}"
                                                                                                                                                                                                                data-barcode="${escapeHtml(barcode)}"
                                                                                                                                                                                                                data-price="${price}"
                                                                                                                                                                                                                readonly
                                                                                                                                                                                                            >


                                                                                                                                                                                                            <input
                                                                                                                                                                                                                type="hidden"
                                                                                                                                                                                                                name="items[${currentIndex}][sale_item_id]"
                                                                                                                                                                                                                value="${saleItemId}"
                                                                                                                                                                                                            >


                                                                                                                                                                                                            <input
                                                                                                                                                                                                                type="hidden"
                                                                                                                                                                                                                name="items[${currentIndex}][product_id]"
                                                                                                                                                                                                                value="${productId}"
                                                                                                                                                                                                            >


                                                                                                                                                                                                            <input
                                                                                                                                                                                                                type="hidden"
                                                                                                                                                                                                                name="items[${currentIndex}][tracking_ids][]"
                                                                                                                                                                                                                value="${trackingId}"
                                                                                                                                                                                                            >


                                                                                                                                                                                                            <input
                                                                                                                                                                                                                type="hidden"
                                                                                                                                                                                                                name="items[${currentIndex}][barcode][]"
                                                                                                                                                                                                                value="${escapeHtml(barcode)}"
                                                                                                                                                                                                            >


                                                                                                                                                                                                            <input
                                                                                                                                                                                                                type="hidden"
                                                                                                                                                                                                                name="items[${currentIndex}][quantity]"
                                                                                                                                                                                                                value="1"
                                                                                                                                                                                                                class="returnQuantityHidden"
                                                                                                                                                                                                            >


                                                                                                                                                                                                            <input
                                                                                                                                                                                                                type="hidden"
                                                                                                                                                                                                                name="items[${currentIndex}][price]"
                                                                                                                                                                                                                value="${price}"
                                                                                                                                                                                                            >

                                                                                                                                                                                                        </td>


                                                                                                                                                                                                        <td>

                                                                                                                                                                                                            ${currencyValue(price)}

                                                                                                                                                                                                        </td>


                                                                                                                                                                                                        <td>

                                                                                                                                                                                                            <span class="returnAmount">

                                                                                                                                                                                                                ${price.toFixed(2)}

                                                                                                                                                                                                            </span>

                                                                                                                                                                                                        </td>


                                                                                                                                                                                                        <td>

                                                                                                                                                                                                            <button
                                                                                                                                                                                                                type="button"
                                                                                                                                                                                                                class="btn btn-danger btn-sm deleteReturnItem"
                                                                                                                                                                                                            >

                                                                                                                                                                                                                <i class="ri-delete-bin-line"></i>

                                                                                                                                                                                                                Delete

                                                                                                                                                                                                            </button>

                                                                                                                                                                                                        </td>

                                                                                                                                                                                                    </tr>

                                                                                                                                                                                                `;


                $('#returnItemsBody')
                    .append(row);


                updateRowNumbers();

                calculateReturnTotal();


                /*
                | Product added
                */

                Swal.fire({

                    icon: 'success',

                    title: 'Product Added',

                    html:

                        '<strong>' +
                        escapeHtml(productName) +
                        '</strong><br>' +

                        'Barcode: ' +
                        escapeHtml(barcode),

                    timer: 900,

                    showConfirmButton: false

                }).then(function () {

                    focusReturnBarcode();

                });

            }


            /*
            |--------------------------------------------------------------------------
            | DELETE RETURN ITEM
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'click',
                '.deleteReturnItem',
                function () {

                    const row =
                        $(this)
                            .closest(
                                'tr.return-item-row'
                            );


                    if (!row.length) {
                        return;
                    }


                    const productName =
                        row.find(
                            'td:nth-child(2) strong'
                        )
                            .text()
                            .trim();


                    const barcode =
                        row.attr(
                            'data-barcode'
                        ) || '';


                    Swal.fire({

                        icon: 'warning',

                        title: 'Remove Product?',

                        html:

                            '<strong>' +
                            escapeHtml(productName) +
                            '</strong><br><br>' +

                            'Barcode: ' +
                            escapeHtml(barcode) +

                            '<br><br>' +

                            'Do you want to remove this product from the return?',

                        showCancelButton: true,

                        confirmButtonText:
                            'Yes, Remove',

                        cancelButtonText:
                            'Cancel',

                        confirmButtonColor:
                            '#dc3545',

                        cancelButtonColor:
                            '#6c757d',

                        reverseButtons: true

                    }).then(function (result) {

                        if (!result.isConfirmed) {
                            return;
                        }


                        row.remove();


                        updateRowNumbers();

                        calculateReturnTotal();


                        if (
                            $('#returnItemsBody tr.return-item-row')
                                .length === 0
                        ) {

                            $('#returnItemsBody').html(`

                                                                                                                                                                                                                <tr class="empty-return-row">

                                                                                                                                                                                                                    <td
                                                                                                                                                                                                                        colspan="7"
                                                                                                                                                                                                                        class="text-center text-muted"
                                                                                                                                                                                                                    >

                                                                                                                                                                                                                        Scan a product barcode to add it
                                                                                                                                                                                                                        to the return.

                                                                                                                                                                                                                    </td>

                                                                                                                                                                                                                </tr>

                                                                                                                                                                                                            `);

                        }


                        focusReturnBarcode();

                    });

                }
            );


            /*
            |--------------------------------------------------------------------------
            | UPDATE ROW NUMBERS
            |--------------------------------------------------------------------------
            */

            function updateRowNumbers() {

                $('#returnItemsBody tr.return-item-row')
                    .each(function (index) {

                        $(this)
                            .find('td:first')
                            .text(index + 1);

                    });

            }


            /*
            |--------------------------------------------------------------------------
            | CALCULATE TOTAL
            |--------------------------------------------------------------------------
            */

            function calculateReturnTotal() {

                let total = 0;


                $('#returnItemsBody tr.return-item-row')
                    .each(function () {

                        const $row =
                            $(this);


                        const quantity =
                            parseFloat(
                                $row
                                    .find('.returnQty')
                                    .val()
                            ) || 0;


                        const price =
                            parseFloat(
                                $row
                                    .find('.returnQty')
                                    .data('price')
                            ) || 0;


                        const amount =
                            quantity * price;


                        $row
                            .find('.returnAmount')
                            .text(
                                amount.toFixed(2)
                            );


                        total += amount;

                    });


                $('#return_total')
                    .val(
                        total.toFixed(2)
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | PREVENT QUANTITY EDIT
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'keydown',
                '.returnQty',
                function (e) {

                    if (e.key === 'Tab') {
                        return;
                    }

                    e.preventDefault();

                }
            );


            $(document).on(
                'paste',
                '.returnQty',
                function (e) {

                    e.preventDefault();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | FORM SUBMIT
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'submit',
                '#salesReturnForm',
                function (e) {

                    e.preventDefault();


                    if (returnProcessing) {
                        return;
                    }


                    const form = this;


                    /*
                    | Customer
                    */

                    const customerId =
                        $('#customer_id')
                            .val()
                            .trim();


                    if (!customerId) {

                        const saleId =
                            $('#sale_id')
                                .val();


                        if (saleId) {

                            openCustomerRequiredPopup(

                                {
                                    sale_id: saleId,
                                    invoice_no:
                                        $('#invoice_no').val()
                                },

                                ''

                            );

                            return;

                        }


                        Swal.fire({

                            icon: 'warning',

                            title: 'Customer Required',

                            text:
                                'Please scan a product barcode first.',

                            confirmButtonText: 'OK'

                        }).then(function () {

                            focusReturnBarcode();

                        });

                        return;
                    }


                    /*
                    | Items
                    */

                    const itemCount =
                        $('#returnItemsBody tr.return-item-row')
                            .length;


                    if (itemCount === 0) {

                        Swal.fire({

                            icon: 'warning',

                            title: 'Scan Product',

                            text:
                                'Please scan at least one product before processing the return.',

                            confirmButtonText: 'OK'

                        }).then(function () {

                            focusReturnBarcode();

                        });

                        return;
                    }


                    /*
                    | Total
                    */

                    const returnTotal =
                        parseFloat(
                            $('#return_total').val()
                        ) || 0;


                    /*
                    | Confirmation
                    */

                    Swal.fire({

                        icon: 'question',

                        title: 'Process Customer Return?',

                        html:

                            'Return Amount: ' +

                            '<strong>' +

                            currencyValue(returnTotal) +

                            '</strong>' +

                            '<br><br>' +

                            'This amount will be added to the customer wallet.' +

                            '<br><br>' +

                            'Are you sure you want to process this return?',

                        showCancelButton: true,

                        confirmButtonText:
                            'Yes, Process Return',

                        cancelButtonText:
                            'Cancel',

                        reverseButtons: true

                    }).then(function (result) {

                        if (!result.isConfirmed) {
                            return;
                        }


                        processReturn(form);

                    });

                }
            );


            /*
            |--------------------------------------------------------------------------
            | PROCESS RETURN
            |--------------------------------------------------------------------------
            */

            function processReturn(form) {

                if (returnProcessing) {
                    return;
                }


                returnProcessing = true;


                const $form =
                    $(form);


                const $submitButton =
                    $form.find(
                        'button[type="submit"], input[type="submit"]'
                    );


                $submitButton
                    .prop('disabled', true);


                const formData =
                    new FormData(form);


                $.ajax({

                    url:
                        $form.attr('action'),

                    type:
                        $form.attr('method') || 'POST',

                    data:
                        formData,

                    processData: false,

                    contentType: false,

                    dataType: 'json',


                    success: function (response) {

                        console.log(
                            'RETURN RESPONSE:',
                            response
                        );


                        if (
                            !response ||
                            response.success !== true
                        ) {

                            showReturnError(

                                response?.message ||
                                'Unable to complete customer return.'

                            );

                            return;
                        }


                        Swal.fire({

                            icon: 'success',

                            title: 'Return Completed',

                            html:

                                '<strong>' +

                                escapeHtml(
                                    response.return_no || ''
                                ) +

                                '</strong><br><br>' +

                                escapeHtml(
                                    response.message ||
                                    'Customer return completed successfully.'
                                ) +

                                '<br><br>' +

                                'The return amount has been added to the customer wallet.',

                            confirmButtonText: 'OK',

                            allowOutsideClick: false

                        }).then(function () {

                            window.location.reload();

                        });

                    },


                    error: function (xhr) {

                        console.log(
                            'RETURN ERROR:',
                            xhr.responseJSON
                        );


                        showReturnError(

                            getAjaxErrorMessage(

                                xhr,

                                'Unable to process customer return.'

                            )

                        );

                    },


                    complete: function () {

                        returnProcessing = false;


                        $submitButton
                            .prop('disabled', false);

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | RETURN ERROR
            |--------------------------------------------------------------------------
            */

            function showReturnError(message) {

                Swal.fire({

                    icon: 'error',

                    title: 'Cannot Complete Return',

                    html: message,

                    confirmButtonText: 'OK',

                    allowOutsideClick: false

                }).then(function () {

                    focusReturnBarcode();

                });

            }


            /*
            |--------------------------------------------------------------------------
            | AJAX ERROR MESSAGE
            |--------------------------------------------------------------------------
            */

            function getAjaxErrorMessage(
                xhr,
                defaultMessage
            ) {

                let message =
                    defaultMessage;


                if (
                    xhr &&
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {

                    message =
                        xhr.responseJSON.message;

                }


                if (
                    xhr &&
                    xhr.responseJSON &&
                    xhr.responseJSON.errors
                ) {

                    let errors = [];


                    $.each(
                        xhr.responseJSON.errors,
                        function (
                            key,
                            value
                        ) {

                            if (Array.isArray(value)) {

                                errors.push(...value);

                            } else {

                                errors.push(value);

                            }

                        }
                    );


                    if (errors.length) {

                        message =
                            errors.join('<br>');

                    }

                }


                return message;

            }


            /*
            |--------------------------------------------------------------------------
            | EMAIL VALIDATION
            |--------------------------------------------------------------------------
            */

            function isValidEmail(email) {

                const pattern =
                    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


                return pattern.test(email);

            }


            /*
            |--------------------------------------------------------------------------
            | CURRENCY
            |--------------------------------------------------------------------------
            */

            function currencyValue(value) {

                const currency =
                    "{{ __('translation.b_ngn') }}";


                return currency +
                    ' ' +
                    parseFloat(
                        value || 0
                    ).toFixed(2);

            }


            /*
            |--------------------------------------------------------------------------
            | ESCAPE HTML
            |--------------------------------------------------------------------------
            */

            function escapeHtml(value) {

                if (
                    value === null ||
                    value === undefined
                ) {

                    return '';

                }


                return String(value)

                    .replace(/&/g, '&amp;')

                    .replace(/</g, '&lt;')

                    .replace(/>/g, '&gt;')

                    .replace(/"/g, '&quot;')

                    .replace(/'/g, '&#039;');

            }

        });

    </script>

@endsection