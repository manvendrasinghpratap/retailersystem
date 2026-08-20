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
                        {{ request()->route()->getName() == 'admin.credit-line.create'
        ? $breadcrumb['route2Title']
        : $breadcrumb['route3Title'] }}
                    </h4>
                </div>

                <div class="card-body">

                    <form autocomplete="off" method="POST" id="creditDurationForm" name="creditDurationForm" action="{{ isset($creditDuration)
        ? route('admin.credit-line.update', \App\Helpers\Settings::getEncodeCode($creditDuration->id))
        : route('admin.credit-line.store') }}" class="needs-validation" novalidate>

                        @csrf

                        <input type="hidden" name="credit_duration_id" id="credit_duration_id" value="{{ isset($creditDuration)
        ? \App\Helpers\Settings::getEncodeCode($creditDuration->id)
        : '' }}">

                        <div class="row">

                            {{-- Name --}}
                            <x-text-input name="name" label="{{ __('translation.name') }}" value="{{ $creditDuration->name ?? '' }}" required />

                            {{-- Duration Days --}}
                            <x-text-input name="duration_days" label="{{ __('translation.duration_days') }}" type="number" min="1" value="{{ $creditDuration->duration_days ?? '' }}" required />

                            {{-- Interest --}}
                            <x-text-input name="interest" label="{{ __('translation.interest') }} (%)" type="number" step="0.01" min="0" max="100" value="{{ $creditDuration->interest ?? 0 }}" required />

                            {{-- Status --}}
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="isset($creditDuration) && $creditDuration->status == 0 ? 0 : 1" required class="accountstatus" />

                        </div>

                        <div class="row">

                            <x-form-buttons submitText="{{ isset($creditDuration) ? __('translation.update') : __('translation.save') }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

@endsection

@section('script')

@endsection