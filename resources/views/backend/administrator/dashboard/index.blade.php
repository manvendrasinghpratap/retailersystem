@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}}
@endsection
@section('css')
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Dashboard
                    <h4 class="card-title d-inline-block">
                        {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Total Subscriptions --}}
                        <div class="col-xl-4 col-md-6">
                            <div class="card mini-stats text-white"
                                style="background: linear-gradient(135deg, #667eea, #764ba2); border: none;">
                                <div class="card-body py-4 px-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <p class="fw-medium mb-1">@lang('translation.total_subscriptions')</p>
                                            <h3 class="mb-0">{{ $totalSubscriptions ?? 0 }}</h3>
                                        </div>
                                        <div class="icon-box">
                                            <i class="bx bx-package"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Total Clients --}}
                        <div class="col-xl-4 col-md-6">
                            <div class="card mini-stats text-white"
                                style="background: linear-gradient(135deg, #0a98d0ff, #83e4f8ff); border: none;">
                                <div class="card-body py-4 px-6">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <p class="fw-medium mb-1">@lang('translation.total_clients')</p>
                                            <h3 class="mb-0">{{ $totalClients ?? 0 }}</h3>
                                        </div>
                                        <div class="icon-box">
                                            <i class="bx bx-package"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection