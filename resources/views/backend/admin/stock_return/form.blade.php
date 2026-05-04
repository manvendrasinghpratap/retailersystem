@extends('backend.layouts.master-horizontal')

@section('content')
@include('backend.components.breadcrumb')

<!-- SELECT2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="card">

    <div class="card-header">
        <h4>Create Stock Return</h4>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('admin.stock_returns.store') }}" id="returnForm">
            @csrf

            <div class="row">
                <x-select-dropdown name="vendor_id" label="Vendor" :options="$vendors" required />
                <x-select-dropdown name="warehouse_id" label="Warehouse" :options="$warehouses" required />

                <x-text-input 
                    name="return_date" 
                    label="Return Date" 
                    type="text" 
                    required 
                    class="flatdatepickr" 
                    value="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" 
                />
            </div>

            <table class="table table-bordered mt-3" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th width="30%">Product</th>
                        <th class="text-center">Stock</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th width="5%">
                            <button type="button" class="btn btn-success btn-sm" id="addRow">+</button>
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="text-end">
                <h4>Total: <span id="grandTotal">0</span></h4>
                <input type="hidden" name="total" id="totalInput">
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('admin.stock_returns.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary">Submit Return</button>
            </div>

        </form>
    </div>
</div>
@endsection

@section('script')

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>

let rowIndex = 0;

// ========================
// INIT SELECT2
// ========================
function initSelect2(element) {
    element.select2({
        placeholder: 'Search Product',
        width: '100%',
        ajax: {
            url: "{{ route('admin.products.search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data };
            }
        }
    });
}

// ========================
// ADD ROW
// ========================
function addRow() {

    let row = `
    <tr>
        <td>
            <select name="items[${rowIndex}][product_id]" class="form-control selectProduct"></select>
        </td>

        <td class="stock text-center text-primary fw-bold">0</td>

        <td>
            <input type="number" name="items[${rowIndex}][qty]" class="form-control qty onlydecimal" min="1" step="1" disabled>
        </td>

        <td>
            <input type="number" name="items[${rowIndex}][price]" class="form-control price onlydecimal" min="0.01" step="0.01">
        </td>

        <td class="rowTotal text-end">0</td>

        <td>
            <button type="button" class="btn btn-danger removeRow">x</button>
        </td>
    </tr>`;

    $('#itemsTable tbody').append(row);

    let newRow = $('#itemsTable tbody tr:last');

    initSelect2(newRow.find('.selectProduct'));

    rowIndex++;
}

$('#addRow').click(addRow);
addRow();

// ========================
// CALCULATE TOTAL
// ========================
function calculateTotal() {

    let total = 0;

    $('#itemsTable tbody tr').each(function () {

        let qty = parseFloat($(this).find('.qty').val());
        let price = parseFloat($(this).find('.price').val());

        qty = isNaN(qty) ? 0 : qty;
        price = isNaN(price) ? 0 : price;

        let rowTotal = qty * price;

        $(this).find('.rowTotal').text(rowTotal.toFixed(2));
        total += rowTotal;
    });

    $('#grandTotal').text(total.toFixed(2));
    $('#totalInput').val(total);
}

$(document).on('input', '.qty, .price', function () {
    calculateTotal();
});

// ========================
// REMOVE ROW
// ========================
$(document).on('click', '.removeRow', function () {
    $(this).closest('tr').remove();
    calculateTotal();
});

// ========================
// FETCH STOCK
// ========================
function fetchStock(row) {

    let product_id = row.find('.selectProduct').val();
    let warehouse_id = $('select[name="warehouse_id"]').val();

    if (!product_id || !warehouse_id) return;

    $.ajax({
        url: "{{ route('admin.stock_returns.stock.check') }}",
        type: "GET",
        data: { product_id, warehouse_id },
        success: function (res) {

            let stock = parseFloat(res.stock) || 0;

            row.find('.stock').text(stock);

            let qtyInput = row.find('.qty');

            if (stock <= 0) {
                qtyInput.val('').prop('disabled', true);
                Swal.fire('No Stock', 'No stock available', 'warning');
            } else {
                qtyInput.prop('disabled', false);
                qtyInput.attr('max', stock);
                qtyInput.val(1); // ✅ default qty
                qtyInput.trigger('input');
            }

            calculateTotal();
        }
    });
}

// ========================
// PRODUCT CHANGE
// ========================
$(document).on('change', '.selectProduct', function () {

    let row = $(this).closest('tr');
    let product_id = $(this).val();

    // prevent duplicate
    let duplicate = false;
    $('.selectProduct').not(this).each(function () {
        if ($(this).val() == product_id && product_id != '') {
            duplicate = true;
        }
    });

    if (duplicate) {
        Swal.fire('Duplicate', 'Product already added', 'warning');
        $(this).val(null).trigger('change');
        return;
    }

    fetchStock(row);

    // fetch last price
    $.ajax({
        url: "{{ route('admin.products.lastPrice') }}",
        type: "GET",
        data: { product_id },
        success: function (res) {

            let priceInput = row.find('.price');

            priceInput.val(res.price);

            // 🔥 trigger calculation
            priceInput.trigger('input');

            calculateTotal();
        }
    });

    // focus qty
    setTimeout(() => {
        row.find('.qty').focus().select();
    }, 200);
});

// ========================
// WAREHOUSE CHANGE
// ========================
$('select[name="warehouse_id"]').on('change', function () {
    $('#itemsTable tbody tr').each(function () {
        fetchStock($(this));
    });
});

// ========================
// QTY VALIDATION
// ========================
$(document).on('input', '.qty', function () {

    let qty = parseFloat($(this).val()) || 0;
    let max = parseFloat($(this).attr('max')) || 0;

    if (!max || max == 0) {
        $(this).val('');
        return;
    }

    if (qty > max) {
        $(this).val(max);

        Swal.fire({
            icon: 'warning',
            title: 'Stock limit exceeded',
            text: 'Max available stock is ' + max
        });
    }

    if (qty <= 0) {
        $(this).val('');
    }

    calculateTotal();
});

// ========================
// FORM VALIDATION
// ========================
$('#returnForm').on('submit', function(e){

    e.preventDefault();

    let valid = true;

    $('#itemsTable tbody tr').each(function () {

        let product = $(this).find('.selectProduct').val();
        let qty = parseFloat($(this).find('.qty').val()) || 0;
        let price = parseFloat($(this).find('.price').val()) || 0;

        if (!product || qty <= 0 || price <= 0) {
            valid = false;
        }
    });

    if (!valid) {
        Swal.fire('Error','Fill all item fields correctly','error');
        return;
    }

    Swal.fire({
        title: 'Confirm Return?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Submit'
    }).then((res)=>{
        if(res.isConfirmed){
            this.submit();
        }
    });
});

</script>
@endsection