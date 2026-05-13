@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}}
@endsection
@section('content')
    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ request()->route()->getName() == 'admin.purchases.create' ? $breadcrumb['route2Title'] : ($breadcrumb['title'])}}
                    </h4>
                </div>
                <div class="card-body">
                        <form method="POST" action="{{ route('admin.purchases.store') }}" id="purchaseForm">
                            @csrf
                            <div class="row"> 
                                <x-select-dropdown name="vendor_id" label="{{ __('translation.vendor')}}" :options="$vendors" :selected="request()->get('vendor_id') ?? ''" class="vendor" mainrows="4" />
                                <x-select-dropdown name="warehouse_id" label="{{ __('translation.warehouse')}}" :options="$warehouses" :selected="request()->get('warehouse_id') ?? ''" class="warehouse" mainrows="4" />
                                <div class="col-md-4">
                                    <label class="form-label d-block">&nbsp;</label>
                                    <x-href-input action="add" name="add-master-item" label="Add New Master Item" class="addMasterItem" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#masterItemModal"/>
                                </div>
                            </div>
                            <div class="position-relative">
                                <table class="table table-hover align-middle" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('translation.product') }}</th>
                                            <th>{{ __('translation.quantity') }}</th>  
                                            <th>@lang('translation.currency') {{ __('translation.price') }}</th>
                                            <th>@lang('translation.currency') {{ __('translation.total') }}</th>
                                            <th width="5%">{{ __('translation.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <!-- ADD ROW BUTTON -->
                                <div class="text-center mt-2">
                                    <button type="button" class="btn btn-success px-4" id="addRow">
                                        <i class="mdi mdi-plus"></i> {{ __('translation.add_item') }}
                                    </button>
                                </div>
                            </div>
                            <div class="text-end">
                                <h4>{{ __('translation.total')}}: @lang('translation.currency')<span id="grandTotal">0</span></h4>
                                <input type="hidden" name="total" id="totalInput">
                            </div>
                            <div class="card-footer form-group center">
                                <div class="d-flex gap-2 dflex">
                                    <button type="submit" class="btn btn-primary">{{ __('translation.save_purchase') }}</button>
                                    <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">{{ __('translation.cancel') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>

let rowIndex = 0;

// ========================
// INIT SELECT2 PRODUCT
// ========================
function initSelect2(element) {
    element.select2({
        placeholder: 'Search Master Item',
        width: '100%',
        ajax: {
            url: "{{ route('admin.master_items.search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
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

        <td>
            <input type="number" name="items[${rowIndex}][qty]" class="form-control qty" value="1" min="1">
        </td>

        <td>
            <input type="number" name="items[${rowIndex}][price]" class="form-control price" value="0" min="0.01" step="0.01">
        </td>

        <td class="rowTotal">0.00</td>

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
// PREVENT DUPLICATE PRODUCT
// ========================
$(document).on('change', '.selectProduct', function () {

    let selected = $(this).val();

    let duplicate = false;

    $('.selectProduct').not(this).each(function () {
        if ($(this).val() == selected && selected != '') {
            duplicate = true;
        }
    });

    if (duplicate) {
        Swal.fire('Duplicate', 'Product already selected', 'warning');
        $(this).val(null).trigger('change');
    }
});

// ========================
// CALCULATE TOTAL
// ========================
function calculateTotal() {

    let total = 0;

    $('#itemsTable tbody tr').each(function () {

        let qty = parseFloat($(this).find('.qty').val()) || 0;
        let price = parseFloat($(this).find('.price').val()) || 0;

        let rowTotal = qty * price;

        $(this).find('.rowTotal').text(rowTotal.toFixed(2));

        total += rowTotal;
    });

    $('#grandTotal').text(total.toFixed(2));
    $('#totalInput').val(total);
}

$(document).on('input', '.qty, .price', calculateTotal);

// ========================
// REMOVE ROW
// ========================
$(document).on('click', '.removeRow', function () {
    $(this).closest('tr').remove();
    calculateTotal();
});

// ========================
// FORM VALIDATION
// ========================
$('#purchaseForm').on('submit', function (e) {

    e.preventDefault();

    let form = this;

    let vendor = $('select[name="vendor_id"]').val();
    let warehouse = $('select[name="warehouse_id"]').val();

    if (!vendor) {
        Swal.fire('Error', 'Select vendor', 'error');
        return;
    }

    if (!warehouse) {
        Swal.fire('Error', 'Select warehouse', 'error');
        return;
    }

    if ($('#itemsTable tbody tr').length === 0) {
        Swal.fire('Error', 'Add at least one product', 'error');
        return;
    }

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
        Swal.fire('Error', 'Fill all fields correctly', 'error');
        return;
    }

    Swal.fire({
        title: 'Save Purchase?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes'
    }).then((res) => {
        if (res.isConfirmed) {
            form.submit();
        }
    });
});

// ========================
// CANCEL CONFIRM
// ========================
$('#cancelBtn').on('click', function (e) {

    e.preventDefault();

    let url = $(this).attr('href');

    Swal.fire({
        title: 'Are you sure?',
        text: "Unsaved data will be lost!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
});

// ========================
// INPUT UX FIXES
// ========================
document.addEventListener('focus', function (e) {
    if (e.target.classList.contains('price') && e.target.value == 0) {
        e.target.value = '';
    }
}, true);

document.addEventListener('blur', function (e) {
    if (e.target.classList.contains('price') && e.target.value.trim() === '') {
        e.target.value = 0;
    }
}, true);

// Allow empty while typing
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('qty')) {

        let val = e.target.value;

        // allow empty (user typing)
        if (val === '') return;

        if (parseFloat(val) < 1) {
            e.target.value = 1;
        }
    }
});

// Final validation on blur (when user leaves field)
document.addEventListener('blur', function (e) {
    if (e.target.classList.contains('qty')) {

        let val = parseFloat(e.target.value);

        if (!val || val < 1) {
            e.target.value = 1;
        }
    }
}, true);

</script>
@endsection
