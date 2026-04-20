@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}} |
    {{array_key_exists('route1Title', $breadcrumb) ? $breadcrumb['route1Title'] : ''}}
@endsection
@section('content')
    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ request()->route()->getName() == 'categories.create' ? $breadcrumb['route2Title'] : ($breadcrumb['route3Title'])}}
                    </h4>
                </div>
                <div class="card-body">
                    <form autocomplete="off" method="POST" id="categoryform" name="categoryform" action="{{ isset($category) ? route('admin.categories.update', \App\Helpers\Settings::getEncodeCode($category->id)) : route('admin.categories.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" value="{{ isset($category) ? \App\Helpers\Settings::getEncodeCode($category->id) : '' }}" name="category_id" id="category_id" />
                        <input type="hidden" value="{{ isset($category) ? \App\Helpers\Settings::isFileExists('categories', $category->image) : 0 }}" name="is_image_exists" id="is_image_exists" />
                        <div class="row">
                            <x-text-input name="name" label="{{ __('translation.category_name') }}" value="{{ $category->name ?? '' }}" required class="" />
                            <x-text-input name="description" label="{{ __('translation.description') }}" value="{{ $category->description ?? '' }}" class="" mainrows="4" required />
                            <x-text-input name="slug" label="{{ __('translation.slug') }}" value="{{ $category->slug ?? '' }}" required class="" />
                            <x-image-upload name="image" label="{{ __('translation.image') }}" :value="$category->image ?? ''" />
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="isset($category) && $category->status == 0 ? 0 : 1" required class="accountstatus" />
                        </div>
                        <div class="row">
                            <x-form-buttons submitText="{{ isset($category) ? 'Update' : 'Save' }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
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
    <!-- <script src="{{ asset('assets/backend/js/menucategory.js?id='.Config::get('app.css_refresh')) }}"></script> -->
@endsection