@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}}
@endsection
@section('content')
@include('backend.components.breadcrumb')

<div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h4 class="card-title">{{array_key_exists('routeTitle',$breadcrumb)?$breadcrumb['routeTitle']:''}}</h4></div>
                <div class="card-body">
                    <form name="addcustomerform" id="addnewstockform"  method="POST" action="{{ route(array_key_exists($route,$breadcrumb)?$breadcrumb[$route]:'') }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="false" onSubmit="return validatedata();">
                    @csrf
                        <div class="row">
                            <x-select-dropdown  name="menu_id"  :label="__('translation.product')"  :options="$menus"    required class="form-control products required-select" mainrows="4"/>
                            <x-text-input name="quantity" type="text" :label="__('translation.quantity')" value="" class="form-control quantity onlyinteger default-zero" maxlength="3" mainrows="4" required/>
                            <x-date-input name="transaction_date"  :label="__('translation.transaction').' '. __('translation.date') " value="{{  \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" required class="flatdatepickr date" required  data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-1 days')) )}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d'))}}"/>
                            <x-textarea-input name="remarks" label="Remarks" value="" class="" placeholder="Please Enter Remarks" mainrows="12" rows="2"/>
                        </div>
                        <div class="row mb-3">
                            <div class="form-group mt-6">
                                <x-form-buttons submitText="{{ $submitText ?? 'Update' }}"
                                    resetText="{{ __('translation.cancel') }}"
                                    url="{{ route(array_key_exists('add_new_route', $breadcrumb) ? $breadcrumb['add_new_route'] : 'javascript:void(0)') }}"
                                    class="btn-success" :iscancel="true"  :isbutton="(isset($order))  ? false : true"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</div>
@endsection
@section('script')
<script>
$(document).ready(function() {
    setupSelect2WithValidation('#addnewstockform');
});
</script>
@endsection
