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
                    <h4 class="card-title d-inline-block">{{ ($breadcrumb['title'] ?? '') . (isset($subscription) ? ' - Edit : '.\App\Helpers\Settings::getEncodeCode($subscription->id) : '') }}</h4>
                </div>
                <div class="card-body">
                    <div>
                        <form method="POST" action="{{ isset($breadcrumb['route2']) ? route($breadcrumb['route2']) : '#' }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="false" onSubmit="return validate();">
                            <input type="hidden" value="{{ isset($subscription) ? $subscription->id : '' }}" name="subscription_id" id="subscription_id" />
                            @csrf
                            <div class="row">
                                <x-text-input name="name" label="Name" value="{{ $subscription->name ?? '' }}" required class="name" mainrows="4"/>
                                <x-text-input name="price" label="Price {{ __('translation.b_ngn') }}" value="{{ $subscription->price ?? '' }}" required class="onlyinteger"/>
                                <x-number-input name="duration" label="{{ __('translation.duration') }}" value="{{ $subscription->duration ?? '' }}" required class="onlyinteger" min="1" max="36"  />
                                <x-textarea-input name="description" label="{{ __('translation.description') }}" value="{{ $subscription->description ?? '' }}" mainrows="12" />
                            </div>
                            <div class="row">
                                <x-form-buttons submitText="{{ isset($subscription) ? 'Update' : 'Save' }}" resetText="Cancel" url="{{route('administrator.subscription')}}" class="btn-success" />
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
    <script type="text/javascript">
        $(document).ready(function() {
        });
    </script>
@endsection
