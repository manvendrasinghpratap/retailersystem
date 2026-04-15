@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? __('translation.add_order') }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm rounded-2xl">
                <div class="card-header">
                    <h4 class="card-title">{{ array_key_exists('routeTitle', $breadcrumb) ? $breadcrumb['routeTitle'] : '' }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="label vital-signs">{{__('translation.order') }} {{__('translation.details') }}</div>
                    </div>

                    {{-- Order Form --}}
                    <form
                        action="{{ isset($order) ? route($breadcrumb['updateroute'], $id) : route($breadcrumb['addroute']) }}"
                        method="POST" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="off"
                        id="orderForm">
                        @csrf
                        @if (isset($order))
                            @method('PUT')
                        @endif

                        <input type="hidden"
                            value="{{ !empty($order) ? \App\Helpers\Settings::getEncodeCode($order->id) : '' }}"
                            name="order_id" id="order_id" />

                        {{-- Customer + Date --}}
                        <div class="row">
                            <x-select-dropdown name="customer_id" label="Customer" :options="$customers" :selected="$order->customer_id ?? ''"
                                class="{{ isset($order) ? 'disabledoption' : 'customer hundredper required-select customer-select' }}"    required   />
                            <x-select-dropdown name="staff_id" label="Staff" :options="$staffList" :selected="$order->staff_id ?? Auth::user()->id"
                                class="{{ isset($order) ? 'disabledoption' : 'staff hundredper required-select staff-select' }}"    required   />
                            <x-date-input name="ordered_at" :label="__('translation.ordered_at')"
                                value="{{ !empty($order) && $order->ordered_at ? \App\Helpers\Settings::getFormattedDate($order->ordered_at)  :  \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}"
                                required class="flatdatepickr ordered_at fourtyper"
                                data-mindate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-1 month'))) }}"
                                data-maxdate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('+1 year'))) }}" 
                               :disabled="!empty($order) && $order->ordered_at"
                                />
                            
                        </div>

                        <div class="row">
                            <div class="label vital-signs">{{__('translation.product') }} {{__('translation.order') }}</div>
                        </div>

                        {{-- Products Section --}}
                        {{-- First Product Row --}}
                        @if (!isset($order) || $order->orderdetails->isEmpty())
                            <div class="row product-row mb-3" data-free="0">
                                    <div class="col-xl-3 col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="field-label">@lang('translation.product')</label>
                                            <select name="product_id[]" class="form-control products setrateonchange required-select" required>
                                                <option value="">Select Product</option>
                                                @foreach ($product as $menu)
                                                    @php
                                                        $message = '';
                                                        if ($menu->bulkOffer) {
                                                            $freeTitle = $menu->bulkOffer->freeMenu
                                                                ? $menu->bulkOffer->freeMenu->title
                                                                : 'Free Item';
                                                            $message = "Buy {$menu->bulkOffer->buy_quantity} {$menu->title}, get {$menu->bulkOffer->free_quantity} {$freeTitle} free!";
                                                        }
                                                    @endphp
                                                    <option value="{{ $menu->id }}" data-price="{{ $menu->price }}"
                                                    @if ($menu->bulkOffer) data-buy-qty="{{ $menu->bulkOffer->buy_quantity }}"
                                                    data-free-qty="{{ $menu->bulkOffer->free_quantity }}"
                                                    data-free-title="{{ $menu->bulkOffer->freeMenu ? $menu->bulkOffer->freeMenu->title : 'Free Item' }}"
                                                    data-message="{{ $message }}"
                                                    data-bulk-offer-id="{{ $menu->bulkOffer->id }}" @endif>{{ $menu->title }}</option>
                                                @endforeach
                                            </select>
                                            <div class="product-message text-danger mt-1"></div>
                                        </div>
                                    </div>
                                    <x-text-input name="price[]" type="number" :label="__('translation.price')" value="0" placeholder="Price" class="form-control price" readonly mainrows="2" />
                                    <x-text-input name="quantity[]" type="number" :label="__('translation.quantity')" value="0" placeholder="Quantity" class="form-control quantity onlyinteger default-zero" min="0" mainrows="2" />
                                    <x-text-input name="free_quantity[]" type="number" :label="__('translation.free_quantity')" value="0" class="form-control free-quantity" readonly mainrows="2" />
                                    <div class="col-xl-2 col-md-4 ">
                                        <button type="button" class="btn btn-danger btn-sm remove-product d-none"><i class="fas fa-minus"></i></button>
                                        <input type="hidden" name="bulk_offer_id[]" class="form-control bulk-offer-id" readonly value="0" />
                                    </div>
                                </div>
                        @else     
                        <?php $headerShown = 0; ?>              
                        @foreach ( $order->orderdetails as $detail )  
                                <div class="row product-row mb-3" data-free="0">
                                    <div class="col-xl-3 col-md-4">
                                        <div class="form-group mb-3">
                                            @if($headerShown == 0)<label class="field-label">@lang('translation.product')</label> @endif
                                            <select name="product_id[]" class="form-control disabledoption "  disabled required>
                                                <option value="">Select Product</option>
                                                @foreach ($product as $menu)
                                                    @php
                                                        $message = '';
                                                        if ($menu->bulkOffer) {
                                                            $freeTitle = $menu->bulkOffer->freeMenu
                                                                ? $menu->bulkOffer->freeMenu->title
                                                                : 'Free Item';
                                                            $message = "Buy {$menu->bulkOffer->buy_quantity} {$menu->title}, get {$menu->bulkOffer->free_quantity} {$freeTitle} free!";
                                                        }
                                                    @endphp
                                                    <option value="{{ $menu->id }}" data-price="{{ $menu->price }}"
                                                        @if ($menu->bulkOffer) data-buy-qty="{{ $menu->bulkOffer->buy_quantity }}"
                                                data-free-qty="{{ $menu->bulkOffer->free_quantity }}"
                                                data-free-title="{{ $menu->bulkOffer->freeMenu ? $menu->bulkOffer->freeMenu->title : 'Free Item' }}"
                                                data-message="{{ $message }}"
                                                data-bulk-offer-id="{{ $menu->bulkOffer->id }}" @endif
                                                @if (isset($detail) && $detail->menus_id == $menu->id) selected @endif                                                
                                                >{{ $menu->title }}</option>
                                                @endforeach
                                            </select>
                                            <div class="product-message text-danger mt-1"></div>
                                        </div>
                                    </div>
                                        <x-text-input name="price[]" type="text" :label="__('translation.price')" value="{{ $detail->price }}" placeholder="Price" class="form-control price" disabled mainrows="2" islabel="{{$headerShown}}" />
                                        <x-text-input name="quantity[]" type="text" :label="__('translation.quantity')" value="{{ $detail->quantity }}" placeholder="Quantity" class="form-control quantity onlyinteger default-zero" min="0" disabled mainrows="2" islabel="{{$headerShown}}" />
                                        <x-text-input name="free_quantity[]" type="text" :label="__('translation.free_quantity')" value="{{ $detail->free_quantity }}" class="form-control free-quantity" readonly disabled mainrows="2" islabel="{{$headerShown}}" />

                                    <div class="col-xl-2 col-md-4 ">
                                        <button type="button" class="btn btn-danger btn-sm remove-product d-none"><i class="fas fa-minus"></i></button>
                                        <input type="hidden" name="bulk_offer_id[]" class="form-control bulk-offer-id" readonly value="0" />
                                    </div>
                                </div>
                                 <?php $headerShown = 1; ?>
                        @endforeach
                        @endif
                        {{-- End of First Product Row --}}

                        {{-- Add More Button --}}
                         @if (!isset($order) || $order->orderdetails->isEmpty())
                            <div class="row mb-3">
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="button" class="btn btn-secondary btn-sm add-more-product">
                                        <i class="fas fa-plus"></i> Add More Product
                                    </button>
                                </div>
                            </div>
                        @endif
                        {{-- End of Products Section --}}

                        {{-- Total Amount and Payment Section --}}
                        <div class="row">
                            <div class="label vital-signs">@lang('translation.total_amount_to_paid_customer')</div>
                        </div>

                        <div class="row mb-4">
                            <x-text-input name="total_amount" id="total_amount" type="text" :label="__('translation.total_amount')" :value="$order->total_amount ?? 0.00" class="form-control font-weight-bold" readonly mainrows="3" />
                            <x-text-input name="total_quantity" id="total_quantity" type="text" :label="__('translation.total_quantity')" :value="$order->total_quantity ?? 0" class="form-control" readonly mainrows="3" />
                            <x-text-input name="total_free_quantity" id="total_free_quantity" type="text" :label="__('translation.free_product')" :value="$order->total_free_quantity ?? 0" class="form-control" readonly mainrows="3" />

                        </div>
                        <div class="row">
                            <div class="label vital-signs">@lang('translation.payment_method') <span id="payment_error" class="text-danger font-weight-bold"></span></div>
                        </div>
                        <div class="row mb-0">
                            <x-select-dropdown name="payment_way" :label="__('translation.payment_way')" :options="\Config::get( 'constants.payment_way' )" :selected="(isset($order) && $order->orderpaymentdetails()->count() > 0 )? $order->orderpaymentdetails[0]->payment_way : ''" class="{{ isset($order) ? 'disabledoption' : 'payment_way form-control required-select' }}"  mainrows="4" required/>
                        </div>
                        <div class="row mb-3 payment-methods">
                            <x-text-input name="payment_cash" id="payment_cash" :label="__('translation.cash')" :value="(isset($order) && $order->orderpaymentdetails()->count() > 0) ? $order->orderpaymentdetails[0]->payment_cash : ''" required class="payment-amount default-zero" type="number" min="0" :disabled="!empty($order) && $order->orderpaymentdetails()->count() > 0" />
                            <x-text-input name="payment_pos" id="payment_pos" :label="__('translation.pos')" :value="(isset($order) && $order->orderpaymentdetails()->count() > 0) ? $order->orderpaymentdetails[0]->payment_pos : ''" required class="payment-amount default-zero" type="number" min="0" :disabled="!empty($order) && $order->orderpaymentdetails()->count() > 0" />
                           <x-text-input name="payment_transfer" id="payment_transfer" :label="__('translation.transfer')" :value="(isset($order) && $order->orderpaymentdetails()->count() > 0) ? $order->orderpaymentdetails[0]->payment_transfer : ''" required class="payment-amount default-zero" type="number" min="0" :disabled="!empty($order) && $order->orderpaymentdetails()->count() > 0" />

                        </div>
                         <div class="row">
                            <div class="label vital-signs">@lang('translation.payment') @lang('translation.status') and  @lang('translation.comment') <span id="payment_error" class="text-danger font-weight-bold"></span></div>
                        </div>
                        <div class="row mb-0">
                            <x-select-dropdown name="payment_status" :label="__('translation.payment'). ' '. __('translation.status')" :options="\Config::get( 'constants.payment_status' )" :selected="(isset($order) && $order->payment_status)? $order->payment_status : ''" class="{{ isset($order) ? 'disabledoption' : 'payment_status form-control required-select' }}"  mainrows="4" required/>
                            <x-textarea-input name="comment" id="comment" :label="__('translation.comment')" value="{{ old('comment', $order->comment ?? '') }}" class="form-control" rows="1" mainrows="8" :disabled="!empty($order)"/>    
                        </div>
                        {{-- Form Buttons --}}
                        <div class="row mb-3">
                            <div class="form-group mt-6">
                                <x-form-buttons submitText="{{ $submitText ?? 'Update' }}"
                                    resetText="{{ __('translation.cancel') }}"
                                    url="{{ route(array_key_exists('add_new_route', $breadcrumb) ? $breadcrumb['add_new_route'] : 'javascript:void(0)') }}"
                                    class="btn-success" :iscancel="true"  :isbutton="(isset($order))  ? false : true"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @if(!isset($order))   
    <script type="text/javascript">
        $(document).ready(function() {
            // ======= 1) Capture template row =======
            var templateRow = $('.product-row:first').clone();
            templateRow.find('.field-label').remove();
            templateRow.find('input').val('0');
            templateRow.find('select').val('');
            templateRow.find('.product-message').text('');

            // ======= 2) Initialize Select2 =======
            function initSelect2(select) {
                select.select2({
                    placeholder: "Select Product",
                    allowClear: true,
                    width: '100%'
                }).on('select2:select', function() {
                    var row = $(this).closest('.product-row');
                    var price = $(this).find('option:selected').data('price') || 0;
                    row.find('.price').val(price.toFixed(2));
                    calculateAmount(row);
                    row.find('.quantity').focus('');

                    // Show product message
                    var message = $(this).find('option:selected').data('message') || '';
                    row.find('.product-message').text(message);
                });
            }

            // Initialize Select2 for existing rows
            $('.products').each(function() {
                initSelect2($(this));
            });

            // ======= 3) Default-zero fields =======
            function setDefaultZero() {
                $('.default-zero').each(function() {
                    if ($.trim($(this).val()) === '') $(this).val(0);
                });
            }
            setDefaultZero();

            $(document).on('blur', '.default-zero', function() {
                if ($(this).val() === '') $(this).val(0);
                var row = $(this).closest('.product-row');
                calculateAmount(row);
                updateTotalAmount();
                updateRemainingAmount();
            });

            $(document).on('click', '.default-zero', function() {
                if ($(this).val() === '0') $(this).val('');
            });

            // ======= 4) Calculate row amount =======
            function calculateAmount(row) {
                var price = parseFloat(row.find('.price').val()) || 0;
                var quantity = parseInt(row.find('.quantity').val()) || 0;
                var amount = price * quantity;
                row.find('.amount').val(amount.toFixed(2));

                // Bulk offer logic
                var selectedOption = row.find('.products option:selected');
                var buyQty = parseInt(selectedOption.data('buy-qty')) || 0;
                var freeQty = parseInt(selectedOption.data('free-qty')) || 0;
                var bulkOfferId = parseInt(selectedOption.data('bulk-offer-id')) || 0;

                var totalFree = 0;
                if (buyQty > 0 && freeQty > 0 && quantity >= buyQty) {
                    var multiplier = Math.floor(quantity / buyQty);
                    totalFree = multiplier * freeQty;

                    // Store bulk_offer_id only if free products applied
                    row.find('.bulk-offer-id').val(totalFree > 0 ? bulkOfferId : 0);
                } else {
                    row.find('.bulk-offer-id').val(0);
                }

                // Update free quantity field
                row.find('.free-quantity').val(totalFree);

                // Update totals
                updateTotalAmount();
            }

            // ======= 5) Update total amounts =======
            function updateTotalAmount() {
                var totalAmount = 0,
                    totalQty = 0,
                    totalFreeQty = 0;

                $('.product-row').each(function() {
                    var row = $(this);
                    var price = parseFloat(row.find('.price').val()) || 0;
                    var qty = parseInt(row.find('.quantity').val()) || 0;
                    totalAmount += price * qty;
                    totalQty += qty;

                    var selectedOption = row.find('.products option:selected');
                    var buyQty = parseInt(selectedOption.data('buy-qty')) || 0;
                    var freeQty = parseInt(selectedOption.data('free-qty')) || 0;
                    var bulkOfferId = parseInt(selectedOption.data('bulk-offer-id')) || 0;

                    var freeCount = 0;
                    if (buyQty > 0 && freeQty > 0 && qty >= buyQty) {
                        var multiplier = Math.floor(qty / buyQty);
                        freeCount = multiplier * freeQty;
                        row.find('.bulk-offer-id').val(freeCount > 0 ? bulkOfferId : 0);
                    } else {
                        row.find('.bulk-offer-id').val(0);
                    }

                    row.find('.free-quantity').val(freeCount);
                    totalFreeQty += freeCount;
                });

                // $('#total_amount').val(totalAmount.toLocaleString('en-IN', {
                //     minimumFractionDigits: 2,
                //     maximumFractionDigits: 2
                // }));
                 $('#total_amount').val(totalAmount);
                $('#total_quantity').val(totalQty + totalFreeQty);
                $('#total_free_quantity').val(totalFreeQty);
            }

            // ======= 6) Add / Remove product rows =======
            $('.add-more-product').on('click', function() {
                var newRow = templateRow.clone();
                newRow.find('input').val('0');
                newRow.find('select').val('');
                newRow.find('.remove-product').removeClass('d-none');
                newRow.find('.bulk-offer-id').val(0);
                newRow.find('.free-quantity').val(0);
                newRow.find('.product-message').text('');

                $('.product-row:last').after(newRow);

                initSelect2(newRow.find('.products'));
            });

            $(document).on('click', '.remove-product', function() {
                $(this).closest('.product-row').remove();
                updateTotalAmount();
            });

            // ======= 7) Quantity input =======
            $(document).on('input', '.quantity, .price', function() {
                var row = $(this).closest('.product-row');
                calculateAmount(row);
            });

            // ======= 8) Payment handling =======
            function updateRemainingAmount() {
                var totalAmount = parseFloat($('#total_amount').val().replace(/,/g, '')) || 0;

                var cash = parseFloat($('#payment_cash').val()) || 0;
                var pos = parseFloat($('#payment_pos').val()) || 0;
                var transfer = parseFloat($('#payment_transfer').val()) || 0;

                var totalPaid = cash + pos + transfer;
                var remaining = totalAmount - totalPaid;

                $('#remaining_amount').val(remaining.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                if (remaining > 0) {
                    $('#remaining_amount').css('color', 'red');
                } else {
                    $('#remaining_amount').css('color', 'green');
                }

                if (totalPaid > totalAmount) {
                    $('#payment_error').text("Total paid amount cannot exceed total order amount.");
                } else if (remaining > 0 && $('#payment_way').val() == '2') {
                    $('#payment_error').text("Partial payment made. Remaining amount: " + remaining.toLocaleString(
                        'en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                } else {
                    $('#payment_error').text('');
                }
            }

            $('#payment_way').on('change', function() {
                var paymentWay = $(this).val();
                $('.payment-amount').val(0).prop('disabled', false);

                if (paymentWay == '1') {
                    $('.payment-amount').off('input').on('input', function() {
                        if (parseFloat($(this).val()) > 0) {
                            $('.payment-amount').not(this).val(0).prop('disabled', true);
                        } else {
                            $('.payment-amount').prop('disabled', false);
                        }
                        updateRemainingAmount();
                    });
                } else if (paymentWay == '2') {
                    $('.payment-amount').prop('disabled', false);
                    $('.payment-amount').off('input');
                }
                updateRemainingAmount();
            });

            $('#payment_way').trigger('change');

            $(document).on('blur', '.payment-amount', function() {
                if ($(this).val() === '') $(this).val(0);
                updateRemainingAmount();
            });

            $(document).on('click', '.payment-amount', function() {
                if ($(this).val() === '0') $(this).val('');
            });

            // ======= 9) Initial calculation =======
            $('.product-row').each(function() {
                calculateAmount($(this));
            });

        });
    </script>
    @endif

   <script>
        $(document).ready(function() {
    // Function to initialize Select2
            function initSelect2(select) {
                    select.select2({
                        placeholder: "Select an option",
                        allowClear: true,
                        width: '100%'
                    });

                    // Remove error highlight on change
                    select.on('change', function() {
                        $(this).next('.select2-container').find('.select2-selection').removeClass('error');
                    });
            }

            // Initialize Select2 for all existing required selects
            $('.required-select').each(function() {
                initSelect2($(this));
            });

    // Form submit validation
            $('#orderForm').on('submit', function(e) {
                    var isValid = true;

                    $('.required-select').each(function() {
                        var value = $(this).val();
                        var container = $(this).next('.select2-container');

                        container.find('.select2-selection').removeClass('error');

                        if (!value || value === '') {
                            isValid = false;
                            container.find('.select2-selection').addClass('error');
                        }
                    });

                    if (!isValid) {
                        e.preventDefault(); // Stop form submission if invalid
                    }
            });

    
    });

    </script>
@endsection

