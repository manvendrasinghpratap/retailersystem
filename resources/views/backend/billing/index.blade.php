@extends('backend.layouts.master-horizontal')

@section('content')
    @include('backend.components.breadcrumb')

    <div class="container-fluid">

        <!-- Barcode -->
        <div class="mb-3">
            <input type="text" id="barcode" class="form-control" placeholder="Scan barcode here" autofocus>
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

            <!-- PARTIAL PAYMENT -->
            <div id="partial_payment_section" style="display:none;">
                <x-text-input :islabel="true" labelclass="left" name="cash_amount" data-method="cash" :label="__('translation.cash')" :value="request('cash_amount')" :placeholder="__('translation.cash')" class="form-control partial-amount onlydecimal default-zero" mainrows="12" />
                <x-text-input :islabel="true" labelclass="left" name="card_amount" data-method="card" :label="__('translation.card')" :value="request('card_amount')" :placeholder="__('translation.card')" class="form-control partial-amount onlydecimal default-zero" mainrows="12" />
                <x-text-input :islabel="true" labelclass="left" name="transfer_amount" data-method="transfer" :label="__('translation.transfer')" :value="request('transfer_amount')" :placeholder="__('translation.transfer')" class="form-control partial-amount onlydecimal default-zero" mainrows="12" />
            </div>
            <button class="btn btn-success w-100" id="complete-sale">{{ __('translation.complete_payment') }}</button>

        </div>

    </div>
@endsection

@section('script')
    <script>

        let cart = [];
        let currency = "{{ __('translation.b_ngn') }}";


        // 🔥 Always focus barcode
        $(document).ready(function () {
            $('#barcode').focus();
        });

        // 🔹 Scan barcode
        // $('#barcode').on('change', function () {

        //     let barcode = $(this).val().trim();
        //     if (!barcode) return;

        //     $.post("{{ route('billing.scan') }}", {
        //         _token: "{{ csrf_token() }}",
        //         barcode: barcode
        //     }, function (res) {
        //         console.log(res);
        //         if (!res || res.status === false) {
        //             showAlert('error', 'Error', res?.message || 'Product not found || This barcode is not allowed for this operation!');
        //             return;
        //         }

        //         try { new Audio('/beep.wav').play(); } catch (e) { }

        //         addToCart(res.data);
        //         calculateTotals();

        //     });

        //     $('#barcode').val('').focus();
        // });

        let scanning = false;

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
                        showAlert('error', 'Error', res?.message || 'Product not found || This barcode is not allowed for this operation!');
                        // showAlert('error', 'Error', res?.message || 'Product not found || ');
                        return;
                    }

                    try { new Audio('/beep.wav').play(); } catch (e) { }

                    addToCart(res.data);
                    calculateTotals();
                })

                .fail(function (xhr) {
                    let message = xhr.responseJSON?.message || 'Error';
                    showAlert('error', 'Error', message);
                })

                .always(function () {
                    input.val('').focus();
                    scanning = false;
                });

        });

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

        function syncFullAmount() {
            let total = parseFloat($('#grand_total').text()) || 0;
            $('#full_amount').val(total.toFixed(2));
        }

        // call whenever total changes
        function calculateTotals() {
            let subtotal = 0;

            cart.forEach(item => {
                subtotal += item.quantity * item.price;
            });

            let tax = subtotal * 0.0;
            let total = subtotal + tax;

            $('#subtotal').text(subtotal.toFixed(2));
            $('#tax').text(tax.toFixed(2));
            $('#grand_total').text(total.toFixed(2));

            syncFullAmount(); // 🔥 auto update
        }
        function addToCart(product) {

            let stock = parseInt(product.stock) || 0;
            let price = parseFloat(product.price) || 0;

            if (stock <= 0) {
                showAlert('error', 'Error', 'Out of stock');
                return;
            }

            // ✅ Match by ID + name (extra safety)
            let existing = cart.find(item =>
                item.id === product.id && item.name === product.name
            );

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

            renderCart();
            calculateTotals();
        }

        // 🔹 Render cart
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

                    html += `<tr> <td>${item.category_name}</td> <td>${item.name}</td> <td class="${stock <= 5 ? 'text-danger' : ''}">${stock}</td> <td><input type="number" value="${qty}" min="1" max="${stock}" style="width:80px" onchange="updateQuantity(${index}, this.value, this)"></td> <td>${currency} ${price.toFixed(2)}</td> <td class="item-total"> ${currency} ${total.toFixed(2)}</td><td><button onclick="removeItem(${index})" class="btn btn-danger btn-sm">X</button></td></tr>`;
                });
            }

            $('#cart-table tbody').html(html);
        }


        // 🔹 Update quantity (NO RE-RENDER)
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

            calculateTotals();
        }


        // 🔹 Remove item
        function removeItem(index) {
            cart.splice(index, 1);
            renderCart();
            calculateTotals();
        }


        // 🔹 Totals

        function processPayment() {

            let type = $('#payment_type').val();
            let total = parseFloat($('#grand_total').text()) || 0;

            let payments = [];
            let totalPaid = 0;

            // ✅ FULL
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
                totalPaid = amount;
            }

            // ✅ PARTIAL
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

        // 🔹 Complete sale

        $('#complete-sale').click(function () {

            let paymentData = processPayment();
            if (!paymentData) return;

            $.post("{{ route('billing.complete') }}", {
                _token: "{{ csrf_token() }}",
                items: cart,
                subtotal: parseFloat($('#subtotal').text()) || 0,
                tax: parseFloat($('#tax').text()) || 0,
                discount: 0,
                total: parseFloat($('#grand_total').text()) || 0,
                payment_type: paymentData.payment_type,
                payments: paymentData.payments
            }, function (res) {

                if (res.success) {
                    showAlert('success', 'Success', 'Sale Completed!');
                    location.reload();
                }
            });
        });


        $('#complete-sale-delete').click(function () {
            if (!cart.length) {
                showAlert('error', 'Error', 'Cart is empty');
                return;
            }

            let paymentData = processPayment();
            if (!paymentData) return; // ❌ stop if invalid

            let payload = {
                _token: "{{ csrf_token() }}",
                items: cart,
                subtotal: parseFloat($('#subtotal').text()) || 0,
                tax: parseFloat($('#tax').text()) || 0,
                total: parseFloat($('#grand_total').text()) || 0,
                payment_type: paymentData.payment_type,
                payments: paymentData.payments
            };

            $.post("{{ route('billing.complete') }}", payload, function (res) {

                if (res.success) {
                    showAlert('success', 'Success', 'Sale Completed!');
                    location.reload();
                } else {
                    showAlert('error', 'Error', res.message || 'Error');
                }
            });
        });
        $('#complete-sale-old').click(function () {

            if (!cart.length) {
                showAlert('error', 'Error', 'Cart is empty');
                return;
            }

            $.post("{{ route('billing.complete') }}", {
                _token: "{{ csrf_token() }}",
                items: cart,
                subtotal: $('#subtotal').text(),
                tax: $('#tax').text(),
                total: $('#grand_total').text(),
                paid_amount: $('#paid_amount').val(),
                payment_method: 'cash'
            }, function (res) {

                if (res.success) {
                    showAlert('success', 'Success', 'Sale Completed!');
                    location.reload();
                } else {
                    showAlert('error', 'Error', res.message || 'Error');
                }
            });
        });

    </script>
@endsection