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
                    <h4 class="card-title">{{ request()->route()->getName() == 'admin.products.create' ? $breadcrumb['route2Title']:($breadcrumb['route3Title'])}}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ isset($product) ? route('admin.products.update') : route('admin.products.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ isset($product) ? \App\Helpers\Settings::getEncodeCode($product->id) : '' }}">
                    <div class="row">
                        <x-select-dropdown name="category_id" label="Category" :options="$categories" :selected="$product->category_id ?? ''" class="category" required/>
                        <x-text-input name="name" label="Product Name" value="{{ $product->name ?? '' }}" required />
                        <x-text-input name="price" :label="__('translation.price') . ' ' . __('translation.b_ngn')"   value="{{ $product->price ?? '' }}" required  class='onlydecimal' />
                        <x-text-input name="cost_price" :label="__('translation.price') . ' ' . __('translation.b_ngn')"  value="{{ $product->cost_price ?? '' }}" class='onlydecimal' required/>
                        <!-- <x-text-input name="barcode" label="Barcode" value="{{ $product->barcode ?? '' }}" class="barcode" required/>
                        <x-text-input name="sku" label="SKU" value="{{ $product->sku ?? '' }}" class="sku" required /> -->
                        <x-textarea-input name="description" label="Description" value="{{ $product->description ?? '' }}" rows='1'/>
                        <x-select-dropdown name="status" label="Status" :options="config('constants.accountstatus')" :selected="$product->status ?? 1" required  class="status"/>
                    </div>
                    <x-form-buttons submitText="{{ isset($product) ? 'Update' : 'Save' }}" url="{{ route('admin.products') }}" />   
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
