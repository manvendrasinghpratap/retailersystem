@extends('backend.layouts.master-horizontal')

@section('content')

@include('backend.components.breadcrumb')

<div class="card">
    <div class="card-header">
        <h4>Return Items</h4>
    </div>

    <form method="POST" action="{{ route('admin.purchase_returns.store') }}">
        @csrf

        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

        <div class="card-body">

            <div class="mb-3">
                <strong>Vendor:</strong> {{ $purchase->vendor->name }} <br>
                <strong>Warehouse:</strong> {{ $purchase->warehouse->name }}
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Purchased Qty</th>
                        <th>Return Qty</th>
                        <th>Price</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($purchase->items as $i => $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>

                        <td>
                            <input type="number"
                                name="items[{{ $i }}][qty]"
                                class="form-control qty"
                                value="0"
                                min="0"
                                max="{{ $item->quantity }}">
                        </td>

                        <td>{{ $item->cost_price }}</td>

                        <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
                        <input type="hidden" name="items[{{ $i }}][price]" value="{{ $item->cost_price }}">
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

        <div class="card-footer text-end">
            <button type="submit" class="btn btn-danger">Submit Return</button>
        </div>

    </form>
</div>

@endsection