@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.customer_name') }}</th>
                <th>{{ __('translation.phone') }}</th>
                <th>{{ __('translation.email') }}</th>
                <th>{{ __('translation.wallet_balance') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.createdat') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($customers) && $customers->count() > 0)
                @foreach($customers as $customer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ __('translation.currency') . number_format($customer->wallet_balance, 2) }}</td>
                        <td>
                            @if($customer->status == 1)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ \App\Helpers\Settings::getFormattedDatetime($customer->created_at) }}</td>
                    </tr>
                @endforeach

            @else
                <tr>
                    <td colspan="7" class="text-center">
                        No customers available
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection