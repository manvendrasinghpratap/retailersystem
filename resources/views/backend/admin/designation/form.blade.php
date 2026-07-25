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
                        {{ request()->route()->getName() == 'admin.designations.create'
        ? $breadcrumb['route2Title']
        : $breadcrumb['route3Title'] }}
                    </h4>
                </div>

                <div class="card-body">

                    <form autocomplete="off" method="POST" id="designationform" name="designationform" action="{{ isset($designation)
        ? route('admin.designations.update', \App\Helpers\Settings::getEncodeCode($designation->id))
        : route('admin.designations.store') }}" class="needs-validation" novalidate>

                        @csrf

                        <input type="hidden" name="designation_id" id="designation_id" value="{{ isset($designation) ? \App\Helpers\Settings::getEncodeCode($designation->id) : '' }}">

                        <div class="row">

                            {{-- Designation Name --}}
                            <x-text-input name="name" label="{{ __('translation.designation') }}" value="{{ $designation->name ?? '' }}" required />

                            {{-- Status --}}
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="isset($designation) ? $designation->status : 1" class="accountstatus" required />

                        </div>

                        <div class="row">
                            <x-form-buttons submitText="{{ isset($designation) ? __('translation.update') : __('translation.save') }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
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

            $('#designationform').validate({
                rules: {
                    name: {
                        required: true,
                        maxlength: 100
                    },
                    status: {
                        required: true
                    }
                }
            });

        });
    </script>

@endsection