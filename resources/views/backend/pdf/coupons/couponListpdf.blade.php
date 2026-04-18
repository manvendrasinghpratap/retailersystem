@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.couponcode') }}</th>
                <th>{{ __('translation.type') }}</th>
                <th>{{ __('translation.value') }}</th>
                <th>{{ __('translation.minamount') }}</th>
                <th>{{ __('translation.maxdiscount') }}</th>
                <th>{{ __('translation.expirydate') }}</th>
                <th>{{ __('translation.status') }}</th>
                <th>{{ __('translation.createdat') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($coupons) && $coupons->count() > 0)
                @foreach($coupons as $coupon)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $coupon->code }}</td>
                        <td>
                            @if($coupon->type == 'flat')
                                <span class="badge bg-info">Flat</span>
                            @else
                                <span class="badge bg-primary">Percent</span>
                            @endif
                        </td>
                        <td>
                            @if($coupon->type == 'percent')
                                {{ $coupon->value }}%
                            @else
                                {{ __('translation.currency') . number_format($coupon->value, 2) }}
                            @endif
                        </td>
                        <td>{{ __('translation.currency') . number_format($coupon->min_amount ?? 0, 2) }}</td>
                        <td>{{ __('translation.currency') . number_format($coupon->max_discount ?? 0, 2) }}</td>
                        <td>{{ $coupon->expired_date }}</td>
                        <td>
                            @if($coupon->is_active == 1)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $coupon->created_date }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="10" class="text-center">{{ __('translation.no_coupons_available') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection