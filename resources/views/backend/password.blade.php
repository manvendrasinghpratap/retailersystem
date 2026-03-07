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
            <div class="card-header">
                <h4 class="card-title">{{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}}</h4>
            </div>
            <div class="card-body">
                    <form method="POST" action="{{ route(array_key_exists('route',$breadcrumb)?$breadcrumb['route']:'javascript::void(0)') }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="false" onSubmit="return validate();">
                        @csrf
                        <div class="row">                            
                            <x-text-input name="current_password" label="Current Password" value="" id="current_password" required class="current_password form-control" mainrows="4"/>
                            <x-text-input name="password" label="New Password" value="" id="password" required class="password form-control datavalidationerror" mainrows="4"/>
                            <x-text-input name="password_confirmation" label="Confirm New Password" value="" id="password_confirmation" required class="password_confirmation form-control" mainrows="4"/>
                            <div class="form-group">
                                <x-form-buttons submitText="{{$submitText??'Update'}}" resetText="Cancel" url="{{route(array_key_exists('add_new_route',$breadcrumb)?$breadcrumb['add_new_route']:'')}}" class="btn-success updatepassword" />
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
<script>
    $('.datavalidationerror').html('*');
    function validate() {
        var valid = true;

        $('.datavalidationerror').html('*');
        let newpassword = $('#password').val();
        if (newpassword.toLocaleLowerCase().indexOf("password") != -1) {
            $('.error_password').html('<small class="text-danger">New password should not contain the word "password".</small>');
        valid = false;
        }
        return valid;
    }
</script>
@endsection