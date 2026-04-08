@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }} |
    {{ array_key_exists('route1Title', $breadcrumb) ? $breadcrumb['route1Title'] : '' }}
@endsection

@section('content')

    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title">
                        {{ request()->route()->getName() == 'admin.coupons.create'
        ? $breadcrumb['route2Title']
        : $breadcrumb['route3Title'] }}
                    </h4>
                </div>

                <div class="card-body">
                    <form autocomplete="off" method="POST" id="couponform" name="couponform" action="{{ isset($coupon)
        ? route('admin.coupons.update', \App\Helpers\Settings::getEncodeCode($coupon->id))
        : route('admin.coupons.store') }}" class="needs-validation" novalidate>

                        @csrf

                        <input type="hidden" value="{{ isset($coupon) ? \App\Helpers\Settings::getEncodeCode($coupon->id) : '' }}" name="coupon_id" id="coupon_id" />

                        <div class="row">

                            {{-- Coupon Code --}}
                            <x-text-input name="code" label="Coupon Code" value="{{ $coupon->code ?? '' }}" required />

                            {{-- Type --}}
                            <x-select-dropdown name="type" label="Discount Type" :options="config('constants.discount_type')" :selected="$coupon->type ?? ''" required />

                            {{-- Value --}}
                            <x-text-input name="value" label="Discount Value {{ __('translation.b_ngn') }}" type="number" step="0.01" value="{{ $coupon->value ?? '' }}" required min="0" />

                            {{-- Minimum Amount --}}
                            <x-text-input name="min_amount" label="Minimum Amount {{ __('translation.b_ngn') }}" type="number" step="0.01" value="{{ $coupon->min_amount ?? '' }}" min="0" />

                            {{-- Max Discount (For Percent) --}}
                            <x-text-input name="max_discount" label="Max Discount (For Percent Only) {{ __('translation.b_ngn') }}" type="number" step="0.01" value="{{ $coupon->max_discount ?? '' }}" min="0" />

                            {{-- Expiry Date --}}
                            <x-text-input name="expires_at" label="Expiry Date" type="text" value="{{ $coupon->expires_at ?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('+2 years')))  }}" required class="flatdatepickr expires_at" data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('0 days')))}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('+2 years')))}}" />

                            {{-- Status --}}
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="isset($coupon) && $coupon->is_active == 0 ? 0 : 1" required class="accountstatus" />

                        </div>

                        {{-- Buttons --}}
                        <div class="row">
                            <x-form-buttons submitText="{{ isset($coupon) ? 'Update' : 'Save' }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
@endsection