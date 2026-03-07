@extends('layouts.app')
@section('content')
<div class="container">
    <h4>📦 Product-Wise Sales Summary</h4>

    <form class="form-inline mb-3" method="GET">
        <label>From:</label>
        <input type="date" name="from" class="form-control mx-2" value="{{ $from }}">
        <label>To:</label>
        <input type="date" name="to" class="form-control mx-2" value="{{ $to }}">
        <button class="btn btn-sm btn-primary">Show</button>
        <a href="{{ route('sales.index') }}" class="btn btn-sm btn-secondary ml-2">Back to Daily List</a>
    </form>

    @if($summary->isEmpty())
        <div class="alert alert-warning">No records found for selected date range.</div>
    @else
        <table class="table table-bordered">
            <thead class="bg-light">
                <tr>
                    <th>Product</th>
                    <th>Total Taken</th>
                    <th>Total Sold</th>
                    <th>Total Returned</th>
                    <th>Mismatches</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $item)
                    <tr @if($item['mismatch_count'] > 0) style="background-color: #fff3cd;" @endif>
                        <td>{{ $item['title'] }}</td>
                        <td>{{ $item['taken'] }}</td>
                        <td>{{ $item['sold'] }}</td>
                        <td>{{ $item['returned'] }}</td>
                        <td>
                            @if($item['mismatch_count'] > 0)
                                ⚠️ {{ $item['mismatch_count'] }}
                            @else
                                ✅ 0
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
