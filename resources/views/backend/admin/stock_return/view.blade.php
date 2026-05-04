@extends('backend.layouts.master-horizontal')

@section('content')
@include('backend.components.breadcrumb')

<div class="card">

    <div class="card-header">
        <h4>Stock Return Details</h4>
    </div>

    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-4"><strong>Return No:</strong> {{ $return->return_no }}</div>
            <div class="col-md-4"><strong>Vendor:</strong> {{ $return->vendor->name }}</div>
            <div class="col-md-4"><strong>Warehouse:</strong> {{ $return->warehouse->name }}</div>
            <div class="col-md-4"><strong>Date:</strong> {{ $return->created_at }}</div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>
                @foreach($return->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ $item->price }}</td>
                    <td>{{ $item->total }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-end">
            <h4>Total: {{ $return->total }}</h4>
        </div>

        <div class="text-end mt-3">
            <a href="{{ route('admin.stock_returns.index') }}" class="btn btn-secondary">Back</a>
        </div>

    </div>
</div>
@endsection