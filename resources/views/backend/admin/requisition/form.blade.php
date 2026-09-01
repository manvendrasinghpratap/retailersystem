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
                    <h4 class="card-title">{{ request()->route()->getName() == 'admin.requisitions.create' ? $breadcrumb['route1Title'] : ($breadcrumb['route3Title'] ?? '') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.requisitions.store') }}" id="requisitionForm" novalidate>
                        @csrf
                        {{-- Header Information --}}
                        <div class="row">
                            <x-select-dropdown name="from_warehouse_id" label="{{ __('translation.from_warehouse') }}" :options="$warehouses" :selected="old('from_warehouse_id')" class="warehouse" id="from_warehouse_id" required />
                            <x-select-dropdown name="for_store_id" label="{{ __('translation.for_store') }}" :options="$stores" :selected="old('for_store_id', $stores->count() === 1 ? $stores->keys()->first() : '')" class="store" id="for_store_id" required />
                            <x-text-input name="date" label="{{ __('translation.date') }}" class="flatdatepickr" id="date" required value="{{ old('date', \App\Helpers\Settings::getFormattedDate(date('Y-m-d'))) }}" />
                        </div>
                        <hr>
                        {{-- Products --}}
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="26%">{{ __('translation.product') }}</th>
                                        <th width="10%" class="text-center">{{ __('translation.stock') }}</th>
                                        <th width="10%" class="text-center">{{ __('translation.quantity') }}</th>
                                        <th width="12%" class="text-center">{{ __('translation.tracking') }}</th>
                                        <th width="34%">{{ __('translation.barcodes') }}</th>
                                        <th width="8%" class="text-center">{{ __('translation.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-3">
                            <div class="text-center mt-2">
                                <button type="button" id="addRow" class="btn btn-success"><i class="mdi mdi-plus"></i>{{ __('translation.add_item') }}</button>
                            </div>
                            <div class="col-md-12 text-end">
                                <h5 class="mb-0">{{ __('translation.total_quantity') }} :<span id="totalQty" class="badge bg-primary"> 0</span></h5>
                            </div>
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
    @include('backend.admin.requisition.partials.barcode_modal')
@endsection

@section('script')

    <script>
        let activeRow = null;

        $(document).on('click', '.scanBarcode', function () {
            activeRow = $(this).closest('tr');
            $('#barcodeModal').modal('show');
            $('#scanBarcode').val('');
            $('#barcodeMessage').html('');
            setTimeout(function () {
                $('#scanBarcode').focus();
            }, 300);
        });
        $(document).on('shown.bs.modal', '#barcodeModal', function () {
            const input = document.getElementById('scanBarcode');
            if (input) {
                input.focus();
                input.select();
            }
        });

        $(document).on('keydown', '#scanBarcode', function (e) {
            // Enter OR Tab from barcode scanner
            if (e.which === 13 || e.which === 9) {
                e.preventDefault();
                searchBarcode();
            }
        });
        validateSelect2Form('requisitionForm', ['from_warehouse_id', 'for_store_id']);


        function searchBarcode() {
            let barcode = $('#scanBarcode').val().trim();
            if (barcode == '') {
                return;
            }
            let warehouse = $('#from_warehouse_id').val();
            let master_item_id = activeRow.find('.selectProduct').val();
            // Product must be selected first
            if (!master_item_id) {
                Swal.fire(
                    'Error',
                    'Please select a product first.',
                    'error'
                );
                return;
            }
            // Already scanned tracking ids
            let scannedIds = $('.hiddenTracking').map(function () {
                return $(this).val();
            }).get();

            $.post(
                "{{ route('admin.requisitions.barcode.search') }}",
                {
                    _token: "{{ csrf_token() }}",
                    barcode: barcode,
                    warehouse_id: warehouse,
                    master_item_id: master_item_id,
                    scanned_ids: scannedIds
                },
                function (res) {
                    if (res.success) {
                        fillProduct(res);
                        $('#scanBarcode').val('');
                        setTimeout(function () {
                            $('#scanBarcode').focus();
                        }, 100);
                    } else {
                        Swal.fire(
                            'Error',
                            res.message,
                            'error'
                        );
                    }
                }
            );
        }

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
                        let warehouse_id = $('select[name="from_warehouse_id"]').val();

                        if (!warehouse_id) {
                            return false;
                        }

                        return {
                            q: params.term,
                            warehouse_id: warehouse_id
                        };
                    },

                    processResults: function (data) {
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
        function resetItems() {
            $('#itemsTable tbody').empty();
            rowIndex = 0;
            if ($('#from_warehouse_id').val()) {
                addRow();
            }
            calculateTotal();
        }

        // ========================
        // ADD ROW
        // ========================
        function addRow() {
            let warehouse = $('#from_warehouse_id').val();
            if (!warehouse) {
                Swal.fire('Error', 'Please select warehouse first', 'error');
                return;
            }
            let row = `
                                                                                                                                                                                                <tr class="productRow">

                                                                                                                                                                                                    <td>
                                                                                                                                                                                                        <select
                                                                                                                                                                                                            name="items[${rowIndex}][master_item_id]"
                                                                                                                                                                                                            class="form-control selectProduct"
                                                                                                                                                                                                            required>
                                                                                                                                                                                                        </select>
                                                                                                                                                                                                    </td>

                                                                                                                                                                                                    <td class="stock text-center fw-bold text-primary">
                                                                                                                                                                                                        0
                                                                                                                                                                                                    </td>

                                                                                                                                                                                                    <td>
                                                                                                                                                                                                        <input
                                                                                                                                                                                                            type="number"
                                                                                                                                                                                                            name="items[${rowIndex}][qty]"
                                                                                                                                                                                                            class="form-control qty"
                                                                                                                                                                                                            min="1"
                                                                                                                                                                                                            disabled>
                                                                                                                                                                                                    </td>

                                                                                                                                                                                                    <td class="text-center">

                                                                                                                                                                                                        <span class="badge bg-secondary trackingType">
                                                                                                                                                                                                            NONE
                                                                                                                                                                                                        </span>

                                                                                                                                                                                                        <br>

                                                                                                                                                                                                        <button
                                                                                                                                                                                                            type="button"
                                                                                                                                                                                                            class="btn btn-primary btn-sm mt-2 scanBarcode d-none">

                                                                                                                                                                                                            <i class="mdi mdi-barcode-scan"></i>

                                                                                                                                                                                                            Scan

                                                                                                                                                                                                        </button>

                                                                                                                                                                                                    </td>

                                                                                                                                                                                                    <td>

                                                                                                                                                                                                        <div class="barcodeContainer"></div>

                                                                                                                                                                                                    </td>

                                                                                                                                                                                                    <td>

                                                                                                                                                                                                        <button
                                                                                                                                                                                                            type="button"
                                                                                                                                                                                                            class="btn btn-danger removeRow">

                                                                                                                                                                                                            <i class="mdi mdi-delete"></i>

                                                                                                                                                                                                        </button>

                                                                                                                                                                                                    </td>

                                                                                                                                                                                                </tr>`;

            $('#itemsTable tbody').append(row);

            let tr = $('#itemsTable tbody tr:last');

            initSelect2(tr.find('.selectProduct'));

            rowIndex++;
        }

        $('#addRow').click(addRow);

        addRow();

        // ========================
        // FETCH STOCK
        // ========================

        function fetchStock(row) {

            let warehouse_id = $('#from_warehouse_id').val();
            let master_item_id = row.find('.selectProduct').val();

            if (!warehouse_id || !master_item_id) {
                return;
            }

            $.get(
                "{{ route('admin.stock_returns.stock.check') }}",
                {
                    warehouse_id: warehouse_id,
                    master_item_id: master_item_id
                },
                function (res) {

                    let stock = parseFloat(res.stock) || 0;
                    let trackingType = (res.tracking_type || 'none').toLowerCase();

                    let qty = row.find('.qty');
                    let scanBtn = row.find('.scanBarcode');

                    // Save tracking type
                    row.attr('data-tracking-type', trackingType);

                    // Update UI
                    row.find('.stock').text(stock);
                    row.find('.trackingType').text(trackingType.toUpperCase());

                    // Clear previous values
                    qty.removeAttr('readonly')
                        .prop('disabled', false)
                        .removeAttr('max');

                    scanBtn.addClass('d-none');

                    // No Stock
                    if (stock <= 0) {

                        qty.val('')
                            .prop('disabled', true)
                            .prop('readonly', true);

                        row.find('.barcodeContainer').empty();

                        calculateTotal();

                        return;
                    }

                    qty.attr('max', stock);

                    // INDIVIDUAL / BATCH
                    scanBtn.removeClass('d-none');

                    qty.prop('readonly', true);

                    let scanned = row.find('.hiddenTracking').length;

                    qty.val(scanned);

                    calculateTotal();

                }
            ).fail(function () {

                Swal.fire(
                    'Error',
                    'Unable to fetch stock.',
                    'error'
                );

            });

        }
        // ========================
        // PRODUCT CHANGE
        // ========================
        $(document).on('change', '.selectProduct', function () {

            let row = $(this).closest('tr');

            let product = $(this).val();

            row.find('.qty').val('');

            let duplicate = false;

            $('.selectProduct').not(this).each(function () {

                if ($(this).val() == product) {

                    duplicate = true;

                }

            });

            if (duplicate) {

                Swal.fire(
                    'Error',
                    'Product already added.',
                    'error'
                );

                $(this).val(null).trigger('change.select2');

                row.find('.stock').text(0);

                row.find('.trackingType').text('NONE');

                row.find('.barcodeContainer').empty();

                row.find('.qty')
                    .val('')
                    .prop('disabled', true);


                return;

            }
            row.find('.barcodeContainer').empty();

            row.find('.trackingType').text('NONE');

            updateQty();

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
        // WAREHOUSE CHANGE
        // ========================
        $('#from_warehouse_id').change(function () {

            resetItems();

        });

        $('#for_store_id').change(function () {

        });

        // ========================
        // FORM VALIDATION
        // ========================
        $('#requisitionForm').on('submit', function (e) {

            e.preventDefault();

            let valid = true;

            let fromWarehouse = $('#from_warehouse_id').val();
            let toWarehouse = $('#for_store_id').val();

            if (!fromWarehouse || !toWarehouse) {

                Swal.fire(
                    'Error',
                    'Please select warehouse and store.',
                    'error'
                );

                return;
            }

            if ($('#itemsTable tbody tr').length === 0) {

                Swal.fire(
                    'Error',
                    'Add at least one item.',
                    'error'
                );

                return;
            }

            // ===========================
            // Validate Product & Qty
            // ===========================
            $('#itemsTable tbody tr').each(function () {

                let product = $(this).find('.selectProduct').val();
                let qty = parseInt($(this).find('.qty').val()) || 0;

                if (!product || qty <= 0) {

                    valid = false;

                    Swal.fire(
                        'Error',
                        'Fill all item details correctly.',
                        'error'
                    );

                    return false; // break loop
                }

            });

            if (!valid) {
                return;
            }

            // ===========================
            // Validate Barcode
            // ===========================
            $('#itemsTable tbody tr').each(function () {

                let tracking = $(this).find('.trackingType').text().trim().toUpperCase();

                if (tracking !== 'none') {

                    let qty = parseInt($(this).find('.qty').val()) || 0;
                    let scanned = $(this).find('.hiddenTracking').length;
                    console.log({
                        tracking: tracking,
                        qty: qty,
                        scanned: scanned
                    });
                    if (qty !== scanned) {

                        valid = false;

                        Swal.fire(
                            'Error',
                            'All barcodes must be scanned.',
                            'error'
                        );

                        return false; // break loop
                    }
                }

            });

            if (!valid) {
                return;
            }

            Swal.fire({
                title: 'Confirm Requisition?',
                icon: 'warning',
                showCancelButton: true
            }).then((result) => {

                if (result.isConfirmed) {
                    this.submit();
                }

            });

        });

        function trackingExists(trackingId) {

            let found = false;

            $('.hiddenTracking').each(function () {

                if ($(this).val() == trackingId) {
                    found = true;
                    return false;
                }

            });

            return found;
        }

        function barcodeExists(barcode) {

            let found = false;

            $('input[name*="[barcodes]"]').each(function () {

                if ($(this).val() == barcode) {
                    found = true;
                    return false;
                }

            });

            return found;
        }

        function addBarcode(item) {

            // Prevent duplicate barcode
            if (trackingExists(item.tracking_id)) {

                Swal.fire(
                    'Error',
                    'Barcode already scanned.',
                    'error'
                );

                return;
            }

            let rowIndex = activeRow.index();

            let selectedProduct = activeRow.find('.selectProduct').val();

            // Product must be selected first
            if (!selectedProduct) {

                Swal.fire(
                    'Error',
                    'Please select a product first.',
                    'error'
                );

                return;
            }

            // Barcode must belong to selected product
            if (parseInt(selectedProduct) !== parseInt(item.master_item_id)) {

                Swal.fire(
                    'Error',
                    'Barcode belongs to another product.',
                    'error'
                );

                return;
            }

            activeRow.find('.barcodeContainer').append(`
                                                                                                                                                                                                                                                                                                                                                                                                        <div class="barcodeItem border rounded p-2 mb-2">

                                                                                                                                                                                                                                                                                                                                                                                                            <div class="d-flex justify-content-between align-items-center">

                                                                                                                                                                                                                                                                                                                                                                                                                <span>${item.barcode}</span>

                                                                                                                                                                                                                                                                                                                                                                                                                <button
                                                                                                                                                                                                                                                                                                                                                                                                                    type="button"
                                                                                                                                                                                                                                                                                                                                                                                                                    class="btn btn-danger btn-sm removeBarcode">
                                                                                                                                                                                                                                                                                                                                                                                                                    ×
                                                                                                                                                                                                                                                                                                                                                                                                                </button>

                                                                                                                                                                                                                                                                                                                                                                                                            </div>

                                                                                                                                                                                                                                                                                                                                                                                                            <input
                                                                                                                                                                                                                                                                                                                                                                                                            type="hidden"
                                                                                                                                                                                                                                                                                                                                                                                                            class="hiddenTracking"
                                                                                                                                                                                                                                                                                                                                                                                                            name="items[${rowIndex}][tracking_ids][]"
                                                                                                                                                                                                                                                                                                                                                                                                            value="${item.tracking_id}">

                                                                                                                                                                                                                                                                                                                                                                                                            <input
                                                                                                                                                                                                                                                                                                                                                                                                                type="hidden"
                                                                                                                                                                                                                                                                                                                                                                                                                name="items[${rowIndex}][barcodes][]"
                                                                                                                                                                                                                                                                                                                                                                                                                value="${item.barcode}">
                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                    `);

            updateQty();
            refreshIndexes();
            $('#barcodeModal').modal('hide');

            $('#scanBarcode').val('');
        }

        $(document).on('click', '.removeBarcode', function () {

            $(this).closest('.barcodeItem').remove();

            updateQty();

            refreshIndexes();

        });

        function updateQty() {

            $('#itemsTable tbody tr').each(function () {

                let trackingType = ($(this).attr('data-tracking-type') || 'none').toLowerCase();

                let input = $(this).find('.qty');

                // Barcode based quantity
                let qty = $(this).find('.hiddenTracking').length;

                input.val(qty)
                    .prop('readonly', true);

            });

            calculateTotal();
        }

        $(document).on('click', '.removeRow', function () {

            $(this).closest('tr').remove();

            refreshIndexes();

            calculateTotal();

        });

        function refreshIndexes() {

            $('#itemsTable tbody tr').each(function (i) {

                $(this).find('.selectProduct')
                    .attr('name', 'items[' + i + '][master_item_id]');

                $(this).find('.qty')
                    .attr('name', 'items[' + i + '][qty]');

                $(this).find('.hiddenTracking').attr(
                    'name',
                    'items[' + i + '][tracking_ids][]'
                );

                $(this).find('input[type="hidden"][name$="[barcodes][]"]').attr(
                    'name',
                    'items[' + i + '][barcodes][]'
                );

                // $(this).find('.hiddenBarcode').each(function () {

                //     $(this).attr(
                //         'name',
                //         'items[' + i + '][barcodes][]'
                //     );

                // });

            });

        }

        function fillProduct(item) {

            let select = activeRow.find('.selectProduct');

            if (select.val()) {

                if (parseInt(select.val()) !== parseInt(item.master_item_id)) {

                    Swal.fire(
                        'Error',
                        'Barcode belongs to another product.',
                        'error'
                    );

                    return;
                }

                addBarcode(item);

                return;
            }

            let option = new Option(
                item.master_item_name,
                item.master_item_id,
                true,
                true
            );

            select.append(option).trigger('change.select2');

            fetchStock(activeRow);

            addBarcode(item);

        }

    </script>

@endsection