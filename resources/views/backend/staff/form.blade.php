@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}}
@endsection
@section('css')
    <link href="{{ URL::asset('/assets/libs/admin-resources/admin-resources.min.css') }}" rel="stylesheet">   
@endsection
@section('content')
@include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><h4 class="card-title">{{array_key_exists('routeTitle',$breadcrumb)?$breadcrumb['routeTitle']:''}}</h4></div>
                <div class="card-body">
                    <div>
                        <form name="addstaffform" id="addstaffform"  method="POST" action="{{ route(array_key_exists($route,$breadcrumb)?$breadcrumb[$route]:'') }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="false" onSubmit="return validatedata();">
                            @csrf
                            <input type="hidden" value="{{ (!empty($userDetails)) ? \App\Helpers\Settings::getEncodeCode($userDetails->id) : '' }}" name="user_id" id="user_id"/>
                            <div class="row">
								<div class="label vital-signs">Staff Information</div>
							</div>
                            <div class="row">   
                                <x-select-dropdown name="staff_suffix" label="Title" :options="$suffix" :selected="$userDetails->userdetail->staff_suffix ?? 'Mr'" class="suffix" required/> 
                                <x-text-input name="first_name" label="First Name <small><i>Only Alphabets.</i></small>" value="{{ $userDetails->first_name ?? '' }}" required class="staffname alphanumeric form-control" placeholder="Please Enter Staff First Name" maxlength="25"/>  
                                <x-text-input name="last_name" label="Last Name <small><i>Only Alphabets.</i></small>" value="{{ $userDetails->last_name ?? '' }}" required class="staffname alphanumeric form-control" placeholder="Please Enter Staff Last Name" maxlength="25"/>
                                <x-text-input name="street_address" label="Address" value="{{ $userDetails->userdetail->street_address ?? '' }}" required class="" placeholder="Please Enter Street Address" />
                                <x-select-dropdown name="local_government" id="local_government" label="Local Government" :options="$localGovernment" :selected="$userDetails->userdetail->local_government ?? ''" required class="local_government"/> 
                                <x-select-dropdown name="country_of_origin" label="Country Of Origin" :options="$countries" :selected="$userDetails->userdetail->country_of_origin ?? ''" class="country" required/>
                                <x-select-dropdown name="state_of_origin" label="State Of Origin" :options="$state" :selected="$userDetails->userdetail->state_of_origin ?? ''" class="state" required/>
                                <x-date-input name="date_of_birth"  label="Date Of Birth" value="{{ $userDetails->userdetail->date_of_birth ?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d',strtotime('-20 years'))) }}" required class="flatdatepickr date_of_birth" data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-60 years')) )}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-10 year')))}}"/>   
                                <x-text-input name="nin" label="National Identification Number" value="{{ $userDetails->userdetail->nin ?? '' }}"  required  class="onlyinteger phonenumber nocutcopypaste"/>
                                <x-email-input name="email" label="Email Id" value="{{ $userDetails->email ?? '' }}" required  placeholder="Please Enter Email Id" />
                                <x-text-input name="mobile_no" label="Mobile Number" value="{{ $userDetails->mobile_no ?? '' }}"  required id="cell_phone" class="onlyinteger phonenumber nocutcopypaste setusername" placeholder="Please Enter Mobile Number"/>
                                 <x-text-input name="whatsapp_number" label="WhatsApp Number" value="{{ $userDetails->userdetail->whatsapp_number ?? '' }}"  id="whatsapp_number" class="onlyinteger phonenumber nocutcopypaste"/>
                                <x-image-upload name="avatar" id="avatar" required label="Upload Profile Picture"  :value="@$userDetails->avatar" />
                                <input type="hidden" class="is_image_exists" name="is_image_exists" id="is_image_exists" value="{{ (!empty($userDetails)) ? \App\Helpers\Settings::getEncodeCode($userDetails->avatar) : '0' }}">
                            </div>
                            <div class="row">
								<div class="label vital-signs">Emergency Contact Details</div>
							</div>
                            <div class="row"> 
                                <x-select-dropdown name="emergency_suffix" label="Title" :options="$suffix" :selected="$userDetails->userdetail->emergency_suffix ?? 'Mr'" class="suffix" required/> 
                                <x-text-input name="emergency_contact_name" label="Emergency Contact Name <small><i>Only Alphabets.</i></small>" value="{{ $userDetails->userdetail->emergency_contact_name ?? '' }}" required class="staffname alphanumeric form-control" placeholder="Please Enter Emergency Contact Name" maxlength="50"/>  
                                 <x-text-input name="emergency_phone" label="Emergency Phone Number" value="{{ $userDetails->userdetail->emergency_phone ?? '' }}"  required id="cell_phone" class="onlyinteger phonenumber nocutcopypaste setusername" placeholder="Please Enter Emergency Phone Number" maxlength="11"/>
                                <x-select-dropdown name="emergency_relationship" label="Relationship" :options="$emergecyRelationship" :selected="$userDetails->userdetail->emergency_relationship ?? ''" class="emergency_relationship" required/>
                            </div>
                            <!------ -Guarantor   Begin----->
                            <div class="row">
								<div class="label vital-signs">Guarantor Information</div>
							</div>
                            <div class="row"> 
                                <x-select-dropdown name="guarantor_suffix" label="Title" :options="$suffix" :selected="$userDetails->userdetail->guarantor_suffix ?? 'Mr'" class="suffix" required/> 
                                <x-text-input name="guarantor_name" label="Guarantor Name <small><i>Only Alphabets.</i></small>" value="{{ $userDetails->userdetail->guarantor_name ?? '' }}" required class="staffname alphanumeric form-control" placeholder="Please Enter Guarantor Name" maxlength="50"/>
                                <x-text-input name="guarantor_address" label="Address" value="{{ $userDetails->userdetail->guarantor_address ?? '' }}" required class="" placeholder="Please Enter Address" />
                                <x-text-input name="guarantor_phone" label="Phone Number" value="{{ $userDetails->userdetail->guarantor_phone ?? '' }}"  required id="guarantor_phone" class="onlyinteger phonenumber nocutcopypaste "/>                               
                            </div>
                            <div class="row">
								<div class="label vital-signs">Designation and Hired Information</div>
							</div>
                                <div class="row">
                                    <x-select-dropdown name="designation_id" label="Designation" :options="$designation ?? []" :selected="$userDetails->designation_id ?? ''" class="designation_id" required/>
                                    <x-date-input name="hire_date"  label="Hired Date" value="{{ $userDetails->hire_date ?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" required class="flatdatepickrto hire_date" data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-60 years')) )}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('+1 year')))}}"/>
                                    <x-select-dropdown name="staffstatus" label="Status" :options="$staffstatus" :selected="$userDetails->is_active ?? ''" class="staffstatus" required/>
                                @if($route == 'addroute')
                                    <x-text-input name="username" label="Username" value="{{ $userDetails->username ?? '' }}" required class="username form-control" placeholder="Please Enter Username" maxlength="50"/>
                                    <x-text-input name="password" label="New Password" id="password" value="{{ $userDetails->password ?? '' }}" required class="password form-control" placeholder="Please Enter password" maxlength="12"/>
                                    <x-text-input name="password_confirmation" label="Confirm New Password" value="{{ $userDetails->password_confirmation ?? '' }}" id="password_confirmation" required class="password_confirmation form-control" placeholder="Please Enter Confirmed Password" maxlength="12"/>                               
                                @endif
                            </div>
                            <div class="row">
								<div class="label vital-signs">Social Media</div>
							</div>
                            <div class="row">
                                <x-text-input name="facebook" label="Facebook" value="{{ $userDetails->userdetail->facebook ?? '' }}" class="" placeholder="Please Enter Facebook link"/>
                                <x-text-input name="twitter" label="Twitter" value="{{ $userDetails->userdetail->twitter ?? '' }}" class="" placeholder="Please Enter Twitter link"/>
                                <x-text-input name="linkedin" label="LinkedIn" value="{{ $userDetails->userdetail->linkedin ?? '' }}" class="" placeholder="Please Enter LinkedIn link"/>
                                <x-text-input name="instagram" label="Instagram" value="{{ $userDetails->userdetail->instagram ?? '' }}" class="" placeholder="Please Enter Instagram link"/>
                                <x-text-input name="pinterest" label="Pinterest" value="{{ $userDetails->userdetail->pinterest ?? '' }}" class="" placeholder="Please Enter Pinterest link"/>								
							</div>
                            <div class="row">
								<div class="label vital-signs">Notes</div>
							</div>
                            <div class="row">
                                <x-textarea-input name="note" label="Notes" value="{{ $userDetails->userdetail->note ?? '' }}" class="" placeholder="Please Enter Notes" mainrows="12"/>
							</div>
                            <div class="form-group">
                                <x-form-buttons submitText="{{$submitText??'Update'}}" resetText="Cancel" url="{{ route(array_key_exists('add_new_route',$breadcrumb)?$breadcrumb['add_new_route']:'javascript::void(0)') }}" class="btn-success" />
                            </div>
                        </form>
                    </div>
                    <!-- end card -->
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script src="{{ URL::asset('assets/js/staff.js') }}"></script>
@endsection
