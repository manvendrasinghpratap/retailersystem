@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? '' }} |{{ $breadcrumb['route1Title'] ?? '' }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ request()->route()->getName() == 'admin.stores.create' ? $breadcrumb['route2Title'] : $breadcrumb['route3Title'] }}
                    </h4>
                </div>
                <div class="card-body">
                    <form enctype="multipart/form-data" autocomplete="off" method="POST" id="storeform" name="storeform" action="{{ isset($store) ? route('admin.stores.update', \App\Helpers\Settings::getEncodeCode($store->id)) : route('admin.stores.store') }}" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="store_id" value="{{ isset($store) ? \App\Helpers\Settings::getEncodeCode($store->id) : '' }}">
                        <div class="row">
                            {{-- Store Name --}}
                            <x-text-input name="name" label="Store Name" value="{{ old('name', $store->name ?? '') }}" required />
                            {{-- Store Code --}}
                            <!-- <x-text-input name="code" label="Store Code" value="{{ old('code', $store->code ?? '') }}" /> -->
                            {{-- Email --}}
                            <x-text-input name="email" label="Email" type="email" value="{{ old('email', $store->email ?? '') }}" />
                            {{-- Phone --}}
                            <x-text-input name="phone" label="Phone" value="{{ old('phone', $store->phone ?? '') }}" required class="onlyinteger" />
                            {{-- Alternate Phone --}}
                            <x-text-input name="alternate_phone" label="Alternate Phone" value="{{ old('alternate_phone', $store->alternate_phone ?? '') }}" class="onlyinteger" maxlength="12" minlength="11" />
                            {{-- Manager --}}
                            <x-select-dropdown name="manager_id" label="Store Manager" :options="$managers" :selected="old('manager_id', $store->manager_id ?? '')" required class="managers" />
                            {{-- Local Government --}}
                            <x-select-dropdown name="local_government" id="local_government" label="Local Government" :options="$localGovernments" :selected="old('local_government', $store->local_government ?? 'Aba South')" required class="local_government" />
                            {{-- State --}}
                            <x-select-dropdown name="state" id="state" label="State" :options="$states" :selected="old('state', $store->state ?? 'Abia')" required class="state" />
                            {{-- Country --}}
                            <x-select-dropdown name="country" id="country" label="Country" :options="$countries" :selected="old('country', $store->country ?? 'Nigeria')" required class="country" />
                            {{-- Status --}}
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="isset($store) ? $store->status : 1" required class="accountstatus" />
                            {{-- Address --}}
                            {{-- logo --}}
                            @if(empty($store->logo))
                                <x-file-input name="logo" :preview="false" label="Store Logo" :value="$store->logo ?? null" accept="image/png,image/jpeg,image/webp" :mainrows="4" required />
                            @else
                                <x-file-input name="logo" :preview="false" label="Store Logo" :value="$store->logo ?? null" accept="image/png,image/jpeg,image/webp" :mainrows="4" />
                            @endif

                            <x-textarea-input name="address" label="Address" :value="old('address', $store->address ?? '')" rows="1" cols="50" :mainrows="8" required />
                        </div>
                        <div class="row">
                            <x-form-buttons submitText="{{ isset($store) ? 'Update' : 'Save' }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
    ```

@endsection

@section('script')

    <script>
        validateSelect2Form('storeform', ['manager_id', 'country', 'state', 'local_government']);
    </script>

@endsection