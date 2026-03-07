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
                    <form name="addcustomerform" id="addnewstockform"  method="POST" action="{{ route(array_key_exists($route,$breadcrumb)?$breadcrumb[$route]:'') }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="false">
                    @csrf
                        <div class="row">
                            <input type="hidden" name="type" value="{{ $adjustment ?? 'IN' }}">
                            <x-select-dropdown  name="product_id"  :label="__('translation.product')"  :options="$products" required class="form-control products required-select" mainrows="4"/>
                            <x-text-input name="quantity" type="text" :label="__('translation.quantity')" value="" class="form-control quantity onlyinteger default-zero" maxlength="5" mainrows="4" required/>
                            <x-date-input name="date"  :label="__('translation.transaction').' '. __('translation.date') " value="{{  \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" required class="flatdatepickr date" required  data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('0 days')) )}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d'))}}" readonly/>
                            @if(!($adjustment ?? false))
                            <x-select-dropdown  name="stock_status"  :label="__('translation.status')"  :options="config('constants.accountstatus')"  :selected="1"  required class="form-control status required-select" mainrows="4"/>
                            @endif
                            <x-textarea-input name="remarks" label="Remarks" value="" class="" placeholder="Please Enter Remarks" :required="!empty($adjustment ?? false)" :mainrows="!empty($adjustment) ? 12 : 8"  rows="1"/>
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
