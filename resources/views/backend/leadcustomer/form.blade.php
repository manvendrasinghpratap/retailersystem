@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }} |
    {{ array_key_exists('route1Title', $breadcrumb) ? $breadcrumb['route1Title'] : '' }}
@endsection

@section('content')
    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title">
                        {{ request()->route()->getName() == 'admin.categories.create'
        ? $breadcrumb['route2Title']
        : $breadcrumb['route3Title'] }}
                    </h4>
                </div>

                <div class="card-body">

                    <form autocomplete="off" method="POST" id="categoryform" name="categoryform" action="{{ isset($category)
        ? route('admin.categories.update', \App\Helpers\Settings::getEncodeCode($category->id))
        : route('admin.categories.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>

                        @csrf

                        <input type="hidden" name="category_id" value="{{ isset($category) ? \App\Helpers\Settings::getEncodeCode($category->id) : '' }}" />

                        <input type="hidden" name="is_image_exists" value="{{ isset($category) ? \App\Helpers\Settings::isFileExists('categories', $category->image) : 0 }}" />

                        <div class="row">

                            <x-text-input name="name" label="{{ __('translation.category_name') }}" value="{{ $category->name ?? '' }}" required />

                            <x-text-input name="slug" label="{{ __('translation.slug') }}" value="{{ $category->slug ?? '' }}" required />

                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="isset($category) && $category->status == 0 ? 0 : 1" required class="accountstatus" />

                            <x-image-upload name="image" label="{{ __('translation.image') }}" :value="$category->image ?? ''" />

                            <x-textarea-input name="description" label="{{ __('translation.short_description') }}" value="{{ $category->description ?? '' }}" mainrows="8" />

                        </div>

                        <div class="row">
                            <x-form-buttons submitText="{{ isset($category) ? 'Update' : 'Save' }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    {{-- Optional JS here --}}
@endsection