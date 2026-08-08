@extends('backend.layouts.master-horizontal')

@section('title', 'Customer Return')

@section('content')

    <div class="container-fluid">

        {{-- Breadcrumb --}}

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    Customer Return
                </h5>
            </div>

            <div class="card-body">

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.sales-return.store') }}" method="POST" id="salesReturnForm">
                    @csrf

                    <div class="row">

                        {{-- Invoice Number --}}
                        <div class="col-md-4 mb-3">
                            <label for="invoice_no" class="form-label">
                                Invoice No
                            </label>

                            <input type="text" name="invoice_no" id="invoice_no" class="form-control" value="{{ old('invoice_no') }}" placeholder="Enter invoice number" required>

                            @error('invoice_no')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Return Date --}}
                        <div class="col-md-4 mb-3">
                            <label for="return_date" class="form-label">
                                Return Date
                            </label>

                            <input type="text" name="return_date" id="return_date" class="form-control" value="{{ old('return_date', date('d/m/Y')) }}" required>

                            @error('return_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Customer --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                Customer
                            </label>

                            <input type="text" id="customer_name" class="form-control" readonly placeholder="Customer will appear here">

                            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}">
                        </div>

                    </div>

                    {{-- Sale Items --}}
                    <div class="table-responsive mt-3">

                        <table class="table table-striped align-middle" id="returnItemsTable">

                            <thead>
                                <tr>
                                    <th width="40">
                                        #
                                    </th>

                                    <th>
                                        Product
                                    </th>

                                    <th width="120">
                                        Sold Qty
                                    </th>

                                    <th width="140">
                                        Returned Qty
                                    </th>

                                    <th width="140">
                                        Price
                                    </th>

                                    <th width="150">
                                        Return Amount
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="returnItemsBody">

                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Enter invoice number to load sale items.
                                    </td>
                                </tr>

                            </tbody>

                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">
                                        Total Return Amount
                                    </th>

                                    <th>
                                        <input type="text" id="return_total" name="return_total" class="form-control" value="0.00" readonly>
                                    </th>
                                </tr>
                            </tfoot>

                        </table>

                    </div>

                    {{-- Return Reason --}}
                    <div class="row mt-3">

                        <div class="col-md-6">
                            <label for="reason" class="form-label">
                                Return Reason
                            </label>

                            <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Enter return reason">{{ old('reason') }}</textarea>

                            @error('reason')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Refund Type --}}
                        <div class="col-md-6">

                            <label class="form-label">
                                Refund Type
                            </label>

                            <select name="refund_type" id="refund_type" class="form-select" required>
                                <option value="">Select Refund Type</option>
                                <option value="cash" {{ old('refund_type') == 'cash' ? 'selected' : '' }}>
                                    Cash
                                </option>

                                <option value="credit" {{ old('refund_type') == 'credit' ? 'selected' : '' }}>
                                    Customer Credit
                                </option>

                                <option value="original" {{ old('refund_type') == 'original' ? 'selected' : '' }}>
                                    Original Payment Method
                                </option>
                            </select>

                            @error('refund_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                        </div>

                    </div>

                    {{-- Buttons --}}
                    <div class="mt-4 text-end">

                        <a href="{{ route('admin.sales-return.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary" id="submitReturn">
                            Save Customer Return
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>

@endsection


@section('script')

    <script>
        $(document).ready(function () {

            /*
            |--------------------------------------------------------------------------
            | Load Sale By Invoice
            |--------------------------------------------------------------------------
            */

            $('#invoice_no').on('change', function () {

                let invoiceNo = $(this).val().trim();

                if (!invoiceNo) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.sales-return.sale-details') }}",
                    type: "GET",
                    data: {
                        invoice_no: invoiceNo
                    },

                    beforeSend: function () {

                        $('#returnItemsBody').html(`
                                            <tr>
                                                <td colspan="6" class="text-center">
                                                    Loading...
                                                </td>
                                            </tr>
                                        `);

                    },

                    success: function (response) {

                        if (!response.success) {

                            Swal.fire({
                                icon: 'warning',
                                title: 'Sale Not Found',
                                text: response.message
                            });

                            $('#returnItemsBody').html(`
                                                <tr>
                                                    <td colspan="6" class="text-center text-danger">
                                                        ${response.message}
                                                    </td>
                                                </tr>
                                            `);

                            return;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Customer
                        |--------------------------------------------------------------------------
                        */

                        $('#customer_id').val(response.sale.customer_id ?? '');

                        $('#customer_name').val(
                            response.sale.customer_name ?? 'Walk-in Customer'
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | Items
                        |--------------------------------------------------------------------------
                        */

                        let html = '';

                        if (!response.items || response.items.length === 0) {

                            html = `
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">
                                                        No returnable items found.
                                                    </td>
                                                </tr>
                                            `;

                        } else {

                            $.each(response.items, function (index, item) {

                                html += `
                                                    <tr>

                                                        <td>
                                                            ${index + 1}
                                                        </td>

                                                        <td>
                                                            ${item.product_name}

                                                            <input
                                                                type="hidden"
                                                                name="items[${index}][sale_item_id]"
                                                                value="${item.sale_item_id}"
                                                            >
                                                        </td>

                                                        <td>
                                                            ${item.sold_qty}
                                                        </td>

                                                        <td>

                                                            <input
                                                                type="number"
                                                                class="form-control returnQty"
                                                                name="items[${index}][quantity]"
                                                                min="0"
                                                                max="${item.returnable_qty}"
                                                                value="0"
                                                                data-price="${item.price}"
                                                            >

                                                            <small class="text-muted">
                                                                Returnable: ${item.returnable_qty}
                                                            </small>

                                                        </td>

                                                        <td>
                                                            ${item.price}
                                                        </td>

                                                        <td>
                                                            <span class="returnAmount">
                                                                0.00
                                                            </span>
                                                        </td>

                                                    </tr>
                                                `;

                            });

                        }

                        $('#returnItemsBody').html(html);

                        calculateReturnTotal();
                    },

                    error: function (xhr) {

                        let message = 'Unable to load sale details.';

                        if (
                            xhr.responseJSON &&
                            xhr.responseJSON.message
                        ) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });

                        $('#returnItemsBody').html(`
                                            <tr>
                                                <td colspan="6" class="text-center text-danger">
                                                    ${message}
                                                </td>
                                            </tr>
                                        `);

                    }

                });

            });


            /*
            |--------------------------------------------------------------------------
            | Calculate Return Amount
            |--------------------------------------------------------------------------
            */

            $(document).on('input', '.returnQty', function () {

                let qty = parseFloat($(this).val()) || 0;

                let max = parseFloat($(this).attr('max')) || 0;

                if (qty < 0) {
                    qty = 0;
                    $(this).val(0);
                }

                if (qty > max) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Quantity',
                        text: 'Return quantity cannot exceed returnable quantity.'
                    });

                    qty = max;

                    $(this).val(max);
                }

                let price = parseFloat($(this).data('price')) || 0;

                let amount = qty * price;

                $(this)
                    .closest('tr')
                    .find('.returnAmount')
                    .text(amount.toFixed(2));

                calculateReturnTotal();

            });


            /*
            |--------------------------------------------------------------------------
            | Total
            |--------------------------------------------------------------------------
            */

            function calculateReturnTotal() {

                let total = 0;

                $('.returnQty').each(function () {

                    let qty = parseFloat($(this).val()) || 0;

                    let price = parseFloat($(this).data('price')) || 0;

                    total += qty * price;

                });

                $('#return_total').val(total.toFixed(2));

            }


            /*
            |--------------------------------------------------------------------------
            | Submit Validation
            |--------------------------------------------------------------------------
            */

            $('#salesReturnForm').on('submit', function (e) {

                let totalQty = 0;

                $('.returnQty').each(function () {

                    totalQty += parseFloat($(this).val()) || 0;

                });

                if (totalQty <= 0) {

                    e.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: 'No Items Selected',
                        text: 'Please enter return quantity for at least one item.'
                    });

                    return false;
                }

            });

        });
    </script>

@endsection