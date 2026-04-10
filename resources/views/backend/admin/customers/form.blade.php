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
                        {{ request()->route()->getName() == 'customers.create'
        ? $breadcrumb['route2Title']
        : $breadcrumb['route3Title'] }}
                    </h4>
                </div>

                <div class="card-body">

                    <form autocomplete="off" method="POST" id="customerform" name="customerform" action="{{ isset($customer) ? route('admin.customers.update', $customer->id) : route('admin.customers.store') }}" class="needs-validation" novalidate>
                        @csrf
                        @if(isset($customer))
                            @method('PUT')
                        @endif
                        <div class="row">
                            <input type="hidden" name="customer_id" value="{{ isset($customer) ? \App\Helpers\Settings::getEncodeCode($customer->id) : '' }}">
                            <!-- Name -->
                            <x-text-input name="name" label="{{ __('translation.customer_name') }}" value="{{ old('name', $customer->name ?? '') }}" required />
                            <!-- Phone -->
                            <x-text-input name="phone" label="{{ __('translation.phone') }}" value="{{ old('phone', $customer->phone ?? '') }}" required class="onlyinteger" minlength="10" maxlength="10" />
                            <!-- Wallet Balance -->
                            <x-text-input name="wallet_balance" label="{{ __('translation.wallet_balance') }}" value="{{ old('wallet_balance', $customer->wallet_balance ?? 0) }}" class="onlydecimal" />
                            {{-- Status --}}
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="isset($customer) && $customer->status == 0 ? 0 : 1" required class="accountstatus" />

                        </div>
                        <div class="row">
                            <x-form-buttons submitText="{{ isset($customer) ? 'Update' : 'Save' }}" resetText="{{ $breadcrumb['reset_route_title'] }}" url="{{ route($breadcrumb['reset_route']) }}" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    {{-- Optional JS --}}
@endsection