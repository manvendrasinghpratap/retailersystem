@extends('backend.layouts.master-horizontal')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Purchase Details</h4>
    </div>

    <div class="card-body">

        <p><strong>Purchase No:</strong> {{ $purchase->purchase_no }}</p>
        <p><strong>Vendor:</strong> {{ $purchase->vendor->name }}</p>
        <p><strong>Warehouse:</strong> {{ $purchase->warehouse->name }}</p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>
                @foreach($purchase->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->cost_price }}</td>
                    <td>{{ $item->total }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h4 class="text-end">Total: {{ $purchase->total }}</h4>

    </div>
</div>

@endsection