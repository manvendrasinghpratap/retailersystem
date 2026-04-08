@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection

@section('content')
    @include('backend.components.breadcrumb')

    {{-- FILTER SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                </div>

                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="code" label="Coupon Code" value="{{ request()->get('code') ?? '' }}" mainrows="3" />

                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="request()->get('status') ?? ''" mainrows="2" class="accountstatus" />

                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>

                                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" />

                                    <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- LISTING SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
                        {{ __('translation.listing') }}
                    </h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive overflowx">
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
                                    <th>{{ __('translation.actions') }}</th>
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
                                                    ₹{{ number_format($coupon->value, 2) }}
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
                                            <td>
                                                <x-href-input name="edit" label="Edit" required href="{{ route('admin.coupons.edit', ['id' => \App\Helpers\Settings::getEncodeCode($coupon->id)]) }}" />
                                                <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($coupon->id) }}" data-routeurl="{{ route('admin.coupons.softdelete', $coupon->id) }}" />
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="10" class="text-center">No Coupons Available</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if(!empty($coupons) && $coupons->count() > 0)
                        <div class="right user-navigation">
                            {!! $coupons->appends(request()->input())->links() !!}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

@endsection