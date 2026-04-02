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
                    <form method="POST" id="menuitemform" name="menuitemform" action="{{ $route == 'add' ? route('menu.store') : route('menu.update') }}" enctype="multipart/form-data" class="needs-validation" novalidate onSubmit="return validatedata();">
                        @csrf
                        <input type="hidden" value="{{ isset($menuitemdetail) ? \App\Helpers\Settings::getEncodeCode($menuitemdetail->id) : '' }}" name="menu_item_id"  id="menu_item_id" />
                        <input type="hidden" value="{{ isset($menuitemdetail) ? \App\Helpers\Settings::isFileExists('menu',$menuitemdetail->image) : 0 }}" name="is_image_exists"  id="is_image_exists" />
                        <div class="row">
                            <x-select-dropdown name="menu_type_id" label="{{ __('translation.menu_category') }}" :options="$menuTypeList ?? []" :selected="$menuitemdetail->menu_type  ?? ''" required class="menucategory"/> 
                            <x-text-input name="title" label="Product Name" value="{{ $menuitemdetail->title ?? '' }}" required class=""/>
                            <x-text-input name="price" label="{{ __('translation.regular') }} Price {{ __('translation.ngn') }}" value="{{ $menuitemdetail->price ?? 0 }}" required class="onlyinteger setdefaultzero nocutcopypaste " min="0" max="122"/>
                            {{-- <x-text-input name="vip_price" label="{{ __('translation.vip') }} Price {{ __('translation.ngn') }}" value="{{ $menuitemdetail->vip_price ?? 0 }}" required class="onlyinteger setdefaultzero nocutcopypaste " min="0" max="122"/> --}}
                            <x-image-upload name="image" label="Upload Image"  :value="@$menuitemdetail->image"  required accept="image/*"/>
                            <x-select-dropdown name="accountstatus" label="Status" :options="$status" :selected="$menuitemdetail->status ?? ''" required class="accountstatus"/> 
                            <x-textarea-input name="description" label="Short Description" value="{!! $menuitemdetail->description ?? '' !!}"  class="" mainrows="12"/>
                        </div>                                         
                        <br>
                        <br>
                        <br>
                        <div class="row">
                            <x-form-buttons submitText="{{$submitText??'Update'}}" resetText="Cancel" url="{{route($breadcrumb['route'])}}" class="btn-success" />
                        </div>
                        </form>
                </div>
        <!-- end card -->
        </div>
    <!-- end col -->
    </div>
</div>
<!-- end row -->
@endsection
@section('script')
<script src="{{ URL::asset('assets/js/menuitems.js?id='.Config::get('app.css_refresh')) }}"></script>
@endsection
