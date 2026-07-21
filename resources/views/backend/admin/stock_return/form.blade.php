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
                        {{ $breadcrumb['route1Title'] }}
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.stock_returns.store') }}" id="returnForm" novalidate>
                        @csrf
                        <div class="row">
                            <x-select-dropdown name="vendor_id" label="{{ __('translation.vendor') }}" :options="$vendors" id='vendor_id' mainrows='4' class="supplier" required />
                            <x-select-dropdown name="warehouse_id" label="{{ __('translation.warehouse') }}" :options="$warehouses" id='warehouse_id' mainrows='4' class="warehouse" required />
                            <x-text-input name="return_date" label="{{ __('translation.return_date') }}" type="text" required class="flatdatepickr" value="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" />
                        </div>
                        <table class="table table-bordered mt-3" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="30%">{{ __('translation.product') }}</th>
                                    <th class="text-center">{{ __('translation.stock') }}</th>
                                    <th width="7%">{{ __('translation.quantity') }}</th>
                                    <th>@lang('translation.currency') {{ __('translation.price') }}</th>
                                    <th>@lang('translation.currency') {{ __('translation.total') }}</th>
                                    <th>{{ __('translation.reason') }}</th>
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
                        <div class="text-end">
                            <h4>@lang('translation.currency') <span id="grandTotal">0.00</span></h4>
                            <input type="hidden" name="total" id="totalInput">
                        </div>
                        <div class="card-footer form-group center">
                            <div class="d-flex gap-2 dflex">
                                <button type="submit" class="btn btn-primary">{{ __('translation.save_return') }}</button>
                                <a href="{{ route('admin.stock_returns.index') }}" class="btn btn-secondary">{{ __('translation.cancel') }}</a>
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
        validateSelect2Form('returnForm', ['vendor_id', 'warehouse_id']);
        let rowIndex = 0;

        // ========================
        // INIT SELECT2
        // ========================
        function initSelect2(element) {

            element.select2({
                placeholder: 'Select Product',
                width: '100%',
                ajax: {
                    url: "{{ route('admin.products.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {

                        let warehouse_id = $('select[name="warehouse_id"]').val();

                        if (!warehouse_id) return false;

                        return {
                            q: params.term,
                            warehouse_id: warehouse_id
                        };
                    },
                    processResults: function (data) {
                        return { results: data };
                    }
                }
            });
        }

        // ========================
        // RESET ITEMS
        // ========================
        function resetFormItems() {
            $('#itemsTable tbody').empty();
            rowIndex = 0;
            addRow();
        }

        // ========================
        // ADD ROW
        // ========================
        function addRow() {

            let warehouse_id = $('select[name="warehouse_id"]').val();

            if (!warehouse_id) {
                Swal.fire('Select Warehouse First');
                return;
            }

            let row = `
                            <tr>
                                <td><select name="items[${rowIndex}][master_item_id]" class="form-control selectProduct"></select></td>
                                <td class="stock text-center text-primary fw-bold">0</td>
                                <td><input type="number" name="items[${rowIndex}][qty]" class="form-control qty" min="1" step="1" disabled></td>
                                <td><input type="number" name="items[${rowIndex}][price]" class="form-control price" min="0.01" step="0.01" readonly></td>
                                <td class="rowTotal text-end">0.00</td>
                                <td><input type="text" name="items[${rowIndex}][reason]" class="form-control"></td>
                                <td><button type="button" class="btn btn-danger removeRow">x</button></td>
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
        // FETCH STOCK
        // ========================
        function fetchStock(row) {

            let master_item_id = row.find('.selectProduct').val();
            let warehouse_id = $('select[name="warehouse_id"]').val();

            if (!master_item_id || !warehouse_id) return;

            $.get("{{ route('admin.stock_returns.stock.check') }}", {
                master_item_id,
                warehouse_id
            }, function (res) {

                let stock = parseFloat(res.stock) || 0;

                row.find('.stock').text(stock);

                let qtyInput = row.find('.qty');

                if (stock <= 0) {
                    qtyInput.val('').prop('disabled', true);
                    Swal.fire('No Stock Available');
                } else {
                    qtyInput.prop('disabled', false);
                    qtyInput.attr('max', stock);
                    qtyInput.val(1).trigger('input');
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
                if ($(this).val() == master_item_id && master_item_id != '') {
                    duplicate = true;
                }
            });

            if (duplicate) {
                Swal.fire('Duplicate Product');
                $(this).val(null).trigger('change');
                return;
            }

            fetchStock(row);

            // Fetch price
            $.get("{{ route('admin.products.lastPrice') }}", {
                master_item_id: master_item_id,
                vendor_id: $('select[name="vendor_id"]').val(),
                warehouse_id: $('select[name="warehouse_id"]').val()
            }, function (res) {
                row.find('.price').val(res.price).trigger('input');
            });

        });

        // ========================
        // WAREHOUSE / VENDOR CHANGE
        // ========================
        $('select[name="warehouse_id"], select[name="vendor_id"]').on('change', function () {
            resetFormItems();
        });

        // ========================
        // QTY VALIDATION
        // ========================
        $(document).on('input', '.qty', function () {

            let qty = parseFloat($(this).val()) || 0;
            let max = parseFloat($(this).attr('max')) || 0;

            if (qty > max) {
                $(this).val(max);
                Swal.fire('Max stock: ' + max);
            }

            if (qty <= 0) {
                $(this).val('');
            }

            calculateTotal();
        });

        // ========================
        // FORM VALIDATION
        // ========================
        $('#returnForm').on('submit', function (e) {

            e.preventDefault();

            let vendor = $('select[name="vendor_id"]').val();
            let warehouse = $('select[name="warehouse_id"]').val();

            if (!vendor) {
                Swal.fire('Error', 'Please select vendor', 'error');
                return;
            }

            if (!warehouse) {
                Swal.fire('Error', 'Please select warehouse', 'error');
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
                Swal.fire('Error', 'Fill all item fields correctly', 'error');
                return;
            }

            if ($('#itemsTable tbody tr').length === 0) {
                Swal.fire('Error', 'Add at least one item', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Return?',
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