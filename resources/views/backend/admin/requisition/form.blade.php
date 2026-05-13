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
                        ? $breadcrumb['route1Title']
                        : ($breadcrumb['route3Title'] ?? '') }}
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.requisitions.store') }}" id="requisitionForm" novalidate> 
                    @csrf
                    <div class="row">
                        {{-- FROM WAREHOUSE --}}
                        <x-select-dropdown name="from_warehouse_id" label="{{ __('translation.from_warehouse') }}" :options="$warehouses" required id="from_warehouse_id" class="warehouse"/>
                        {{-- FOR STORE --}}
                        <x-select-dropdown name="for_store_id" label="{{ __('translation.for_store') }}" :options="$stores" required id="for_store_id" class="store"/>
                        {{-- DATE --}}
                        <x-text-input name="date" label="{{ __('translation.date') }}" type="text" class="flatdatepickr" value="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" required id="date"/>
                    </div>

                    {{-- ITEMS TABLE --}}
                    <table class="table table-bordered mt-3" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="40%">{{ __('translation.product') }}</th>
                                <th class="text-center">{{ __('translation.stock') }}</th>
                                <th>{{ __('translation.quantity') }}</th>
                                <th width="5%">{{ __('translation.action') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    {{-- ADD BUTTON --}}
                    <div class="text-center mt-2">
                        <button type="button" class="btn btn-success px-4" id="addRow">
                            <i class="mdi mdi-plus"></i>
                            {{ __('translation.add_item') }}
                        </button>
                    </div>

                    {{-- TOTAL --}}
                    <div class="text-end mt-3">
                        <h5>
                            {{ __('translation.total_quantity') }} :
                            <span id="totalQty">0</span>
                        </h5>
                    </div>
                        <div class="card-footer form-group center">
                            <div class="d-flex gap-2 dflex">
                                <button type="submit" class="btn btn-primary">{{ __('translation.save') }}</button>
                                <a href="{{ route('admin.requisitions.index') }}" class="btn btn-secondary">{{ __('translation.cancel') }}</a>
                            </div>
                        </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection

@section('script')

<script>

validateSelect2Form('requisitionForm', [
    'from_warehouse_id',
    'for_store_id'
]);

let rowIndex = 0;

// ========================
// INIT SELECT2
// ========================
function initSelect2(element)
{
    element.select2({
        placeholder: 'Select Product',
        width: '100%',
        ajax: {
            url: "{{ route('admin.products.search') }}",
            dataType: 'json',
            delay: 250,

            data: function(params)
            {
                let warehouse_id = $('select[name="from_warehouse_id"]').val();

                if (!warehouse_id) {
                    return false;
                }

                return {
                    q: params.term,
                    warehouse_id: warehouse_id
                };
            },

            processResults: function(data)
            {
                return {
                    results: data
                };
            }
        }
    });
}

// ========================
// RESET ITEMS
// ========================
function resetItems()
{
    $('#itemsTable tbody').empty();

    rowIndex = 0;

    addRow();

    calculateTotal();
}

// ========================
// ADD ROW
// ========================
function addRow()
{
    let fromWarehouse = $('select[name="from_warehouse_id"]').val();

    if (!fromWarehouse) {
        Swal.fire(
            'Error',
            'Please select from warehouse first',
            'error'
        );
        return;
    }

    let row = `
        <tr>

            <td>
                <select
                    name="items[${rowIndex}][master_item_id]"
                    class="form-control selectProduct"
                    required>
                </select>
            </td>

            <td class="stock text-center text-primary fw-bold">
                0
            </td>

            <td>
                <input
                    type="number"
                    name="items[${rowIndex}][qty]"
                    class="form-control qty"
                    min="1"
                    step="1"
                    disabled
                    required>
            </td>

            <td>
                <button
                    type="button"
                    class="btn btn-danger removeRow">
                    x
                </button>
            </td>

        </tr>
    `;

    $('#itemsTable tbody').append(row);

    let newRow = $('#itemsTable tbody tr:last');

    initSelect2(newRow.find('.selectProduct'));

    rowIndex++;
}

$('#addRow').click(addRow);

addRow();

// ========================
// FETCH STOCK
// ========================
function fetchStock(row)
{
    let master_item_id = row.find('.selectProduct').val();

    let warehouse_id = $('select[name="from_warehouse_id"]').val();

    if (!master_item_id || !warehouse_id) {
        return;
    }

    $.get("{{ route('admin.stock_returns.stock.check') }}", {
        master_item_id: master_item_id,
        warehouse_id: warehouse_id
    }, function(res) {

        let stock = parseFloat(res.stock) || 0;

        row.find('.stock').text(stock);

        let qtyInput = row.find('.qty');

        if (stock <= 0) {

            qtyInput.val('').prop('disabled', true);

            Swal.fire(
                'Warning',
                'No stock available',
                'warning'
            );

        } else {

            qtyInput.prop('disabled', false);

            qtyInput.attr('max', stock);

            qtyInput.val(1);

            calculateTotal();
        }
    });
}

// ========================
// PRODUCT CHANGE
// ========================
$(document).on('change', '.selectProduct', function () {

    let row = $(this).closest('tr');

    let master_item_id = $(this).val();

    // Prevent duplicate
    let duplicate = false;

    $('.selectProduct').not(this).each(function () {

        if ($(this).val() == master_item_id && master_item_id !== '') {
            duplicate = true;
        }
    });

    if (duplicate) {

        Swal.fire(
            'Warning',
            'Product already added',
            'warning'
        );

        $(this).val(null).trigger('change');

        return;
    }

    fetchStock(row);
});

// ========================
// CALCULATE TOTAL
// ========================
function calculateTotal()
{
    let total = 0;

    $('.qty').each(function () {

        total += parseFloat($(this).val()) || 0;
    });

    $('#totalQty').text(total);
}

// ========================
// QTY VALIDATION
// ========================
$(document).on('input', '.qty', function () {

    let qty = parseFloat($(this).val()) || 0;

    let max = parseFloat($(this).attr('max')) || 0;

    if (qty > max) {

        $(this).val(max);

        Swal.fire(
            'Warning',
            'Max stock: ' + max,
            'warning'
        );
    }

    if (qty <= 0) {
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
// WAREHOUSE CHANGE
// ========================
$('select[name="from_warehouse_id"], select[name="to_warehouse_id"]').on('change', function () {

    let fromWarehouse = $('select[name="from_warehouse_id"]').val();

    let toWarehouse = $('select[name="to_warehouse_id"]').val();

    if (fromWarehouse && toWarehouse && fromWarehouse == toWarehouse) {

        Swal.fire(
            'Error',
            'From & To warehouse cannot be same',
            'error'
        );

        $(this).val('').trigger('change');

        return;
    }

    resetItems();
});

// ========================
// FORM VALIDATION
// ========================
$('#requisitionForm').on('submit', function(e){

    e.preventDefault();

    let fromWarehouse = $('select[name="from_warehouse_id"]').val();

    let toWarehouse = $('select[name="for_store_id"]').val();

    if (!fromWarehouse || !toWarehouse) {

        Swal.fire(
            'Error',
            'Please select warehouses',
            'error'
        );

        return;
    }

    let valid = true;

    $('#itemsTable tbody tr').each(function () {

        let product = $(this).find('.selectProduct').val();

        let qty = parseFloat($(this).find('.qty').val()) || 0;

        if (!product || qty <= 0) {
            valid = false;
        }
    });

    if (!valid) {

        Swal.fire(
            'Error',
            'Fill all item details correctly',
            'error'
        );

        return;
    }

    if ($('#itemsTable tbody tr').length === 0) {

        Swal.fire(
            'Error',
            'Add at least one item',
            'error'
        );

        return;
    }

    Swal.fire({
        title: 'Confirm Requisition?',
        icon: 'warning',
        showCancelButton: true
    }).then((res) => {

        if (res.isConfirmed) {
            this.submit();
        }
    });
});

</script>

@endsection