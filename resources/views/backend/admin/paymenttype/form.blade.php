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
                        {{ request()->route()->getName() == 'admin.payment-types.create' ? $breadcrumb['route2Title'] : $breadcrumb['route3Title'] }}
                    </h4>
                </div>
                <div class="card-body">
                    <form autocomplete="off" method="POST" id="paymentTypeForm" name="paymentTypeForm" action="{{ isset($paymentType) ? route('admin.payment-types.update', \App\Helpers\Settings::getEncodeCode($paymentType->id)) : route('admin.payment-types.store') }}" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="payment_type_id" id="payment_type_id" value="{{ isset($paymentType) ? \App\Helpers\Settings::getEncodeCode($paymentType->id) : '' }}">
                        <div class="row">
                            {{-- Name --}}
                            <x-text-input name="name" label="{{ __('translation.name') }}" value="{{ $paymentType->name ?? '' }}" required />
                            {{-- Status --}}
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="isset($paymentType) && $paymentType->status == 0 ? 0 : 1" class="accountstatus" required />
                        </div>
                        <div class="row">
                            <x-form-buttons submitText="{{ isset($paymentType) ? __('translation.update') : __('translation.save') }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection