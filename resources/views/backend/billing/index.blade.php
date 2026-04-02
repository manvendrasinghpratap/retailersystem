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
                    <th>{{ __('translation.product_name') }}</th>
                    <th width="100">{{ __('translation.quantity') }}</th>
                    <th width="100">{{ __('translation.price') }}</th>
                    <th width="120">{{ __('translation.total') }}</th>
                    <th width="50">X</th>
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

        $('#barcode').on('change', function () {

            let barcode = $(this).val();

            $.post("{{ route('billing.scan') }}", {
                _token: "{{ csrf_token() }}",
                barcode: barcode
            }, function (response) {

                if (response.error) {
                    alert(response.error);
                    return;
                }

                addToCart(response);
                calculateTotals();
            });

            $('#barcode').val('');
        });

        function addToCart(product) {

            let existing = cart.find(item => item.id === product.id);

            if (existing) {
                existing.quantity++;
                existing.total = existing.quantity * existing.price;
            } else {
                cart.push({
                    product_id: product.id,
                    name: product.name,
                    quantity: 1,
                    price: parseFloat(product.price),
                    total: parseFloat(product.price)
                });
            }

            renderCart();
        }

        function renderCart() {
            let html = '';
            cart.forEach((item, index) => {
                html += `
                                    <tr>
                                        <td>${item.name}</td>
                                        <td>${item.quantity}</td>
                                        <td>${item.price}</td>
                                        <td>${item.total}</td>
                                        <td><button onclick="removeItem(${index})">X</button></td>
                                    </tr>
                                    `;
            });

            $('#cart-table tbody').html(html);
        }

        function removeItem(index) {
            cart.splice(index, 1);
            renderCart();
            calculateTotals();
        }

        function calculateTotals() {

            let subtotal = cart.reduce((sum, item) => sum + item.total, 0);
            let tax = subtotal * 0.05;
            let grand_total = subtotal + tax;

            $('#subtotal').text(subtotal.toFixed(2));
            $('#tax').text(tax.toFixed(2));
            $('#grand_total').text(grand_total.toFixed(2));
        }

        $('#complete-sale').click(function () {

            $.post("{{ route('billing.complete') }}", {
                _token: "{{ csrf_token() }}",
                items: cart,
                subtotal: $('#subtotal').text(),
                tax: $('#tax').text(),
                discount: 0,
                total: $('#grand_total').text(),
                paid_amount: $('#paid_amount').val(),
                change_amount: 0,
                payment_method: $('#payment_method').val()
            }, function (response) {

                if (response.success) {
                    alert("Sale Completed!");
                    location.reload();
                }
            });
        });
    </script>
    <!-- <script>
                                                                                                                                                                                                                                                                                                                                document.addEventListener('click', () => {
                                                                                                                                                                                                                                                                                                                                    document.getElementById('barcode').focus();
                                                                                                                                                                                                                                                                                                                                });

                                                                                                                                                                                                                                                                                                                                document.getElementById('barcode').addEventListener('keydown', function (e) {
                                                                                                                                                                                                                                                                                                                                    if (e.key === 'Enter') {
                                                                                                                                                                                                                                                                                                                                        e.preventDefault();

                                                                                                                                                                                                                                                                                                                                        let barcode = this.value.trim();
                                                                                                                                                                                                                                                                                                                                        let routeName = "{{ request()->route()->getName() }}";

                                                                                                                                                                                                                                                                                                                                        if (barcode !== '') {

                                                                                                                                                                                                                                                                                                                                            fetch("{{ route('admin.barcode.validateBarcode') }}", {
                                                                                                                                                                                                                                                                                                                                                method: "POST",
                                                                                                                                                                                                                                                                                                                                                headers: {
                                                                                                                                                                                                                                                                                                                                                    "Content-Type": "application/json",
                                                                                                                                                                                                                                                                                                                                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                                                                                                                                                                                                                                                                                                                                },
                                                                                                                                                                                                                                                                                                                                                body: JSON.stringify({ barcode: barcode, routeName: routeName })
                                                                                                                                                                                                                                                                                                                                            })
                                                                                                                                                                                                                                                                                                                                                .then(res => res.json())
                                                                                                                                                                                                                                                                                                                                                .then(data => {
                                                                                                                                                                                                                                                                                                                                                    // console.log(data); return;
                                                                                                                                                                                                                                                                                                                                                    if (data.adjustmentType > 1 && data.status == false) {
                                                                                                                                                                                                                                                                                                                                                        Swal.fire({
                                                                                                                                                                                                                                                                                                                                                            icon: 'warning',
                                                                                                                                                                                                                                                                                                                                                            title: 'Invalid Barcode',
                                                                                                                                                                                                                                                                                                                                                            text: 'This barcode is not allowed for this operation!',
                                                                                                                                                                                                                                                                                                                                                            confirmButtonText: 'OK'
                                                                                                                                                                                                                                                                                                                                                        });
                                                                                                                                                                                                                                                                                                                                                        return;
                                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                                    new Audio('/beep.wav').play();
                                                                                                                                                                                                                                                                                                                                                    if (data.status) {
                                                                                                                                                                                                                                                                                                                                                        var route = "{{ route('admin.inventory.update', 'TOKEN') }}"
                                                                                                                                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                                                                                                                                        var route = "{{ route('admin.products.create', 'TOKEN') }}"
                                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                                    let url = route.replace('TOKEN', encodeURIComponent(data.payload));
                                                                                                                                                                                                                                                                                                                                                    window.location.href = url;
                                                                                                                                                                                                                                                                                                                                                })
                                                                                                                                                                                                                                                                                                                                                .catch(err => {
                                                                                                                                                                                                                                                                                                                                                    console.error(err);
                                                                                                                                                                                                                                                                                                                                                });

                                                                                                                                                                                                                                                                                                                                            this.value = '';
                                                                                                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                });
                                                                                                                                                                                                                                                                                                                            </script> -->
@endsection