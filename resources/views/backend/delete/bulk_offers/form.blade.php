@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title',$breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection

@section('css')
@endsection

@section('content')
@include('backend.components.breadcrumb')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ array_key_exists('routeTitle',$breadcrumb) ? $breadcrumb['routeTitle'] : '' }}</h4>
            </div>
            <div class="card-body">
                <form 
                    name="bulkOfferForm" 
                    id="bulkOfferForm"  
                    method="POST" 
                    action="{{ isset($bulkOffer) ? route($breadcrumb['updateroute'], $bulkOffer->id) : route($breadcrumb['addroute']) }}" 
                    enctype="multipart/form-data" 
                    class="needs-validation" 
                    novalidate
                >
                    @csrf
                    @if(isset($bulkOffer))
                        @method('PUT')
                    @endif
                    
                    <div class="row">
                        <input type="hidden" value="{{ (!empty($bulkOffer)) ? \App\Helpers\Settings::getEncodeCode($bulkOffer->id) : '' }}" name="bulk_offer_id" id="bulk_offer_id"/>
                        {{-- Select Menu --}}
                        <x-select-dropdown 
                            name="menus_id" 
                            :label="__('translation.product')" 
                            :options="$menus" 
                            :selected="$bulkOffer->menus_id ?? ''" 
                            required 
                            class="form-control products"
                        />

                        {{-- Buy Quantity --}}
                        <x-text-input 
                            name="buy_quantity" 
                            type="number"
                            :label="__('translation.buy_quantity')" 
                            value="{{ $bulkOffer->buy_quantity ?? '' }}" 
                            required 
                            class="form-control onlyinteger"
                            :placeholder="__('translation.enter_buy_quantity')" 
                        />

                        {{-- Select Free Menu --}}
                        <x-select-dropdown 
                            name="free_menus_id" 
                            :label="__('translation.free_product')" 
                             :options="$menus" 
                            :selected="$bulkOffer->free_menus_id ?? ''" 
                            required 
                            class="form-control products"
                        />

                        {{-- Free Quantity --}}
                        <x-text-input 
                            name="free_quantity" 
                            type="number"
                            :label="__('translation.free_quantity')" 
                            value="{{ $bulkOffer->free_quantity ?? '' }}" 
                            required 
                            class="form-control onlyinteger"
                            :placeholder="__('translation.enter_free_quantity')" 
                        />

                        {{-- Start Date --}}
                        <x-date-input name="start_date"  :label="__('translation.offer_start_date')" value="{{ $bulkOffer->start_date ?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" required class="flatdatepickr start_date" data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-1 month')) )}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('+1 year')))}}"/>

                        {{-- End Date --}}
                         <x-date-input name="end_date"  :label="__('translation.offer_end_date')"  value="{{ $bulkOffer->end_date ?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" required class="flatdatepickrto end_date" data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-1 month')) )}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('+1 year')))}}"/>

                        {{-- Status --}}
                        <x-select-dropdown name="is_active" :label="__('translation.status')"   :options="\Config::get( 'constants.accountstatus' )" :selected="$bulkOffer->is_active ?? '1'" class="staffstatus accountstatus" required/>
                    </div>

                     <div class="row">
                            <x-form-buttons submitText="{{$submitText??'Update'}}" resetText="Cancel" url="{{route($breadcrumb['add_new_route'])}}" class="btn-success" />
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
