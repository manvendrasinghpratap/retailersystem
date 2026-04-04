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
            <input type="number" id="paid_amount" class="form-control mb-2" placeholder="Paid Amount">

            <button class="btn btn-success w-100" id="complete-sale">
                Complete Payment
            </button>
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
        $('#barcode').on('change', function () {

            let barcode = $(this).val().trim();
            if (!barcode) return;

            $.post("{{ route('billing.scan') }}", {
                _token: "{{ csrf_token() }}",
                barcode: barcode
            }, function (res) {

                if (!res || res.status === false) {
                    alert(res?.message || 'Product not found');
                    return;
                }

                try { new Audio('/beep.wav').play(); } catch (e) { }

                addToCart(res.data);
                calculateTotals();

            });

            $('#barcode').val('').focus();
        });


        function addToCart(product) {

            let stock = parseInt(product.stock) || 0;
            let price = parseFloat(product.price) || 0;

            if (stock <= 0) {
                alert('Out of stock');
                return;
            }

            // ✅ Match by ID + name (extra safety)
            let existing = cart.find(item =>
                item.id === product.id && item.name === product.name
            );

            if (existing) {

                if (existing.quantity >= stock) {
                    alert('Stock limit reached');
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

                    html += `
                                                                                    <tr>
                                                                                        <td>${item.category_name}</td>
                                                                                        <td>${item.name}</td>
                                                                                        <td class="${stock <= 5 ? 'text-danger' : ''}">${stock}</td>

                                                                                        <td>
                                                                                            <input type="number"
                                                                                                value="${qty}"
                                                                                                min="1"
                                                                                                max="${stock}"
                                                                                                style="width:80px"
                                                                                                onchange="updateQuantity(${index}, this.value, this)">
                                                                                        </td>

                                                                                        <td>${currency} ${price.toFixed(2)}</td>

                                                                                        <td class="item-total">
                                                                                            ${currency} ${total.toFixed(2)}
                                                                                        </td>

                                                                                        <td>
                                                                                            <button onclick="removeItem(${index})" class="btn btn-danger btn-sm">X</button>
                                                                                        </td>
                                                                                    </tr>`;
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
                alert('Max stock reached');
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
        }


        // 🔹 Complete sale
        $('#complete-sale').click(function () {

            if (!cart.length) {
                alert('Cart is empty');
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
                    alert('Sale Completed!');
                    location.reload();
                } else {
                    alert(res.message || 'Error');
                }
            });
        });

    </script>
@endsection