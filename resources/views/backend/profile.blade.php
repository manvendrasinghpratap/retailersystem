@extends('backend.layouts.master-horizontal')
@section('title')
{{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}}
@endsection
@section('css')
@endsection
@section('content')
@include('backend.components.breadcrumb')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{array_key_exists('routeTitle',$breadcrumb)?$breadcrumb['routeTitle']:''}}</h4>
                </div>    
                <div class="card-body">
                    <div>
                            <form method="POST" action="{{ route(array_key_exists('route',$breadcrumb)?$breadcrumb['route']:'javascript::void(0)') }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="false" onSubmit="return validate();">
                            @csrf
                            <div class="row">
                                <x-select-dropdown name="suffix" label="Title" :options="$suffix" :selected="$user->userdetail->staff_suffix ?? 'Mr'" class="suffix" required/>
                                <x-text-input name="first_name" label="First Name" value="{{ $user->first_name ?? '' }}" required class=""/>
                                <x-text-input name="last_name" label="Last Name" value="{{ $user->last_name ?? '' }}"  required />                               
                                <x-text-input name="office_phone" label="Office Phone" value="{{ $user->userdetail->office_phone ?? '' }}" required class="onlyinteger phonenumber nocutcopypaste"/>
                                <x-text-input name="cell_phone" label="Cell Phone" value="{{ $user->mobile_no ?? '' }}"  required id="cell_phone" class="onlyinteger phonenumber nocutcopypaste setusername"/>
                                <x-text-input name="whatsapp_number" label="WhatsApp Number" value="{{ $user->userdetail->whatsapp_number ?? '' }}"  required id="whatsapp_number" class="onlyinteger phonenumber nocutcopypaste"/>
                                <x-date-input name="date_of_birth"  label="Date Of Birth" value="{{ $user->start_date ?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" required class="flatdatepickr date_of_birth" data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-60 years')) )}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('+1 year')))}}"/> 
                                <x-email-input name="email" label="Email Address" value="{{ Auth::user()->email }}" required readonly />
                                <x-text-input name="nin" label="National Identification Number" value="{{ $user->userdetail->nin ?? '' }}"  required  class="onlyinteger phonenumber nocutcopypaste"/>
                                <x-text-input name="street_address" label="Address" value="{{ $user->userdetail->street_address ?? '' }}" />
                                <x-select-dropdown name="local_government" label="Local Government" :options="$localGovernment" :selected="$user->userdetail->local_government ?? ''" required class="local_government"/>
                                <x-select-dropdown name="country" label="Country Of Origin" :options="$countries" :selected="$user->userdetail->country_of_origin ?? ''" class="country" required/>
                                <x-select-dropdown name="state" label="State Of Origin" :options="$state" :selected="$user->userdetail->state_of_origin ?? ''" class="state" required/>
                                <x-image-upload name="avatar" label="Upload Profile Picture"  :value="@$user->avatar" />
                                <div class="form-group">
                                    <x-form-buttons submitText="{{$submitText??'Update'}}" resetText="Cancel" url="{{ route(array_key_exists('add_new_route',$breadcrumb)?$breadcrumb['add_new_route']:'javascript::void(0)') }}" class="btn-success" />
                                </div>
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
@endsection
