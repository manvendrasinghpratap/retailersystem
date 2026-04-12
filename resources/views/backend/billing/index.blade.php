@extends('backend.layouts.master-horizontal')

@section('content')
    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <!-- Barcode -->
                    <div class="mb-3">
                        <input type="text" id="barcode" class="form-control" placeholder="Scan barcode here" autofocus autocomplete="off">
                    </div>

                    <!-- Cart -->
                    <table class="table table-bordered" id="cart-table">
                        <thead>
                            <tr>
                                <th>{{ __('translation.category_name') }}</th>
                                <th>{{ __('translation.product_name') }}</th>
                                <th>{{ __('translation.stock') }}</th>
                                <th>{{ __('translation.quantity') }}</th>
                                <th>{{ __('translation.b_ngn') . ' ' . __('translation.price') }}</th>
                                <th>{{ __('translation.b_ngn') . ' ' . __('translation.total') }}</th>
                                <th>{{ __('translation.action') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <!-- Totals -->
                    <div class="text-end">
                        <p>Subtotal: {{ __('translation.b_ngn') }} <span id="subtotal">0.00</span></p>
                        <p>Tax: {{ __('translation.b_ngn') }} <span id="tax">0.00</span></p>
                        <h4>Total: {{ __('translation.b_ngn') }} <span id="grand_total">0.00</span></h4>
                        <!-- <input type="number" id="paid_amount" class="form-control mb-2" placeholder="Paid Amount"> -->
                        <!-- Payment Type -->
                        <div class="mt-4 mb-2">
                            <x-select-dropdown :noselect="true" :nolabel="true" id="payment_type" name="payment_type" label="{{ __('translation.payment_type') }}" :options="config('constants.paymenttypes')" :selected="request('payment_type')" class="payment_type" mainrows="12" />
                        </div>
                        <!-- FULL PAYMENT -->
                        <div id="full_payment_section">
                            <x-text-input :islabel="true" labelclass="left" name="full_amount" :label="__('translation.amount')" :value="request('full_amount')" :placeholder="__('translation.amount')" class="form-control onlydecimal default-zero" mainrows="12" />
                            <x-select-dropdown :nolabel="true" id="full_method" name="full_method" label="{{ __('translation.customer_payment_method') }}" :options="config('constants.customer_payment_method')" :selected="request('full_method')" class="full_method" mainrows="12" />
                        </div>
                        <div class="mb-2 d-flex gap-2 justify-content-end">
                            <input type="text" id="coupon_code" class="form-control" style="max-width:200px;" placeholder="Enter Coupon">
                            <button type="button" class="btn btn-primary" id="apply_coupon">Apply</button>
                        </div>
                        <!-- CUSTOMER SECTION -->
                        <div class="mt-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Customer</strong>
                                <a href="javascript:void(0)" id="toggle_customer_form">+ Add Customer</a>
                            </div>

                            <!-- Selected Customer -->
                            <div id="selected_customer" class="text-muted mb-2">
                                No customer selected
                            </div>

                            <!-- Hidden Form -->
                            <div id="customer_form" style="display:none;">
                                <x-text-input :islabel="false" labelclass="left" id="customer_phone" name="customer_phone" :value="request('customer_phone')" :placeholder="__('translation.phone_number_or_whatsapp_number')" class="form-control onlyinteger default-zero" mainrows="12" maxlength="11" />
                                <x-text-input :islabel="false" labelclass="left" id="customer_name" name="customer_name" :value="request('customer_name')" :placeholder="__('translation.customer_name')" class="form-control" mainrows="12" />
                                <x-text-input :islabel="false" labelclass="left" id="customer_email" name="customer_email" :value="request('customer_email')" :placeholder="__('translation.email')" class="form-control" mainrows="12" />
                                <!-- <input type="text" id="customer_name" class="form-control mb-2" placeholder="Customer name"> -->
                                <button type="button" class="btn btn-sm btn-primary" id="save_customer_btn">Save Customer</button>
                            </div>
                        </div>
                        <p>Discount: {{ __('translation.b_ngn') }} <span id="discount">0.00</span></p>
                        <!-- PARTIAL PAYMENT -->
                        <div id="partial_payment_section" style="display:none;">
                            <x-text-input :islabel="true" labelclass="left" name="cash_amount" data-method="cash" :label="__('translation.cash')" :value="request('cash_amount')" :placeholder="__('translation.cash')" class="form-control partial-amount onlydecimal default-zero" mainrows="12" />
                            <x-text-input :islabel="true" labelclass="left" name="card_amount" data-method="card" :label="__('translation.card')" :value="request('card_amount')" :placeholder="__('translation.card')" class="form-control partial-amount onlydecimal default-zero" mainrows="12" />
                            <x-text-input :islabel="true" labelclass="left" name="transfer_amount" data-method="transfer" :label="__('translation.transfer')" :value="request('transfer_amount')" :placeholder="__('translation.transfer')" class="form-control partial-amount onlydecimal default-zero" mainrows="12" />
                        </div>
                        <button class="btn btn-success w-100 mb-4" id="complete-sale">{{ __('translation.complete_payment') }}</button>

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let cart = [];
        let currency = "{{ __('translation.b_ngn') }}";
        let discount = 0;
        let scanning = false;
        let selectedCustomerId = null;

        // 🔥 Auto focus
        $(document).ready(function () {
            $('#barcode').focus();
        });

        // =========================
        // 🔹 CUSTOMER TOGGLE
        // =========================
        $('#toggle_customer_form').click(function () {
            $('#customer_form').toggle();
        });

        // =========================
        // 🔹 AUTO CHECK PHONE
        // =========================
        $('#customer_phone').on('blur', function () {

            let phone = $(this).val().trim();

            if (!phone) return;

            $.post("{{ route('admin.customers.findByPhone') }}", {
                _token: "{{ csrf_token() }}",
                phone: phone
            })
                .done(function (res) {

                    if (res.exists) {

                        selectedCustomerId = res.customer.id;

                        // ✅ Autofill name
                        $('#customer_name').val(res.customer.name);

                        // ✅ Show selected
                        $('#selected_customer').html(
                            `Customer: ${res.customer.name} (${res.customer.phone})`
                        );
                        $('#save_customer_btn').hide();

                    } else {

                        // ❌ New customer → clear name & ID
                        selectedCustomerId = null;
                        $('#customer_name').val('');

                        $('#selected_customer').html('New customer (not saved)');
                        $('#save_customer_btn').show();
                    }
                });
        });

        // =========================
        // 🔹 SAVE CUSTOMER
        // =========================
        $('#save_customer_btn').click(function () {

            let phone = $('#customer_phone').val().trim();
            let name = $('#customer_name').val().trim();
            let email = $('#customer_email').val().trim();

            if (!phone) {
                showAlert('error', 'Error', 'Phone is required');
                return;
            }

            $.post("{{ route('admin.customers.findByPhone') }}", {
                _token: "{{ csrf_token() }}",
                phone: phone
            })
                .done(function (res) {

                    if (res.exists) {
                        selectedCustomerId = res.customer.id;

                        $('#selected_customer').html(
                            `Customer: ${res.customer.name} (${res.customer.phone})`
                        );

                        showAlert('success', 'Found', 'Existing customer selected');

                    } else {

                        if (!name) {
                            showAlert('error', 'Error', 'Enter name for new customer');
                            return;
                        }

                        $.post("{{ route('admin.customers.quickStore') }}", {
                            _token: "{{ csrf_token() }}",
                            name: name,
                            phone: phone,
                            email: email
                        })
                            .done(function (res) {

                                selectedCustomerId = res.customer.id;

                                $('#selected_customer').html(
                                    `Customer: ${res.customer.name} (${res.customer.phone})`
                                );
                                $('#save_customer_btn').hide();
                                $('#customer_form').hide();
                                $('#toggle_customer_form').hide();
                                showAlert('success', 'Success', 'Customer created');
                            });
                    }
                });
        });

        // =========================
        // 🔹 SCAN BARCODE
        // =========================
        $('#barcode').on('change', function () {

            if (scanning) return;
            scanning = true;

            let input = $(this);
            let barcode = input.val().trim();

            if (!barcode) {
                scanning = false;
                return;
            }

            $.post("{{ route('billing.scan') }}", {
                _token: "{{ csrf_token() }}",
                barcode: barcode
            })
                .done(function (res) {

                    if (!res || res.status === false) {
                        showAlert('error', 'Error', res?.message || 'Product not found');
                        return;
                    }

                    try { new Audio('/beep.wav').play(); } catch (e) { }

                    addToCart(res.data);

                })
                .fail(function (xhr) {
                    showAlert('error', 'Error', xhr.responseJSON?.message || 'Server error');
                })
                .always(function () {
                    input.val('').focus();
                    scanning = false;
                });

        });

        // =========================
        // 🔹 ADD TO CART
        // =========================
        function addToCart(product) {

            let stock = parseInt(product.stock) || 0;
            let price = parseFloat(product.price) || 0;

            if (stock <= 0) {
                showAlert('error', 'Error', 'Out of stock');
                return;
            }

            let existing = cart.find(item => item.id === product.id);

            if (existing) {

                if (existing.quantity >= stock) {
                    showAlert('error', 'Error', 'Stock limit reached');
                    return;
                }

                existing.quantity++;

            } else {

                cart.push({
                    id: product.id,
                    name: product.name || 'N/A',
                    category_name: product.category_name || '-',
                    quantity: 1,
                    price: price,
                    stock: stock
                });
            }

            resetCoupon();
            renderCart();
            calculateTotals();
        }

        // =========================
        // 🔹 RENDER CART
        // =========================
        function renderCart() {

            let html = '';

            if (!cart.length) {
                html = `<tr><td colspan="7" class="text-center">Cart is empty</td></tr>`;
            } else {

                cart.forEach((item, index) => {

                    let qty = parseInt(item.quantity) || 1;
                    let price = parseFloat(item.price) || 0;
                    let stock = parseInt(item.stock) || 0;
                    let total = qty * price;

                    html += `<tr><td>${item.category_name}</td><td>${item.name}</td><td class="${stock <= 5 ? 'text-danger' : ''}">${stock}</td><td><input type="number" value="${qty}" min="1" max="${stock}" style="width:80px" onchange="updateQuantity(${index}, this.value, this)"></td><td>${currency} ${price.toFixed(2)}</td><td class="item-total">${currency} ${total.toFixed(2)}</td><td><button onclick="removeItem(${index})" class="btn btn-danger btn-sm">X</button></td></tr>`;
                });
            }

            $('#cart-table tbody').html(html);
        }

        // =========================
        // 🔹 UPDATE QTY
        // =========================
        function updateQuantity(index, qty, el) {

            let stock = parseInt(cart[index].stock) || 0;

            qty = parseInt(qty);
            if (isNaN(qty) || qty < 1) qty = 1;

            if (qty > stock) {
                qty = stock;
                showAlert('error', 'Error', 'Max stock reached');
            }

            cart[index].quantity = qty;

            $(el).val(qty);

            let price = parseFloat(cart[index].price);
            let total = qty * price;

            let row = $(el).closest('tr');
            row.find('.item-total').text(`${currency} ${total.toFixed(2)}`);

            resetCoupon();
            calculateTotals();
        }

        // =========================
        // 🔹 REMOVE ITEM
        // =========================
        function removeItem(index) {
            cart.splice(index, 1);
            resetCoupon();
            renderCart();
            calculateTotals();
        }

        // =========================
        // 🔹 APPLY COUPON
        // =========================
        $('#apply_coupon').click(function () {

            let code = $('#coupon_code').val().trim();
            let subtotal = parseFloat($('#subtotal').text()) || 0;

            if (!code) {
                showAlert('error', 'Error', 'Enter coupon code');
                return;
            }

            $.post("{{ route('coupon.apply') }}", {
                _token: "{{ csrf_token() }}",
                code: code,
                total: subtotal
            })
                .done(function (res) {

                    if (!res.success) {
                        showAlert('error', 'Error', res.message);
                        return;
                    }

                    discount = parseFloat(res.discount) || 0;

                    $('#discount').text(discount.toFixed(2));

                    calculateTotals();

                    showAlert('success', 'Success', 'Coupon Applied');

                })
                .fail(function () {
                    showAlert('error', 'Error', 'Server error');
                });
        });

        // =========================
        // 🔹 RESET COUPON
        // =========================
        function resetCoupon() {
            discount = 0;
            $('#discount').text('0.00');
            $('#coupon_code').val('');
        }

        // =========================
        // 🔹 TOTALS
        // =========================
        function calculateTotals() {

            let subtotal = 0;

            cart.forEach(item => {
                subtotal += item.quantity * item.price;
            });

            let tax = 0;
            let total = subtotal + tax - discount;

            if (total < 0) total = 0;

            $('#subtotal').text(subtotal.toFixed(2));
            $('#tax').text(tax.toFixed(2));
            $('#grand_total').text(total.toFixed(2));

            syncFullAmount();
        }

        function syncFullAmount() {
            let total = parseFloat($('#grand_total').text()) || 0;
            $('#full_amount').val(total.toFixed(2));
        }

        // =========================
        // 🔹 PAYMENT TYPE SWITCH
        // =========================
        $('#payment_type').on('change', function () {

            let type = $(this).val();

            if (type === 'full') {
                $('#full_payment_section').show();
                $('#partial_payment_section').hide();
            } else {
                $('#full_payment_section').hide();
                $('#partial_payment_section').show();
            }
        });

        // =========================
        // 🔹 PROCESS PAYMENT
        // =========================
        function processPayment() {

            let type = $('#payment_type').val();
            let total = parseFloat($('#grand_total').text()) || 0;

            let payments = [];
            let totalPaid = 0;

            if (type === 'full') {

                let amount = parseFloat($('#full_amount').val()) || 0;
                let method = $('#full_method').val();

                if (!method) {
                    showAlert('error', 'Error', 'Select payment method');
                    return false;
                }

                if (amount !== total) {
                    showAlert('error', 'Error', 'Amount must equal total');
                    return false;
                }

                payments.push({ method, amount });
            }

            if (type === 'partial') {

                $('.partial-amount').each(function () {

                    let amt = parseFloat($(this).val()) || 0;
                    let method = $(this).data('method');

                    if (amt > 0) {
                        payments.push({ method, amount: amt });
                        totalPaid += amt;
                    }
                });

                if (!payments.length) {
                    showAlert('error', 'Error', 'Enter payment amounts');
                    return false;
                }

                if (totalPaid !== total) {
                    showAlert('error', 'Error', 'Total payment must equal bill');
                    return false;
                }
            }

            return {
                payment_type: type,
                payments: payments
            };
        }

        // =========================
        // 🔹 COMPLETE SALE
        // =========================
        $('#complete-sale').click(function () {

            if (!cart.length) {
                showAlert('error', 'Error', 'Cart is empty');
                return;
            }

            // ✅ Confirmation popup FIRST
            Swal.fire({
                title: 'Complete Purchase?',
                text: `Total: ${currency} ${$('#grand_total').text()}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Complete Purchase',
                cancelButtonText: 'Add More Items',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then((result) => {

                // ❌ User wants to continue shopping
                if (!result.isConfirmed) {
                    $('#barcode').focus();
                    return;
                }

                // ✅ Now process payment AFTER confirmation
                let paymentData = processPayment();
                if (!paymentData) return;

                let phone = $('#customer_phone').val().trim();
                let name = $('#customer_name').val().trim();
                let email = $('#customer_email').val().trim();

                // ✅ If Save button is visible → customer not saved yet
                if ($('#save_customer_btn').is(':visible')) {

                    if (!phone) {
                        showAlert('error', 'Error', 'Phone is required');
                        return;
                    }

                    if (!name) {
                        showAlert('error', 'Error', 'Name is required for new customer');
                        return;
                    }

                    // 🔥 Auto create customer
                    $.post("{{ route('admin.customers.quickStore') }}", {
                        _token: "{{ csrf_token() }}",
                        name: name,
                        phone: phone,
                        email: email
                    })
                        .done(function (res) {

                            selectedCustomerId = res.customer.id;

                            // ✅ Complete sale
                            completeSale(paymentData, selectedCustomerId);
                        })
                        .fail(function () {
                            showAlert('error', 'Error', 'Failed to create customer');
                        });

                } else {
                    // ✅ Existing customer or optional
                    completeSale(paymentData, selectedCustomerId || null);
                }

            });
        });


        // =========================
        // 🔹 FINAL API
        // =========================
        function completeSale(paymentData, customer_id) {

            $.post("{{ route('billing.complete') }}", {
                _token: "{{ csrf_token() }}",
                customer_id: customer_id,
                items: cart,
                subtotal: parseFloat($('#subtotal').text()) || 0,
                tax: parseFloat($('#tax').text()) || 0,
                discount: discount || 0,
                total: parseFloat($('#grand_total').text()) || 0,
                payment_type: paymentData.payment_type,
                payments: paymentData.payments
            })
                .done(function (res) {

                    if (!res || !res.success) {
                        showAlert('error', 'Error', res?.message || 'Something went wrong');
                        return;
                    }

                    showAlert('success', 'Success', 'Sale Completed!')
                        .then(() => {
                            // printReceipt(res.sale_id);
                            // setTimeout(() => location.reload(), 500);
                            location.reload();
                        });

                })
                .fail(function (xhr) {
                    showAlert('error', 'Error', xhr.responseJSON?.message || 'Server Error');
                });
        }

        // =========================
        // 🔹 PRINT
        // =========================
        function printReceipt(sale_id) {
            let url = "{{ route('printinvoice', ':id') }}".replace(':id', sale_id);
            window.open(url, '_blank');
        }
</script>@endsection