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
                <h4 class="card-title">{{ $breadcrumb['title'] ?? '' }}</h4>
            </div>
            <div class="card-body">
                    <form method="POST" action="{{ array_key_exists('route2', $breadcrumb) ? route($breadcrumb['route2']) : url('/') }}" class="needs-validation" novalidate autocomplete="off" onsubmit="return validatePassword();">
                        @csrf
                        <div class="row">                            
                            <x-text-input name="current_password" label="Current Password" value="" id="current_password" required class="current_password form-control" mainrows="4"/>
                            <x-text-input name="password" label="New Password" value="" id="password" required class="password form-control datavalidationerror" mainrows="4"/>
                            <x-text-input name="password_confirmation" label="Confirm New Password" value="" id="password_confirmation" required class="password_confirmation form-control" mainrows="4"/>
                            <div class="form-group">
                                <x-form-buttons submitText="{{$submitText??'Update'}}" resetText="{{$breadcrumb['reset_route_title'] ?? 'Cancel'}}" url="{{ route($breadcrumb['reset_route'] ?? '#') }}" class="btn-success updatepassword" />
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
