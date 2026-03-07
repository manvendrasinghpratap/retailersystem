<div class="row deleterow">
    <input type="hidden" name="new_product_id[]" id="new_product_id" value="{{ rand(1, 999) }}" />
        <div class="col-xl-3 col-md-4">
            <div class="form-group mb-3">
                {{-- <label for="product_id"  > @lang('translation.product') <span class="required error_product_id"></span></label> --}}
                <select name="product_id[]" id="product_id_{{ rand(10,1000) }}{{ rand(9,1000) }}" class="form-control products fourtyper setrateonchange" required>
                    <option value="">Select Product</option>
                    @foreach($product as $prod)
                        <option value="{{ $prod->id }}" data-price="{{ $prod->price }}">{{ $prod->title }}</option>
                    @endforeach
                </select> 
            </div>
        </div>
        <div class="col-xl-2 col-md-4 ">
            <div class="form-group mb-3 ">
                {{-- <label for="price"  > @lang('translation.price') <span class="required error_price"></span></label> --}}
                <input type="text" name="price[]" id="price_{{ rand(10,1000) }}{{ rand(9,1000) }}" class="form-control price" readonly placeholder="Price" value="0"/>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 ">
            <div class="form-group mb-3 ">
                {{-- <label for="quantity"  > @lang('translation.quantity') <span class="required error_quantity"></span></label> --}}
                <input type="text" name="quantity[]" id="quantity_{{ rand(10,1000) }}{{ rand(9,1000) }}" class="form-control quantity onlyinteger default-zero"  placeholder="Quantity" value="0"/>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 ">
            <div class="form-group mb-3 ">
                {{-- <label for="amount"  > @lang('translation.amount') <span class="required error_product_id"></span></label> --}}
                <input type="text" name="amount[]" id="amount_{{ rand(10,1000) }}{{ rand(9,1000) }}" class="form-control amount onlyinteger default-zero" readonly placeholder="Amount" value="0"/>
            </div>
        </div>
</div>
<script src="{{ URL::asset('/assets/js/customvalidatation.js?id=123467890') }}"></script>