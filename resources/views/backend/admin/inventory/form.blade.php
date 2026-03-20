@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}}
@endsection
@section('content')
@include('backend.components.breadcrumb')

<div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                <h4 class="card-title">{{ request()->route()->getName() == 'admin.products.create' ? $breadcrumb['route2Title']:($breadcrumb['route3Title'])}} :- {{ $route }}</h4>    
                </div>
                <div class="card-body">
                    <form name="addcustomerform" id="addnewstockform" method="POST" action="{{ isset($product) ? route('admin.stock.adjust') : route('admin.stock.adjust') }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="false">
                    @csrf
                        <div class="row">
                            <input type="hidden" name="type" value="add">
                            <input type="hidden" name="type" value="{{ $adjustment ?? 'IN' }}">
                            <x-select-dropdown  name="product_id"  :label="__('translation.product')"  :options="$products" required class="form-control products required-select" mainrows="4"/>
                            <x-text-input name="quantity" type="text" :label="__('translation.quantity')" value="" class="form-control quantity onlyinteger default-zero" maxlength="5" mainrows="4" required/>
                            <x-date-input name="date"  :label="__('translation.transaction').' '. __('translation.date') " value="{{  \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" required class="flatdatepickr date" required  data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('0 days')) )}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d'))}}" readonly/>
                            @if(!($adjustment ?? false))
                            <x-select-dropdown  name="stock_status"  :label="__('translation.status')"  :options="config('constants.accountstatus')"  :selected="1"  required class="form-control status required-select" mainrows="4"/>
                            @endif
                            <x-textarea-input name="note" label="Note" value="" class="" placeholder="Please Enter Note" :required="!empty($adjustment ?? false)" :mainrows="!empty($adjustment) ? 12 : 8"  rows="1"/>
                        </div>
                        <div class="row mb-3">
                             <x-form-buttons submitText="Update" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
</div>
@endsection
