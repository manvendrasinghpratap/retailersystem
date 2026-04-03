@extends('backend.layouts.master-horizontal')


@section('content')
    @include('backend.components.breadcrumb')
    <div class="container-fluid">
        <!-- Barcode Input -->
        <div class="mb-3">
            <form method="POST" action="" onkeydown="return event.key != 'Enter';" autocomplete="off">
                @csrf
                <div class="row">
                    <x-text-input id="barcode" name="barcode" label="Barcode" class="barcode" required placeholder="Scan barcode here" autofocus maxlength="15" />
                </div>
            </form>
        </div>

        <!-- Cart Table -->
        <table class="table table-bordered" id="cart-table">
            <thead>
                <tr>
                    <th width="20%">{{ __('translation.category_name') }}</th>
                    <th width="40%">{{ __('translation.product_name') }}</th>
                    <th width="10%">{{ __('translation.quantity') }}</th>
                    <th width="100">{{ __('translation.b_ngn') . ' ' . __('translation.price') }}</th>
                    <th width="120">{{ __('translation.b_ngn') . ' ' . __('translation.total') }}</th>
                    <th width="50">{{ __('translation.action') }}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <!-- Totals -->
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <p>{{ __('translation.subtotal') }} : {{ __('translation.b_ngn') }} <span id="subtotal">0.00</span></p>
                <p>{{ __('translation.tax') }} : {{ __('translation.b_ngn') }} <span id="tax">0.00</span></p>
                <p><strong>{{ __('translation.grand_total') }} : {{ __('translation.b_ngn') }} <span id="grand_total">0.00</span></strong></p>

                <input type="number" id="paid_amount" class="form-control mb-2" placeholder="Paid Amount">
                <x-select-dropdown id="payment_method" name="payment_method" label="Payment Method" class="payment_method" :options="config('constants.customer_payment_method')" required />

                <button class="btn btn-success w-100" id="complete-sale">
                    Complete Payment
                </button>

            </div>
        </div>

    </div>

@endsection
@section('script')

    <script>
        let cart = [];

        // 🔥 Auto focus barcode always
        $(document).ready(function () {
            $('#barcode').focus();

            $(document).click(() => $('#barcode').focus());
        });

        // 🔹 Scan barcode (auto trigger on change)
        $('#barcode').on('change', function () {

            let barcode = $(this).val().trim();
            if (!barcode) return;

            $.post("{{ route('billing.scan') }}", {
                _token: "{{ csrf_token() }}",
                barcode: barcode
            }, function (response) {

                if (!response || response.status === false) {
                    alert(response?.message || 'Product not found');
                    return;
                }

                // 🔊 Beep sound (optional)
                try { new Audio('/beep.wav').play(); } catch (e) { }

                addToCart(response.data);
                calculateTotals();

            }).fail(function () {
                alert('Error fetching product');
            });

            $('#barcode').val('').focus();
        });

        // 🔹 Add product to cart
        function addToCart(product) {

            let existing = cart.find(item => item.id === product.id);

            let price = parseFloat(product.price) || 0;

            if (existing) {
                existing.quantity++;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name || 'N/A',
                    category_name: product.category_name || '-',
                    quantity: 1,
                    price: price
                });
            }

            renderCart();
        }

        // 🔹 Render cart
        function renderCart() {

            let html = '';

            if (!cart.length) {
                html = `<tr>
                                                                            <td colspan="6" class="text-center">Cart is empty</td>
                                                                        </tr>`;
            } else {

                cart.forEach((item, index) => {

                    let quantity = parseInt(item.quantity) || 1;
                    let price = parseFloat(item.price) || 0;
                    let total = quantity * price;

                    html += `<tr>
                                                        <td>${item.category_name}</td>

                                                        <td>${item.name}</td>

                                                        <td>
                                                            <input type="number"
                                                                class="form-control"
                                                                style="width: 80px;"
                                                                value="${quantity}"
                                                                min="1"
                                                                oninput="updateQuantity(${index}, this.value)">
                                                        </td>

                                                        <td>₹${price.toFixed(2)}</td>

                                                        <td class="item-total">₹${total.toFixed(2)}</td>

                                                        <td>
                                                            <button class="btn btn-sm"
                                                                onclick="removeItem(${index})">
                                                                <i class="fas fa-trash action-btn darkred"></i>
                                                            </button>
                                                        </td>
                                                    </tr>`;
                });
            }

            $('#cart-table tbody').html(html);
        }

        // 🔹 Update quantity
        function updateQuantity(index, qty) {

            qty = parseInt(qty);

            if (isNaN(qty) || qty < 1) qty = 1;

            cart[index].quantity = qty;

            renderCart();
            calculateTotals();
        }

        // 🔹 Remove item
        function removeItem(index) {
            cart.splice(index, 1);
            renderCart();
            calculateTotals();
        }

        // 🔹 Calculate totals
        function calculateTotals() {

            let subtotal = 0;

            cart.forEach(item => {
                let price = parseFloat(item.price) || 0;
                let qty = parseInt(item.quantity) || 1;
                subtotal += price * qty;
            });

            let tax = subtotal * 0.05;
            let grand_total = subtotal + tax;

            $('#subtotal').text(subtotal.toFixed(2));
            $('#tax').text(tax.toFixed(2));
            $('#grand_total').text(grand_total.toFixed(2));
        }

        // 🔹 Complete Sale
        $('#complete-sale').click(function () {

            if (!cart.length) {
                alert('Cart is empty');
                return;
            }

            let payload = {
                _token: "{{ csrf_token() }}",
                items: cart,
                subtotal: parseFloat($('#subtotal').text()) || 0,
                tax: parseFloat($('#tax').text()) || 0,
                discount: 0,
                total: parseFloat($('#grand_total').text()) || 0,
                paid_amount: parseFloat($('#paid_amount').val()) || 0,
                change_amount: 0,
                payment_method: $('#payment_method').val()
            };

            $.post("{{ route('billing.complete') }}", payload, function (response) {

                if (response.success) {
                    alert("✅ Sale Completed!");
                    location.reload();
                } else {
                    alert(response.message || 'Sale failed');
                }

            }).fail(function () {
                alert('Server error');
            });
        });
    </script>
@endsection