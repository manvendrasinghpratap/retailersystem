@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? '' }} | {{ $breadcrumb['route1Title'] ?? '' }}
@endsection

@section('content')
@include('backend.components.breadcrumb')

<div class="row">
    <div class="col-lg-12">
        <div class="card">

            <div class="card-header">
                <h4 class="card-title">
                    {{ request()->route()->getName() == 'admin.requisitions.create' 
                        ? $breadcrumb['route2Title'] 
                        : ($breadcrumb['route3Title'] ?? '') }}
                </h4>
            </div>

            <div class="card-body">

                <form method="POST" action="{{ route('admin.requisitions.store') }}" id="requisitionForm">
                    @csrf

                    <div class="row">

                        <!-- FROM WAREHOUSE -->
                        <x-select-dropdown 
                            name="from_warehouse_id"
                            label="From Warehouse"
                            :options="$warehouses"
                            required
                        />

                        <!-- TO WAREHOUSE -->
                        <x-select-dropdown 
                            name="to_warehouse_id"
                            label="To Warehouse"
                            :options="$warehouses"
                            required
                        />

                        <!-- DATE -->
                        <x-text-input 
                            name="date"
                            label="Date"
                            type="text"
                            class="flatdatepickr"
                            value="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}"
                            required
                        />

                    </div>

                    <!-- ITEMS TABLE -->
                    <table class="table table-bordered mt-3" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="40%">Product</th>
                                <th>Available Stock</th>
                                <th>Qty</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <!-- ADD BUTTON -->
                    <div class="text-center mt-2">
                        <button type="button" class="btn btn-success" id="addRow">
                            + Add Item
                        </button>
                    </div>

                    <!-- TOTAL -->
                    <div class="text-end mt-3">
                        <h5>Total Qty: <span id="totalQty">0</span></h5>
                    </div>

                    <div class="card-footer text-end">
                        <a href="{{ route('admin.requisitions.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button class="btn btn-primary">
                            Save Requisition
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

<script>
let rowIndex = 0;

// ========================
// ADD ROW
// ========================
function addRow() {

    let fromWarehouse = $('select[name="from_warehouse_id"]').val();

    if (!fromWarehouse) {
        Swal.fire('Select From Warehouse first');
        return;
    }

    let row = `
    <tr>
        <td>
            <select name="items[${rowIndex}][product_id]" class="form-control product"></select>
        </td>

        <td class="stock text-primary fw-bold text-center">0</td>

        <td>
            <input type="number" name="items[${rowIndex}][qty]" class="form-control qty" min="1" disabled>
        </td>

        <td>
            <button type="button" class="btn btn-danger removeRow">x</button>
        </td>
    </tr>
    `;

    $('#itemsTable tbody').append(row);

    let newRow = $('#itemsTable tbody tr:last');

    initSelect2(newRow.find('.product'));

    rowIndex++;
}

$('#addRow').click(addRow);
addRow();

// ========================
// SELECT2 PRODUCT
// ========================
function initSelect2(el) {

    el.select2({
        placeholder: 'Search Product',
        width: '100%',
        ajax: {
            url: "{{ route('admin.products.search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    warehouse_id: $('select[name="from_warehouse_id"]').val()
                };
            },
            processResults: function (data) {
                return { results: data };
            }
        }
    });
}

// ========================
// FETCH STOCK
// ========================
function fetchStock(row) {

    let product_id = row.find('.product').val();
    let warehouse_id = $('select[name="from_warehouse_id"]').val();

    if (!product_id || !warehouse_id) return;

    $.get("{{ route('admin.stock_returns.stock.check') }}", {
        product_id, warehouse_id
    }, function (res) {

        let stock = parseFloat(res.stock) || 0;

        row.find('.stock').text(stock);

        let qtyInput = row.find('.qty');

        if (stock <= 0) {
            qtyInput.val('').prop('disabled', true);
            Swal.fire('No stock available');
        } else {
            qtyInput.prop('disabled', false);
            qtyInput.attr('max', stock);
            qtyInput.val(1);
        }

        calculateTotal();
    });
}

// ========================
// PRODUCT CHANGE
// ========================
$(document).on('change', '.product', function () {

    let row = $(this).closest('tr');

    // prevent duplicate
    let product_id = $(this).val();
    let duplicate = false;

    $('.product').not(this).each(function () {
        if ($(this).val() == product_id) duplicate = true;
    });

    if (duplicate) {
        Swal.fire('Product already added');
        $(this).val(null).trigger('change');
        return;
    }

    fetchStock(row);
});

// ========================
// CALCULATE TOTAL
// ========================
function calculateTotal() {

    let total = 0;

    $('.qty').each(function () {
        total += parseFloat($(this).val()) || 0;
    });

    $('#totalQty').text(total);
}

// ========================
// QTY CHANGE
// ========================
$(document).on('input', '.qty', function () {

    let max = parseFloat($(this).attr('max')) || 0;
    let val = parseFloat($(this).val()) || 0;

    if (val > max) {
        $(this).val(max);
        Swal.fire('Max stock: ' + max);
    }

    if (val <= 0) {
        $(this).val('');
    }

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
// RESET ON WAREHOUSE CHANGE
// ========================
$('select[name="from_warehouse_id"], select[name="to_warehouse_id"]').on('change', function () {

    if ($('select[name="from_warehouse_id"]').val() == $('select[name="to_warehouse_id"]').val()) {
        Swal.fire('From & To warehouse cannot be same');
        $(this).val('').trigger('change');
        return;
    }

    $('#itemsTable tbody').empty();
    rowIndex = 0;
    addRow();
});

// ========================
// FORM VALIDATION
// ========================
$('#requisitionForm').on('submit', function(e){

    e.preventDefault();

    let valid = true;

    $('#itemsTable tbody tr').each(function () {

        let product = $(this).find('.product').val();
        let qty = parseFloat($(this).find('.qty').val()) || 0;

        if (!product || qty <= 0) valid = false;
    });

    if (!valid) {
        Swal.fire('Fill all item details');
        return;
    }

    Swal.fire({
        title: 'Confirm Requisition?',
        icon: 'warning',
        showCancelButton: true
    }).then((res)=>{
        if(res.isConfirmed){
            this.submit();
        }
    });

});
</script>

@endsection