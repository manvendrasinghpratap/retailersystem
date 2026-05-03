@extends('backend.layouts.master-horizontal')
@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection
@section('content')

@include('backend.components.breadcrumb') 

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    {{ array_key_exists('title1', $breadcrumb) ? $breadcrumb['title'] : __('translation.name') }} : <i>{{ $vendor->name }}</i> | {{ __('translation.current_due') }} : <i>{{ __('translation.currency') }} {{ (\App\Helpers\Settings::getcustomnumberformat($vendor->current_balance)) }}</i>
                </h4>
            </div>

            <div class="card-body">
                {{-- Payment Form --}}
                <form id="vendorPaymentForm" method="POST" action="{{ route('admin.vendors.paymentStore') }}" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="vendor_id" value="{{ \App\Helpers\Settings::getEncodeCode($vendor->id) }}">
                    <div class="row">
                        {{-- Payment Date --}}
                        <x-text-input name="payment_date" :label="__('translation.payment_date')" value="{{ \App\Helpers\Settings::formatDate(date('Y-m-d'),Config::get('constants.dateformat.slashdmyonly')) }}" mainrows="4" class="flatdatepickr" />

                        {{-- Amount --}}
                        <x-text-input name="amount" type="number" step="0.01" label="Amount" required />

                        {{-- Payment Method --}}
                        <x-select-dropdown  name="payment_method"  id="payment_method"
                            label="Payment Method"
                            :options="config('constants.customer_payment_method')"
                            required
                            class="payment_method"
                        />
                        {{-- Reference No --}}
                        <x-text-input name="reference_no" label="Reference No" value="" />

                        {{-- Notes --}}
                        <x-text-input name="notes" label="Notes" value="" mainrows="8" />

                    </div>

                    {{-- Buttons --}}
                    <div class="row">
                        <x-form-buttons
                            submitText="{{ __('translation.Savepayment') }}"
                            resetText="{{ __('translation.cancel') }}"
                            url="{{ route('admin.vendors.index') }}" />
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

@endsection

@section('script')
<script>
    /////Single select2 field in form
    validateSelect2Form('vendorPaymentForm', ['payment_method']);
    /////Multiple select2 fields in one form
    //validateSelect2Form('purchaseForm', ['vendor_id','warehouse_id','payment_method']);
</script>

@endsection


