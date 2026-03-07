@extends('backend.layouts.master-horizontal')
@section('title')
    @lang('translation.Form_Validation')
@endsection
@section('content')
    @component('frontend.components.breadcrumb')
        @slot('li_1')
            Menu Type
        @endslot
        @slot('title')
            @if(isset($menuTypeData))
                Create Menu Type
            @else
               Add Menu Type
            @endif
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">@if(isset($menuTypeData)) Edit @else Create  @endif  Page</h4>
                    <p class="card-title-desc">CMS pages are typically used for static content that doesn't change
                        frequently, such as an "About Us" page, "Contact" page, or "Terms and Conditions" page. They
                        provide a structured and consistent layout for presenting information to website visitors.</p>
                </div>
                <div class="card-body">
                    <div>
                        <form  method="POST" action="{{ isset($menuTypeData) ? route('menu_type.update', $menuTypeData->id) : route('menu_type.store') }}">
                            @csrf
                            @if(isset($menuTypeData))
                                @method('PUT')
                            @endif
                            <div class="row">
                                <div class="col-xl-12 col-md-12">
                                    <div class="form-group mb-3">
                                        <label>Menu Type</label>
                                        <input name="type" id="type" type="text" required
                                               data-pristine-required-message="Please Enter a Menu Tupe"
                                               class="form-control"
                                               value="{{ isset($menuTypeData) ? $menuTypeData->type : old('type') }}"
                                        />
                                    </div>
                                </div>

                                <div class="col-xl-12 col-md-12">
                                    <div class="form-group mb-3">
                                        <label>Short Description</label>
                                        <textarea name="description"  id="description" required
                                                  data-pristine-required-message="Please Enter a  Description"
                                                  class="form-control" rows="5">{{ isset($menuTypeData) ? $menuTypeData->description : old('description') }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group center">
                                    <input type="submit" value="Submit" name="submit" class="btn btn-primary"/>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- end card -->
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
            @endsection
            @section('script')
                <script src="{{ URL::asset('assets/libs/pristinejs/pristinejs.min.js') }}"></script>
                <script src="{{ URL::asset('assets/js/pages/form-validation.init.js') }}"></script>
                <script src="{{ URL::asset('assets/libs/@ckeditor/@ckeditor.min.js') }}"></script>
                <script src="{{ URL::asset('assets/js/pages/form-editor.init.js') }}"></script>
                {{-- <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script> --}}
@endsection
