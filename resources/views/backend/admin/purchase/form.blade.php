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
                            <x-select-dropdown name="vendor_id" label="{{ __('translation.vendor')}}" :options="$vendors" :selected="request('vendor_id') ?? ''" class="supplier" mainrows="4" />
                            <x-select-dropdown name="warehouse_id" label="{{ __('translation.warehouse')}}" :options="$warehouses" :selected="request('warehouse_id') ?? ''" class="warehouse" mainrows="4" />
                            <div class="col-md-4">
                                <label class="form-label d-block">&nbsp;</label>
                                <x-href-input action="add" name="add-master-item" label="Add New Master Item" class="addMasterItem" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#masterItemModal" />
                            </div>
                        </div>
                        <div class="position-relative">
                            <table class="table table-hover align-middle" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="25%">{{ __('translation.product') }}</th>
                                        <th width="8%">{{ __('translation.quantity') }}</th>
                                        <th width="10%">{{ __('translation.price') }}</th>
                                        <th width="15%">{{ __('translation.tracking') }}</th>
                                        <th width="10%">{{ __('translation.total') }}</th>
                                        <th width="5%">{{ __('translation.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <!-- ADD ROW BUTTON -->
                            <div class="text-center mt-2"><button type="button" class="btn btn-success px-4" id="addRow"><i class="mdi mdi-plus"></i> {{ __('translation.add_item') }}</button></div>
                        </div>
                        <div class="text-end">
                            <h4>{{ __('translation.total')}}: @lang('translation.currency')<span id="grandTotal">0</span></h4><input type="hidden" name="total" id="totalInput">
                        </div>
                        <div id="trackingHiddenContainer"></div>
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
    <div class="offcanvas offcanvas-end" tabindex="-1" id="trackingCanvas" style="width:600px;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Product Tracking</h5>
            <a href="javascript:void(0);" class="btn btn-secondary btn-sm" data-bs-dismiss="offcanvas"><i class="fas fa-arrow-left"></i> {{ __('translation.back') }}</a>
            <!-- <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button> -->
        </div>
        <div class="offcanvas-body">
            <div class="mb-3">
                <strong>{{__('translation.product')}} :</strong>
                <span id="trackingProduct"></span>
                <br>
                <strong>{{__('translation.quantity')}} :</strong>
                <span id="trackingQty"></span>
                <br>
                <strong>{{__('translation.tracking')}} :</strong>
                <span id="trackingTypeLabel"></span>
            </div>
            <div id="trackingWorkspace"></div>
        </div>

    </div>
@endsection

@section('script')
    <script>
        // ========================
        // CANCEL CONFIRM
        // ========================

        let rowIndex = 0;
        let trackingCanvas = null;
        let currentTrackingRow = null;

        // Store tracking information of each purchase row
        let trackingData = {};

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
                        return {
                            results: data
                        };
                    }
                }
            });

        }

        function addRow() {
            let row = `
                                <tr data-row="${rowIndex}">
                                <td>
                                <select
                                name="items[${rowIndex}][product_id]"
                                class="form-control selectProduct">
                                </select>

                                </td>

                                <td>

                                <input
                                type="number"
                                name="items[${rowIndex}][qty]"
                                class="form-control qty"
                                value="1"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').slice(0, 3);"
                                min="1">

                                </td>

                                <td>

                                <input
                                type="number"
                                name="items[${rowIndex}][price]"
                                class="form-control price"
                                value="0"
                                min="0.01"
                                step="0.01"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').slice(0, 9);"
                                onfocus="if (this.value == '0') this.value = '';"
                                onblur="if (this.value === '') this.value = '0';"
                                >
                                </td>

                                <td>

                                <select
                                name="items[${rowIndex}][tracking_type]"
                                class="form-control trackingType">

                                <option value="none">None</option>
                                <option value="batch">Batch</option>
                                <option value="individual">Individual</option>

                                </select>

                                <button
                                type="button"
                                class="btn btn-primary btn-sm mt-2 manageTracking d-none">

                                <i class="mdi mdi-barcode-scan"></i>

                                Manage Tracking

                                </button>

                                <div class="trackingStatus text-success small mt-1">

                                No Tracking

                                </div>

                                </td>

                                <td class="rowTotal">

                                0.00

                                </td>

                                <td>

                                <button
                                type="button"
                                class="btn btn-danger removeRow">

                                ×

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

        $(document).on('change', '.selectProduct', function () {

            let selected = $(this).val();

            let duplicate = false;

            $('.selectProduct').not(this).each(function () {

                if ($(this).val() == selected && selected != '') {

                    duplicate = true;

                }

            });

            if (duplicate) {

                Swal.fire(

                    'Duplicate',

                    'Product already selected.',

                    'warning'

                );

                $(this).val(null).trigger('change');

            }

        });

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

        $(document).on(

            'input',

            '.qty,.price',

            calculateTotal

        );

        $(document).on('click', '.removeRow', function () {

            let row = $(this).closest('tr');

            let rowId = row.data('row');

            delete trackingData[rowId];

            row.remove();

            calculateTotal();

        });
        $('#purchaseForm').on('submit', function (e) {

            e.preventDefault();

            let form = this;

            let valid = true;

            if (!$('select[name="vendor_id"]').val()) {

                Swal.fire('Error', 'Select Supplier', 'error');

                return;

            }

            if (!$('select[name="warehouse_id"]').val()) {

                Swal.fire('Error', 'Select Warehouse', 'error');

                return;

            }

            if ($('#itemsTable tbody tr').length == 0) {

                Swal.fire(

                    'Error',

                    'Add at least one product.',

                    'error'

                );

                return;

            }

            $('#itemsTable tbody tr').each(function () {

                let row = $(this);

                let product = row.find('.selectProduct').val();

                let qty = parseInt(row.find('.qty').val()) || 0;

                let price = parseFloat(row.find('.price').val()) || 0;

                let type = row.find('.trackingType').val();

                let rowId = row.data('row');

                if (!product || qty <= 0 || price <= 0) {

                    valid = false;

                    return;

                }

                if (type === 'batch') {

                    if (

                        !trackingData[rowId] ||

                        trackingData[rowId].length === 0

                    ) {

                        valid = false;

                    }

                }

                if (type === 'individual') {

                    let scanned = trackingData[rowId]

                        ? trackingData[rowId].length

                        : 0;

                    if (qty !== scanned) {

                        valid = false;

                    }

                }

            });

            if (!valid) {

                Swal.fire(

                    'Error',

                    'Please complete product tracking.',

                    'error'

                );

                return;

            }

            Swal.fire({

                title: 'Save Purchase?',

                icon: 'question',

                showCancelButton: true

            }).then((result) => {

                if (result.isConfirmed) {

                    form.submit();

                }

            });

        });
        // ===============================
        // TRACKING TYPE CHANGED
        // ===============================

        $(document).on('change', '.trackingType', function () {

            let row = $(this).closest('tr');

            let rowId = row.data('row');

            let type = $(this).val();

            // Reset all tracking data
            trackingData[rowId] = [];

            // If this row is currently open in the offcanvas,
            // reload the tracking workspace
            if (
                currentTrackingRow &&
                currentTrackingRow.data('row') == rowId
            ) {
                currentTrackingRow = row;

                if (type !== 'none') {
                    loadTrackingWorkspace(type);
                }
            }

            if (type === 'none') {

                row.find('.manageTracking').addClass('d-none');

                row.find('.trackingStatus')
                    .removeClass('text-danger')
                    .addClass('text-success')
                    .text('No Tracking');

            } else {

                row.find('.manageTracking').removeClass('d-none');

                let qty = parseInt(row.find('.qty').val()) || 0;

                row.find('.trackingStatus')
                    .removeClass('text-success')
                    .addClass('text-danger')
                    .text('0 / ' + qty + ' Scanned');

            }

        });


        // ===============================
        // OPEN TRACKING WINDOW
        // ===============================

        $(document).on('click', '.manageTracking', function () {

            currentTrackingRow = $(this).closest('tr');

            let product = currentTrackingRow
                .find('.selectProduct')
                .select2('data')[0]?.text || '';

            let qty = currentTrackingRow.find('.qty').val();

            let trackingType = currentTrackingRow
                .find('.trackingType')
                .val();

            $('#trackingProduct').text(product);

            $('#trackingQty').text(qty);

            $('#trackingTypeLabel').text(

                trackingType.charAt(0).toUpperCase() +
                trackingType.slice(1)

            );

            loadTrackingWorkspace(trackingType);

            if (!trackingCanvas) {

                trackingCanvas = new bootstrap.Offcanvas(
                    document.getElementById('trackingCanvas')
                );

            }

            trackingCanvas.show();

        });


        // ===============================
        // QTY CHANGED
        // ===============================

        $(document).on('input', '.qty', function () {

            let row = $(this).closest('tr');

            updateTrackingStatus(row);

            if (
                currentTrackingRow &&
                currentTrackingRow.is(row)
            ) {

                $('#trackingQty').text($(this).val());

                updateProgress();

            }

        });


        // ===============================
        // LOAD TRACKING SCREEN
        // ===============================

        function loadTrackingWorkspace(type) {

            let html = '';

            // ==========================
            // BATCH TRACKING
            // ==========================
            if (type === 'batch') {

                html += `

                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="card">

                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="card-body">

                                                                                                                                                                                                                                                                                                                                                                                                                            <label class="form-label">
                                                                                                                                                                                                                                                                                                                                                                                                                                Batch Barcode
                                                                                                                                                                                                                                                                                                                                                                                                                            </label>

                                                                                                                                                                                                                                                                                                                                                                                                                            <input
                                                                                                                                                                                                                                                                                                                                                                                                                                type="text"
                                                                                                                                                                                                                                                                                                                                                                                                                                id="batchBarcode"
                                                                                                                                                                                                                                                                                                                                                                                                                                class="form-control"
                                                                                                                                                                                                                                                                                                                                                                                                                                autocomplete="off"
                                                                                                                                                                                                                                                                                                                                                                                                                                placeholder="Scan Batch Barcode">

                                                                                                                                                                                                                                                                                                                                                                                                                        </div>

                                                                                                                                                                                                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                                                                                                                                                                                                `;

            }

            // ==========================
            // INDIVIDUAL TRACKING
            // ==========================
            else {

                html += `

                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="card">

                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="card-body">

                                                                                                                                                                                                                                                                                                                                                                                                                            <label class="form-label">
                                                                                                                                                                                                                                                                                                                                                                                                                                Scan Barcode
                                                                                                                                                                                                                                                                                                                                                                                                                            </label>

                                                                                                                                                                                                                                                                                                                                                                                                                            <input
                                                                                                                                                                                                                                                                                                                                                                                                                                type="text"
                                                                                                                                                                                                                                                                                                                                                                                                                                id="barcodeInput"
                                                                                                                                                                                                                                                                                                                                                                                                                                class="form-control form-control-lg"
                                                                                                                                                                                                                                                                                                                                                                                                                                autocomplete="off"
                                                                                                                                                                                                                                                                                                                                                                                                                                placeholder="Scan barcode and press ENTER">

                                                                                                                                                                                                                                                                                                                                                                                                                        </div>

                                                                                                                                                                                                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="mt-3">

                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="progress">

                                                                                                                                                                                                                                                                                                                                                                                                                            <div
                                                                                                                                                                                                                                                                                                                                                                                                                                id="scanProgressBar"
                                                                                                                                                                                                                                                                                                                                                                                                                                class="progress-bar"
                                                                                                                                                                                                                                                                                                                                                                                                                                style="width:0%;">

                                                                                                                                                                                                                                                                                                                                                                                                                                0%

                                                                                                                                                                                                                                                                                                                                                                                                                            </div>

                                                                                                                                                                                                                                                                                                                                                                                                                        </div>

                                                                                                                                                                                                                                                                                                                                                                                                                        <div
                                                                                                                                                                                                                                                                                                                                                                                                                            id="scanProgressText"
                                                                                                                                                                                                                                                                                                                                                                                                                            class="text-center mt-2">

                                                                                                                                                                                                                                                                                                                                                                                                                            0 / 0

                                                                                                                                                                                                                                                                                                                                                                                                                        </div>

                                                                                                                                                                                                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                                                                                                                                                                                                `;

            }

            // ==========================
            // COMMON TABLE
            // ==========================
            html += `

                                                                                                                                                                                                                                                                                                                                                                                                                <div class="card mt-3">

                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="card-header">

                                                                                                                                                                                                                                                                                                                                                                                                                        Tracking List

                                                                                                                                                                                                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="card-body p-0">

                                                                                                                                                                                                                                                                                                                                                                                                                        <table class="table table-bordered table-striped mb-0">

                                                                                                                                                                                                                                                                                                                                                                                                                            <thead>

                                                                                                                                                                                                                                                                                                                                                                                                                                <tr>

                                                                                                                                                                                                                                                                                                                                                                                                                                    <th width="60">#</th>

                                                                                                                                                                                                                                                                                                                                                                                                                                    <th>Barcode</th>

                                                                                                                                                                                                                                                                                                                                                                                                                                    <th width="100">Action</th>

                                                                                                                                                                                                                                                                                                                                                                                                                                </tr>

                                                                                                                                                                                                                                                                                                                                                                                                                            </thead>

                                                                                                                                                                                                                                                                                                                                                                                                                            <tbody id="barcodeTable"></tbody>

                                                                                                                                                                                                                                                                                                                                                                                                                        </table>

                                                                                                                                                                                                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                                                                                                                                                                                                </div>

                                                                                                                                                                                                                                                                                                                                                                                                                <div id="hiddenTrackingInputs"></div>

                                                                                                                                                                                                                                                                                                                                                                                                            `;

            $('#trackingWorkspace').html(html);

            loadExistingTracking();

            setTimeout(function () {

                if (type === 'batch') {

                    $('#batchBarcode').focus();

                } else {

                    $('#barcodeInput').focus();

                }

            }, 200);

        }
        // ===============================
        // LOAD EXISTING TRACKING
        // ===============================

        function loadExistingTracking() {

            if (!currentTrackingRow) {

                return;

            }

            let rowId = currentTrackingRow.data('row');

            if (!trackingData[rowId]) {

                trackingData[rowId] = [];

            }

            renderTracking();

        }


        // ===============================
        // UPDATE TRACKING STATUS
        // ===============================

        function updateTrackingStatus(row) {

            let rowId = row.data('row');

            let qty = parseInt(row.find('.qty').val()) || 0;

            let scanned = trackingData[rowId]
                ? trackingData[rowId].length
                : 0;

            if (row.find('.trackingType').val() === 'none') {

                row.find('.trackingStatus')
                    .removeClass('text-danger')
                    .addClass('text-success')
                    .text('No Tracking');

                return;

            }

            row.find('.trackingStatus')
                .removeClass('text-success text-danger')
                .addClass(scanned == qty ? 'text-success' : 'text-danger')
                .text(scanned + ' / ' + qty + ' Scanned');

        }


        // ===============================
        // UPDATE PROGRESS BAR
        // ===============================

        function updateProgress() {

            if (!currentTrackingRow) {

                return;

            }

            let rowId = currentTrackingRow.data('row');

            let qty = parseInt(
                currentTrackingRow.find('.qty').val()
            ) || 0;

            let scanned = trackingData[rowId]
                ? trackingData[rowId].length
                : 0;

            let percent = qty > 0
                ? (scanned / qty) * 100
                : 0;

            percent = Math.min(percent, 100);

            $('#scanProgressBar')
                .css('width', percent + '%')
                .text(Math.round(percent) + '%');

            $('#scanProgressText')
                .text(scanned + ' / ' + qty);

        }

        // ===============================
        // RENDER TRACKING
        // ===============================

        function renderTracking() {

            if (!currentTrackingRow) {
                return;
            }

            let rowId = currentTrackingRow.data('row');

            let list = trackingData[rowId] || [];

            let tbody = '';

            list.forEach(function (item, index) {
                tbody += `
                                                                                                                                                                            <tr>
                                                                                                                                                                            <td>${index + 1}</td>
                                                                                                                                                                            <td>${item.barcode}</td>
                                                                                                                                                                            <td>
                                                                                                                                                                            <button
                                                                                                                                                                            type="button"
                                                                                                                                                                            class="btn btn-danger btn-sm removeBarcode"
                                                                                                                                                                            data-index="${index}">
                                                                                                                                                                            Remove
                                                                                                                                                                            </button>
                                                                                                                                                                            </td>
                                                                                                                                                                            </tr>`;

            });

            $('#barcodeTable').html(tbody);

            // Remove previous hidden inputs for this row
            $('#trackingHiddenContainer')
                .find('.tracking-row-' + rowId)
                .remove();

            let hidden = '';

            list.forEach(function (item, index) {
                hidden += `<input class="tracking-row-${rowId}"  type="hidden" name="items[${rowId}][trackings][${index}][barcode]" value="${item.barcode}">`;
            });

            $('#trackingHiddenContainer').append(hidden);

            updateTrackingStatus(currentTrackingRow);

            updateProgress();

        }
        // ===============================
        // SCAN INDIVIDUAL BARCODE
        // ===============================

        $(document).on('keydown', '#barcodeInput', function (e) {

            // Allow ESC to close Offcanvas
            if (e.key === 'Escape') {
                return;
            }

            // Only Enter or Tab
            if (e.key !== 'Enter' && e.key !== 'Tab') {
                return;
            }

            e.preventDefault();

            let input = $(this);
            let barcode = input.val().trim();

            if (barcode === '') {
                return;
            }

            let rowId = currentTrackingRow.data('row');

            let qty = parseInt(currentTrackingRow.find('.qty').val()) || 0;

            // Initialize
            if (!trackingData[rowId]) {
                trackingData[rowId] = [];
            }

            // Quantity limit
            if (trackingData[rowId].length >= qty) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Limit Reached',
                    text: 'Required quantity already scanned.'
                });

                input.val('').focus();
                return;
            }

            fetch("{{ route('admin.purchase.validateBarcode') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    barcode: barcode,
                    routeName: "{{ request()->route()->getName() }}"
                })
            })
                .then(function (res) {

                    if (!res.ok) {
                        throw new Error('Server Error');
                    }

                    return res.json();

                })
                .then(function (data) {

                    // Invalid barcode
                    if (data.status === false) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Barcode',
                            text: data.message
                        });

                        input.val('').focus();
                        return;
                    }

                    // Duplicate barcode
                    let exists = trackingData[rowId].some(function (item) {
                        return item.barcode === barcode;
                    });

                    if (exists) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Duplicate',
                            text: 'Barcode already scanned.'
                        });

                        input.val('').focus();
                        return;
                    }

                    // Add barcode
                    trackingData[rowId].push({
                        barcode: barcode
                    });

                    renderTracking();

                    new Audio('/beep.wav').play();

                    input.val('').focus();

                })
                .catch(function (err) {

                    console.error(err);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Unable to validate barcode.'
                    });

                });

        });

        // ===============================
        // REMOVE SCANNED BARCODE
        // ===============================

        $(document).on('click', '.removeBarcode', function () {

            let rowId = currentTrackingRow.data('row');

            let index = $(this).data('index');

            trackingData[rowId].splice(index, 1);

            renderTracking();

            $('#barcodeInput').focus();

        });


        // ===============================
        // SCAN BATCH BARCODE
        // ===============================

        $(document).on('keydown', '#batchBarcode', function (e) {

            if (e.key === 'Escape') {
                return;
            }

            if (e.key !== 'Enter' && e.key !== 'Tab') {
                return;
            }

            e.preventDefault();

            let input = $(this);

            let barcode = input.val().trim();

            if (barcode === '') {
                return;
            }

            let rowId = currentTrackingRow.data('row');

            fetch("{{ route('admin.purchase.validateBarcode') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    barcode: barcode,
                    routeName: "{{ request()->route()->getName() }}"
                })
            })
                .then(res => res.json())
                .then(data => {

                    // Invalid barcode
                    if (data.status === false) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Barcode',
                            text: data.message
                        });

                        input.val('').focus();
                        return;
                    }

                    if (!trackingData[rowId]) {
                        trackingData[rowId] = [];
                    }

                    // Already same barcode
                    if (
                        trackingData[rowId].length > 0 &&
                        trackingData[rowId][0].barcode === barcode
                    ) {

                        Swal.fire(
                            'Duplicate',
                            'This batch barcode is already scanned.',
                            'warning'
                        );

                        input.val('').focus();
                        return;
                    }

                    // Existing batch barcode -> ask replace
                    if (trackingData[rowId].length > 0) {

                        Swal.fire({
                            title: 'Replace Batch Barcode?',
                            text: 'Only one barcode is allowed for Batch tracking.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Replace'
                        }).then((result) => {

                            if (!result.isConfirmed) {
                                input.val('').focus();
                                return;
                            }

                            trackingData[rowId] = [{
                                barcode: barcode
                            }];

                            renderTracking();

                            new Audio('/beep.wav').play();

                            input.val('').focus();

                        });

                        return;
                    }

                    // First barcode
                    trackingData[rowId].push({
                        barcode: barcode
                    });

                    renderTracking();

                    new Audio('/beep.wav').play();

                    input.val('').focus();

                })
                .catch(function () {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Unable to validate barcode.'
                    });

                });

        });
    </script>
@endsection