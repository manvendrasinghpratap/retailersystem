@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}} | {{array_key_exists('routeTitle',$breadcrumb)?$breadcrumb['routeTitle']:''}}
@endsection
@section('content')
@include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{$route == 'add' ? $breadcrumb['routeTitle']:$breadcrumb['updatesubheading']}}</h4>
                </div>
                <div class="card-body">
                         <form method="POST" id="mencatform" name="mencatform" action="{{ $route == 'add' ? route('menu.category.store') : route('menu.category.update') }}" enctype="multipart/form-data" class="needs-validation" novalidate onSubmit="return validatedata();">
                            @csrf
                            <input type="hidden" value="{{ isset($menucatdetails) ? \App\Helpers\Settings::getEncodeCode($menucatdetails->id) : '' }}" name="menu_cat_id"  id="menu_cat_id" />
                            <input type="hidden" value="{{ isset($menucatdetails) ? \App\Helpers\Settings::isFileExists('menu_type',$menucatdetails->image) : 0 }}" name="is_image_exists"  id="is_image_exists" />
                            <div class="row">
                                <x-text-input name="type" label="Category Name" value="{{ $menucatdetails->type ?? '' }}" required class=""/>
                                <x-image-upload name="image" label="Upload Image"  :value="@$menucatdetails->image"  required accept="image/*"/>
                                <x-select-dropdown name="menucatstatus" label="Status" :options="$status" :selected="@$menucatdetails->status ?? ''" required class="accountstatus menucatstatus"/> 
                                <x-textarea-input name="description" label="Short Description" value="{{ $menucatdetails->description ?? '' }}"  class="" mainrows="12"/>
                            </div>
                             <div class="row">
                                <x-form-buttons submitText="{{$submitText??'Update'}}" resetText="Cancel" url="{{route($breadcrumb['route'])}}" class="btn-success" />
                            </div>
                        </form>
                    <!-- end card -->
                </div>
                <!-- end col -->
            </div>
            </div>
        </div>
    </div>
    <!-- end row -->
    @endsection
    @section('script')
     <script src="{{ URL::asset('assets/js/menucategory.js?id='.Config::get('app.css_refresh')) }}"></script>
    @endsection
