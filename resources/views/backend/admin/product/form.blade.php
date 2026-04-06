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
                        {{ request()->route()->getName() == 'admin.products.create' ? $breadcrumb['route2Title'] : ($breadcrumb['route3Title'])}}
                        : {{ $route ?? 'Edit' }}
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ isset($product) ? route('admin.products.update') : route('admin.products.store') }}" class="needs-validation" novalidate autocomplete="off">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ isset($product) ? \App\Helpers\Settings::getEncodeCode($product->id) : '' }}">
                        <input type="hidden" name="route" value="{{ $route ?? '' }}">
                        <input type="hidden" name="adjustment" value="{{ $adjustment ?? '' }}">
                        <div class="row">
                            <x-select-dropdown name="category_id" label="{{ __('translation.category')}}" :options="$categories" :selected="$product->category_id ?? ''" class="category" required />
                            <x-text-input name="name" label="{{ __('translation.product_name')}}" value="{{ $product->name ?? '' }}" required />
                            <!-- <x-text-input name="cost_price" :label="__('translation.cost_price') . ' ' . __('translation.b_ngn')" value="{{ $product->cost_price ?? '' }}" class='onlydecimal' required /> -->
                            <x-text-input name="selling_price" :label="__('translation.selling_price') . ' ' . __('translation.b_ngn')" value="{{ $product->selling_price ?? '' }}" required class='onlydecimal' />
                            <x-textarea-input name="description" label="{{ __('translation.short_description')}}" value="{{ $product->description ?? '' }}" rows='1' />
                            <x-select-dropdown name="status" label="{{ __('translation.status')}}" :options="config('constants.accountstatus')" :selected="$product->status ?? 1" required class="status" />
                        </div>
                        @if($route == 'Add')

                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="card-title">{{ __('translation.inventory_details') }}</h4>
                                    <hr>
                                </div>
                                <x-text-input name="barcode" label="Barcode" value="{{ $barcode ?? '' }}" readonly />
                                <x-text-input name="quantity" label="Quantity" value="" required class="onlyinteger" />
                            </div>
                        @endif
                        <div class="row mb-3">
                            <x-form-buttons submitText="{{ isset($product) ? 'Update' : 'Save' }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
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