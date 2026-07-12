@extends('backend.layouts.master-horizontal')
@section('title')
    {{ $breadcrumb['title'] ?? '' }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    @php
        $adjustmentType = $adjustment ?? 'add';
        $showReason = in_array($adjustmentType, [
            'damage',
            'deduct',
            'expired',
            'return'
        ]);
        $reasonOptions = [
            'expired' => 'Expired',
            'spoiled' => 'Spoiled',
            'broken' => 'Broken',
            'damage' => 'Damaged',
            'leakage' => 'Leakage',
            'customer_return' => 'Customer Return',
            'excess' => 'Excess Stock',
            'manual' => 'Manual Deduct',
        ];
    @endphp
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                {{-- Header --}}
                <div class="card-header">
                    <h4 class="card-title">
                        {{ request()->route()->getName() == 'admin.products.create' ? ($breadcrumb['route2Title'] ?? '') : ($breadcrumb['route3Title'] ?? '')}}:- {{ $route ?? '' }}
                    </h4>
                </div>
                {{-- Body --}}
                <div class="card-body">
                    <form name="returnWarehouseForm" id="returnWarehouseForm" method="POST" action="{{ route('admin.stock.adjust') }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="off">
                        @csrf
                        {{-- Hidden Fields --}}
                        <input type="hidden" name="type" value="{{ $adjustmentType }}">
                        <input type="hidden" name="barcode" value="{{ $barcode ?? '' }}">
                        <input type="hidden" name="route" value="{{ $route ?? '' }}">
                        <input type="hidden" name="to_account_id" value="{{ auth()->user()->account_id }}">
                        <input type="hidden" name="from_account_id" value="{{ auth()->user()->account_id }}">
                        <input type="hidden" name="requisition_item_id" value="{{ $requisition_item_id ?? '' }}">
                        <div class="row">
                            {{-- From Store --}}
                            <x-select-dropdown id="store_id" name="store_id" :label="__('translation.my_store')" :options="$stores" :selected="old('store_id', auth()->user()->store_id)" required class="form-control required-select store" mainrows="4" readonly />
                            {{-- To Warehouse --}}
                            <x-select-dropdown id="warehouse_id" name="warehouse_id" :label="__('translation.to_warehouse')" :options="$warehouses" :selected="old('warehouse_id')" required class="form-control warehouse" mainrows="4" />
                            {{-- Product --}}
                            <x-select-dropdown id="product_id" name="product_id" :label="__('translation.product')" :options="$products" :selected="count($products) === 1 ? array_key_first($products) : old('product_id', request('product_id'))" required class="form-control products required-select" mainrows="4" />
                            {{-- Quantity --}}
                            <x-text-input name="quantity" type="text" :label="__('translation.quantity')" :value="$qty ?? ''" class="form-control quantity onlyinteger default-zero" maxlength="5" mainrows="4" required :readonly="isset($requisition_item_id) && !empty($requisition_item_id)" />
                            {{-- Reason --}}
                            @if($showReason)
                                <x-select-dropdown id='reason_id' name="reason_id" :label="__('translation.reason')" :options="$reasonOptions" :selected="old('reason')" required class="form-control reason" mainrows="4" />
                            @endif
                            {{-- Transaction Date --}}
                            <x-date-input name="date" :label="__('translation.transaction') . ' ' . __('translation.date')" :value="\App\Helpers\Settings::getFormattedDate(date('Y-m-d'))" required class="flatdatepickr date" data-mindate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" data-maxdate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" readonly />
                            {{-- Note --}}
                            <x-textarea-input name="note" :label="$showReason ? __('translation.reason_note') : __('translation.note')" :value="$masterItemName ?? ''" :placeholder="__('translation.please_enter_note')" :required="$showReason" class="" :mainrows="12" rows="2" />
                        </div>
                        {{-- Buttons --}}
                        <div class="row mb-3">
                            <x-form-buttons :submitText="__('translation.update')" :resetText="$breadcrumb['reset_route_title']" :url="route($breadcrumb['reset_route'])" />
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('script')
    <script>
        $(document).ready(function () {

            validateSelect2Form('returnWarehouseForm', ['store_id', 'warehouse_id', 'reason_id']);
            $('#returnWarehouseForm').on('submit', function (e) {
                e.preventDefault();
                let form = this;
                let submitBtn = $(form).find('button[type="submit"]');
                // Prevent multiple clicks
                if (submitBtn.prop('disabled')) {
                    return false;
                }
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to submit this transaction?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Submit',
                    cancelButtonText: 'Cancel',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitBtn.prop('disabled', true);
                        submitBtn.html(
                            '<span class="spinner-border spinner-border-sm me-1"></span> Processing...'
                        );
                        form.submit();
                    }
                });
            });

        });
    </script>
@endsection