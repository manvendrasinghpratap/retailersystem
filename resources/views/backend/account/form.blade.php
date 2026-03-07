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
                    <h4 class="card-title">{{$breadcrumb['routeTitle']}}</h4>
                </div>
                <div class="card-body">
                        <form method="POST" action="{{ $route == 'add' ? route('account.store') : route('account.update') }}" enctype="multipart/form-data" class="needs-validation" novalidate onSubmit="return validatedata();">
                            <input type="hidden" value="{{ isset($accountdetails) ? \App\Helpers\Settings::getEncodeCode($accountdetails->id) : '' }}" name="account"  id="account_id" />
                            @csrf
                            <div class="row">
                                <x-select-dropdown name="suffix" label="Title" :options="$suffix" :selected="$accountdetails->user->userdetail->staff_suffix ?? 'Mr'" class="suffix" required/>
                                <x-text-input name="first_name" label="First Name" value="{{ $accountdetails->user->first_name ?? '' }}" required class="setusername"/>
                                <x-text-input name="last_name" label="Last Name" value="{{ $accountdetails->user->last_name ?? '' }}"  required />
                                <x-text-input name="office_phone" label="Office Phone" value="{{ $accountdetails->user->userdetail->office_phone ?? '' }}" required class="onlyinteger phonenumber nocutcopypaste"/>
                                <x-text-input name="cell_phone" label="Cell Phone" value="{{ $accountdetails->user->mobile_no ?? '' }}"  required id="cell_phone" class="onlyinteger phonenumber nocutcopypaste setusername"/>
                                <x-text-input name="whatsapp_number" label="WhatsApp Number" value="{{ $accountdetails->user->userdetail->whatsapp_number ?? '' }}"  required id="whatsapp_number" class="onlyinteger phonenumber nocutcopypaste"/>
                                <x-email-input name="email" label="Email Address" value="{{ $accountdetails->user->email ?? '' }}" required/>
                                <x-text-input name="nin" label="National Identification Number" value="{{ $accountdetails->user->userdetail->nin ?? '' }}"  required  class="onlyinteger phonenumber nocutcopypaste"/>
                                <x-text-input name="street_address" label="Address" value="{{ $accountdetails->user->userdetail->street_address ?? '' }}" />
                                <x-select-dropdown name="local_government" label="Local Government" :options="$localGovernment" :selected="$accountdetails->user->userdetail->local_government ?? ''" required class="local_government"/>
                                <x-select-dropdown name="country" label="Country Of Origin" :options="$countries" :selected="$accountdetails->user->userdetail->country_of_origin ?? ''" class="country" required/>
                                <x-select-dropdown name="state" label="State Of Origin" :options="$state" :selected="$accountdetails->user->userdetail->state_of_origin ?? ''" class="state" required/>
                            </div>
                            @if($route == 'add')
                            <div class="row">
								<div class="label vital-signs">@lang('translation.logindetails')</div>
							</div>
                            <div class="row">
                                <x-text-input name="username" label="Username" value="{{ $account->username ?? '' }}" required class="username"/>
                                <x-text-input name="password" label="New Password" value="{{ $account->password ?? '' }}"  required class="password" id="password"/>
                                <x-text-input name="password_confirmation" label="Confirm New Password" value="{{ $account->password_confirmation ?? '' }}" id='password_confirmation' required class="password_confirmation" />
                            </div>
                            @endif
                            <div class="row">
								<div class="label vital-signs">@lang('translation.accountstatus')</div>
							</div>
                            <x-select-dropdown name="is_active" label="Account Status"  :options="$account_status" :selected="$accountdetails->status ?? '1'" class="account_status" required />
                            <div class="row">
                                <x-form-buttons submitText="{{$submitText}}" resetText="Cancel" url="{{route($breadcrumb['route'])}}" class="btn-success" />
                            </div>
                        </form>
                    <!-- end card -->
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
{{-- /// Select2 code is written in common .js        --}}
    <script src="{{ URL::asset('assets/js/account.js') }}"></script>
@endsection
