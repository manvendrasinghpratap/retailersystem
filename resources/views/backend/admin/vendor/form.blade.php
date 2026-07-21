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
                    <h4 class="card-title">{{ request()->route()->getName() == 'admin.vendors.create' ? $breadcrumb['route2Title'] : $breadcrumb['route3Title'] }}</h4>
                </div>
                <div class="card-body">
                    <form autocomplete="off" method="POST" id="vendorform" name="vendorform" action="{{ isset($vendor) ? route('admin.vendors.update') : route('admin.vendors.store') }}" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="vendor_id" id="vendor_id" value="{{ isset($vendor) ? \App\Helpers\Settings::getEncodeCode($vendor->id) : '' }}">
                        <div class="row">
                            {{-- Company Name --}}
                            <x-text-input name="company_name" label="{{ __('translation.company_name') }}" value="{{ $vendor->company_name ?? '' }}" />
                            {{-- Vendor Name --}}
                            <x-text-input name="name" label="{{ __('translation.vendor_name_with_master_name') }}" value="{{ $vendor->name ?? '' }}" required />
                            {{-- Web Site --}}
                            <x-text-input name="website" label="{{ __('translation.website') }}" value="{{ $vendor->website ?? '' }}" />
                            {{-- Phone --}}
                            <x-text-input name="phone" label="{{ __('translation.phone') }}" value="{{ $vendor->phone ?? '' }}" required class="onlyinteger" minlength='11' maxlength='12' />
                            {{-- Whatsapp Number --}}
                            <x-text-input name="whatsapp_number" label="{{ __('translation.whatsapp_number') }}" value="{{ $vendor->whatsapp_number ?? '' }}" class="onlyinteger" minlength='11' maxlength='12' />
                            {{-- Email --}}
                            <x-text-input name="email" type="email" label="{{ __('translation.email') }}" value="{{ $vendor->email ?? '' }}" />
                            {{-- Address --}}
                            <x-text-input name="address" label="{{ __('translation.address') }}" value="{{ $vendor->address ?? '' }}" mainrows="8" />
                            {{-- Opening Balance --}}
                            <x-text-input name="opening_balance" type="number" step="0.01" min="0" label="{{ __('translation.opening_balance') }} {{ __('translation.b_ngn') }}" value="{{ $vendor->opening_balance ?? 0 }}" />
                            {{-- Local Government --}}
                            <x-select-dropdown name="lga_id" id="local_government" label="Local Government" :options="$localGovernment" :selected="$vendor->lga_id ?? 'Aba North'" required class="local_government" />
                            {{-- State of Origin --}}
                            <x-select-dropdown name="state_id" label="State Of Origin" :options="$state" :selected="$vendor->state_id ?? 'Lagos'" class="state" required />
                            {{-- Country of Origin --}}
                            <x-select-dropdown name="country_id" label="Country Of Origin" :options="$countries" :selected="$vendor->country_id ?? 'Nigeria'" class="country" required />
                            {{-- Comment --}}
                            <x-textarea-input name="comment" label="{{ __('translation.comment') }}" value="{{ $vendor->comment ?? '' }}" mainrows="8" rows='1' cols='1' />
                            {{-- Status --}}
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="isset($vendor) && $vendor->status == 0 ? 0 : 1" required class="accountstatus" />

                        </div>

                        {{-- Buttons --}}
                        <div class="row">
                            <x-form-buttons submitText="{{ isset($vendor) ? 'Update' : 'Save' }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection