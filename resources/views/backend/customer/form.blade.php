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
                         <div class="row">
								<div class="label vital-signs">{{__('translation.customer_information')}}</div>
							</div>
                        <form name="addcustomerform" id="addcustomerform"  method="POST" action="{{ route(array_key_exists($route,$breadcrumb)?$breadcrumb[$route]:'') }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="false" onSubmit="return validatedata();">
                            @csrf
                            <input type="hidden" value="{{ (!empty($customerDetails)) ? \App\Helpers\Settings::getEncodeCode($customerDetails->id) : '' }}" name="customer_id" id="customer_id"/>
                            <div class="row"> 
                                <x-select-dropdown name="customer_suffix" label="Title" :options="\Config::get( 'constants.suffix' )" :selected="$customerDetails->customer_suffix ?? 'Mr'" class="suffix" required/>   
                                <x-text-input name="first_name" label="First Name <small><i>Only Alphabets.</i></small>" value="{{ $customerDetails->first_name ?? '' }}" required class="staffname alphanumeric form-control" placeholder="Please Enter Staff First Name" maxlength="25"/>  
                                <x-text-input name="last_name" label="Last Name <small><i>Only Alphabets.</i></small>" value="{{ $customerDetails->last_name ?? '' }}"  required class="staffname alphanumeric form-control" placeholder="Please Enter Staff Last Name" maxlength="25"/>
                                <x-text-input name="business_name" label="Business Name<small><i>  if you have...</i></small>" value="{{ $customerDetails->business_name ?? '' }}" class="staffname alphanumeric form-control" placeholder="Please Enter Business Name" maxlength="25"/>  
                                <x-select-dropdown name="gender" label="Gender" :options="\Config::get( 'constants.genders' )" :selected="$customerDetails->gender ?? '1'" class="suffix" required/>
                                <x-email-input name="email" label="Email Id" value="{{ $customerDetails->email ?? '' }}"   placeholder="Please Enter Email Id" />
                                <x-text-input name="phone_no" :label="__('translation.mobile_number')" value="{{ $customerDetails->phone_no ?? '' }}"  required id="phone_no" class="onlyinteger phonenumber nocutcopypaste setusername" placeholder="Please Enter Mobile Number"/>
                                <x-text-input name="alternate_phone_no" :label="__('translation.alternate_phone_no')" value="{{ $customerDetails->alternate_phone_no ?? '' }}"  id="alternate_phone_no" class="onlyinteger phonenumber nocutcopypaste setusername" placeholder="Please Enter Alternate Mobile Number"/>
                                 <x-text-input name="whatsapp_number" label="WhatsApp Number" value="{{ $customerDetails->whatsapp_number ?? '' }}"  id="whatsapp_number" class="onlyinteger phonenumber nocutcopypaste"/>
                                <x-image-upload name="avatar" id="avatar"  label="Upload Profile Picture"  :value="@$customerDetails->avatar" />
                                <input type="hidden" class="is_image_exists" name="is_image_exists" id="is_image_exists" value="{{ (!empty($customerDetails)) ? \App\Helpers\Settings::getEncodeCode($customerDetails->avatar) : '0' }}">
                                <x-textarea-input name="current_location" label="Location" value="{{ $customerDetails->current_location ?? '' }}" class="" placeholder="Please Enter Current Location" mainrows="4" rows="1"/>
                                <x-textarea-input name="near_by" :label="__('translation.near_by')" value="{{ $customerDetails->near_by ?? '' }}" class="" placeholder="Please Enter Near By" mainrows="4" rows="1"/>
                                <x-textarea-input name="notes" label="Notes" value="{{ $customerDetails->notes ?? '' }}" class="" placeholder="Please Enter Notes" mainrows="12" rows="2"/>
                            </div>
                             <div class="form-group">
                                <x-form-buttons submitText="{{$submitText??'Update'}}" resetText="Cancel" url="{{ route(array_key_exists('add_new_route',$breadcrumb)?$breadcrumb['add_new_route']:'javascript::void(0)') }}" class="btn-success" />
                            </div>
                        </form>
                </div>
            </div>
        </div>
    </div>
@endsection
