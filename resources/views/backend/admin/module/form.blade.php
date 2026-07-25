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
                        {{ request()->route()->getName() == 'admin.modules.create' ? $breadcrumb['route2Title'] : $breadcrumb['route3Title'] }}
                    </h4>
                </div>
                <div class="card-body">
                    <form autocomplete="off" method="POST" id="moduleform" name="moduleform" action="{{ isset($module) ? route('admin.modules.update', \App\Helpers\Settings::getEncodeCode($module->id)) : route('admin.modules.store') }}" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="module_id" id="module_id" value="{{ isset($module) ? \App\Helpers\Settings::getEncodeCode($module->id) : '' }}" />
                        <div class="row">
                            {{-- Module Name --}}
                            <x-text-input name="name" label="{{ __('translation.module') }}" value="{{ $module->name ?? '' }}" required />
                            {{-- Slug --}}
                            <x-text-input name="slug" label="{{ __('translation.slug') }}" value="{{ $module->slug ?? '' }}" required />
                            {{-- Status --}}
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="isset($module) ? $module->status : 1" class="accountstatus" required />
                        </div>
                        <div class="row">
                            <x-form-buttons submitText="{{ isset($module) ? __('translation.update') : __('translation.save') }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            // Auto generate slug from module name
            $('#name').on('keyup blur', function () {
                if ($('#slug').val() == '') {
                    let slug = $(this).val()
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '_')
                        .replace(/^_+|_+$/g, '');
                    $('#slug').val(slug);
                }
            });
        });
    </script>

@endsection