@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}} |
    {{array_key_exists('route1Title', $breadcrumb) ? $breadcrumb['route1Title'] : ''}}
@endsection
@section('content')
    @include('backend.components.breadcrumb')

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                {{ request()->route()->getName() == 'admin.master_items.create' ? $breadcrumb['route1Title'] : ($breadcrumb['route3Title'])}}
            </h4>
        </div>

        <div class="card-body">
            <form id="master_item" name="master_item" method="POST" action="{{ request()->route()->getName() == 'admin.master_items.create' ? route('admin.master_items.store') : route('admin.master_items.update') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="id" value="{{ isset($item) ? \App\Helpers\Settings::getEncodeCode($item->id) : '' }}">
                <div class="row">
                    <x-select-dropdown name="categoryId" label="Category" :options="$categories" :selected="$item->category_id ?? ''" required class="category_id" id="categoryId" />
                    <x-text-input name="name" label="Name" value="{{ $item->name ?? '' }}" required />
                    <x-text-input name="description" label="Description" value="{{ $item->description ?? '' }}" mainrows="4" />
                    <x-file-input name="image" :preview="false" label="Product Image" :value="$item->image ?? null" accept="image/png,image/jpeg,image/webp" :mainrows="4" />
                    <x-select-dropdown name="status" label="Status" :options="\Config::get('constants.accountstatus')" :selected="isset($item) ? $item->status : 1" class="accountstatus" required />
                </div>
                <div class="row">
                    <x-form-buttons submitText="{{ isset($item) ? 'Update' : 'Save' }}" resetText="Cancel" url="{{ route($breadcrumb['reset_route']) }}" />
                </div>
            </form>

        </div>
    </div>
@endsection
@section('script')
    <script>
        validateSelect2Form('master_item', [
            'categoryId'
        ]);
    </script>
@endsection