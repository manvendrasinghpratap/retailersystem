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
                                </div>
                                {{-- PRODUCTS TABLE --}}
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

                            <div class="card-footer text-end">
                                <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary" id="cancelBtn">{{ __('translation.cancel') }}</a>
                                <button type="submit" class="btn btn-primary" id="saveBtn">{{ __('translation.save_purchase') }}</button>
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
    let products = @json($products);
let rowIndex = 0;

    function addRow() {
        let row = `
                <tr>
                    <td>
                        <select name="items[${rowIndex}][product_id]" class="form-control product">
                            ${Object.entries(products).map(([id,name]) => `<option value="${id}">${name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${rowIndex}][qty]" class="form-control qty" value="1" min="1" step="1">
                    </td>
                    <td>
                        <input type="number" name="items[${rowIndex}][price]" class="form-control price" value="0" min="0">
                    </td>
                    <td class="rowTotal">0</td>
                    <td>
                        <button type="button" class="btn btn-danger removeRow">x</button>
                    </td>
                </tr>`;
        $('#itemsTable tbody').append(row);
        rowIndex++;
    }

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

    $(document).on('keyup change', '.qty, .price', calculateTotal);

    $(document).on('click', '.removeRow', function () {
        $(this).closest('tr').remove();
        calculateTotal();
    });

    $('#addRow').click(addRow);

    // default row
    addRow();
    /////Multiple select2 fields in one form
    validateSelect2Form('purchaseForm', ['vendor_id','warehouse_id']);

    $('#cancelBtn').on('click', function (e) {
        e.preventDefault();

        let url = $(this).attr('href');

        Swal.fire({
            title: 'Are you sure?',
            text: "Unsaved data will be lost!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            confirmButtonText: 'Yes, leave page',
            cancelButtonText: 'Stay here'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
    $('#purchaseForm').on('submit', function (e) {
    e.preventDefault();

    let form = this;

    // ✅ HTML validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    let hasItems = $('#itemsTable tbody tr').length > 0;

    if (!hasItems) {
        Swal.fire({
            icon: 'warning',
            title: 'No Items',
            text: 'Please add at least one product'
        });
        return;
    }

    // ✅ NEW: Price validation (IMPORTANT)
    let invalidPrice = false;

    $('#itemsTable tbody tr').each(function () {
        let price = parseFloat($(this).find('.price').val()) || 0;

        if (price <= 0) {
            invalidPrice = true;
            $(this).find('.price').addClass('is-invalid'); // highlight
        } else {
            $(this).find('.price').removeClass('is-invalid');
        }
    });

    if (invalidPrice) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Price',
            text: 'Price must be greater than 0'
        });
        return;
    }

    // ✅ SweetAlert confirm
    Swal.fire({
        title: 'Save Purchase?',
        text: "Please confirm before saving",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Yes, save it',
        cancelButtonText: 'Review again'
    }).then((result) => {

        if (result.isConfirmed) {

            Swal.fire({
                title: 'Saving...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            form.submit();
        }
    });
});
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

document.addEventListener('input', function (e) {
    if (e.target.classList.contains('qty')) {
        if (e.target.value < 1) {
            e.target.value = 1;
        }
    }
});
</script>
@endsection
