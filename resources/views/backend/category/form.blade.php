@extends('layouts.backend.app')
@section('title')
    {{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}} | {{array_key_exists('route1Title',$breadcrumb)?$breadcrumb['route1Title']:''}}
@endsection
@section('content')
@include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ request()->route()->getName() == 'categories.create' ? $breadcrumb['route1Title']:($breadcrumb['route3Title'])}}</h4>
                </div>
                <div class="card-body">
                         <form method="POST" id="productcatform" name="productcatform" action="{{ request()->route()->getName() == 'categories.create' ? route('categories.store') : route('categories.update',  \App\Helpers\Settings::getEncodeCode($category->id)) }}" enctype="multipart/form-data" class="needs-validation" novalidate onSubmit="return validatedata();">
                            @csrf
                            @if(isset($category)) @method('PUT') @endif
                            <input type="hidden" value="{{ isset($category) ? \App\Helpers\Settings::getEncodeCode($category->id) : '' }}" name="category_id"  id="category_id" />
                            <input type="hidden" value="{{ isset($category) ? \App\Helpers\Settings::isFileExists('categories',$category->image) : 0 }}" name="is_image_exists"  id="is_image_exists" />
                            <div class="row">
                                <x-text-input name="name" label="Category Name" value="{{ $category->name ?? '' }}" required class=""/>
                                <x-image-upload name="image" label="Upload Image"  :value="@$category->image"  required accept="image/*"/>
                                <x-select-dropdown name="status" label="Status" :options="\Config::get('constants.accountstatus') ?? []" :selected="@$category->status ?? '1'" required class="accountstatus status"/> 
                                <x-textarea-input name="description" label="Short Description" value="{{ $category->description ?? '' }}"  class="" mainrows="12"/>
                            </div>
                             <div class="row">
                                <x-form-buttons submitText="{{request()->route()->getName() == 'categories.create' ? 'Add' : 'Update'}}" resetText="Cancel" url="{{route($breadcrumb['route1'])}}" class="btn-success" />
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
     <script src="{{ asset('assets/backend/js/menucategory.js?id='.Config::get('app.css_refresh')) }}"></script>
    @endsection
