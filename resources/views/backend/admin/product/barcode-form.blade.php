<form method="POST" action="{{ route('barcode.print') }}" target="_blank">
    @csrf
    <input type="hidden" name="product_id" value="{{ \App\Helpers\Settings::getEncodeCodeWithHashids($product->id) }}">
    <h5>{{ $product->name }}</h5>
    <span class="badge badge-success mb-2">{{ $product->stock->stock }} Available Stock</span>
    <div class="mb-3">
        <label>{{__('translation.quantity')}}</label>
        <input type="number" name="qty" value="1" class="form-control" @error('qty') is-invalid @enderror min="1" max="10000" placeholder="Enter quantity" onkeydown="if (event.key === '-' || event.key === 'e') event.preventDefault()" oninput="if (this.value < 1) this.value = 1">
        @error('qty')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="mb-3">
        <label>{{__('translation.paper')}}</label>
        <select name="size" class="form-control" @error('size') is-invalid @enderror>
            <option value="a4">A4</option>
            <option value="thermal">Thermal</option>
        </select>
        @error('size')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <button class="btn btn-success w-100">
        Print
    </button>

</form>