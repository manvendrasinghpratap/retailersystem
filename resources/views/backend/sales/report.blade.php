@extends('layouts.app')
@section('content')
<div class="container">
    <h4>Sales Report</h4>

    <form class="form-inline mb-3" method="GET">
        <label>From:</label>
        <input type="date" name="from" class="form-control mx-2" value="{{ $from }}">
        <label>To:</label>
        <input type="date" name="to" class="form-control mx-2" value="{{ $to }}">
        <select name="user_id" class="form-control mx-2">
            <option value="">-- All Staff --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @if($user_id == $user->id) selected @endif>{{ $user->name }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-primary">Show</button>
    </form>

    @if($summary->isEmpty())
        <div class="alert alert-warning">No data found for selected range.</div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Taken</th>
                    <th>Sold</th>
                    <th>Returned</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $staffId => $sum)
                    @php $staff = $users->firstWhere('id', $staffId); @endphp
                    <tr>
                        <td>{{ $staff->name ?? 'N/A' }}</td>
                        <td>{{ $sum['taken'] }}</td>
                        <td>{{ $sum['sold'] }}</td>
                        <td>{{ $sum['returned'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
