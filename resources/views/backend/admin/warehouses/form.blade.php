@extends('backend.layouts.master-horizontal')
@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ request()->route()->getName() == 'admin.warehouses.create'
                            ? $breadcrumb['route2Title']
                            : $breadcrumb['route3Title'] }}
                    </h4>
                </div>
                <div class="card-body">
                    <form autocomplete="off" method="POST" id="warehouseform" name="warehouseform" action="{{ isset($warehouse) ? route('admin.warehouses.update', \App\Helpers\Settings::getEncodeCode($warehouse->id)) : route('admin.warehouses.store') }}" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="warehouse_id" value="{{ isset($warehouse) ? \App\Helpers\Settings::getEncodeCode($warehouse->id) : '' }}" />
                    <div class="row">
                        {{-- Warehouse Name --}}
                        <x-text-input name="name" label="{{ __('translation.warehouse_name') }}" value="{{ $warehouse->name ?? '' }}" required/>
                        {{-- Staff --}}
                        <x-select-dropdown name="staff_id" label="{{ __('translation.staff') }} ({{ __('translation.manager') }})" :options="$staffs" :selected="isset($warehouse) && $warehouse->staff_id ? $warehouse->staff_id : ''" required class="staff"/>
                        {{-- Phone --}}
                        <x-text-input name="phone" label="{{ __('translation.phone') }}" value="{{ $warehouse->phone ?? '' }}" required class="onlyinteger"/>
                        {{-- Email --}}
                        <x-text-input name="email" label="{{ __('translation.email') }}" value="{{ $warehouse->email ?? '' }}"/>
                        {{-- Address --}}
                        <x-text-input name="address" label="{{ __('translation.address') }}" value="{{ $warehouse->address ?? '' }}" mainrows="8"/>
                        {{-- Status --}}
                        <x-select-dropdown name="status"
                            label="{{ __('translation.status') }}"
                            :options="config('constants.accountstatus')"
                            :selected="isset($warehouse) && $warehouse->status == 0 ? 0 : 1"
                            required
                            class="accountstatus"
                        />

                    </div>

                    {{-- Buttons --}}
                    <div class="row">
                        <x-form-buttons
                            submitText="{{ isset($warehouse) ? 'Update' : 'Save' }}"
                            resetText="{{ $breadcrumb['reset_route_title'] }}"
                            url="{{ route($breadcrumb['reset_route']) }}"
                        />
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>

    validateSelect2Form('warehouseform', ['staff_id']);

</script>
@endsection