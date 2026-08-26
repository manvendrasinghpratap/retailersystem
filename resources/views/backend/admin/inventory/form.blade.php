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
                    {{ request()->route()->getName() == 'admin.products.create' ? $breadcrumb['route2Title'] : $breadcrumb['route3Title'] }} :- {{ $route }}
                </h4>
            </div>
            <div class="card-body">
                <!------ admin.stock.adjust.customerReturn------->
                <form name="addcustomerform" id="addnewstockform" method="POST" action="{{ route('admin.stock.adjust') }}" enctype="multipart/form-data" class="needs-validation" novalidate autocomplete="off">
                    @csrf
                    <div class="row">
                        {{-- Hidden Fields --}}
                        <input type="hidden" name="type" value="{{ $adjustment ?? 'add' }}">
                        <input type="hidden" name="barcode" value="{{ $barcode ?? '' }}">
                        <input type="hidden" name="route" value="{{ $route ?? '' }}">
                        <input type="hidden" name="requisition_item_id" value="{{ $requisition_item_id ?? '' }}">
                        {{-- Product --}}
                        <x-select-dropdown name="product_id" :label="__('translation.product')" :options="$products" :selected="count($products) === 1 ? array_key_first($products) : old('product_id', request('product_id'))" required class="form-control products required-select" mainrows="4" />
                        {{-- Quantity --}}
                        <x-text-input name="quantity" type="text" :label="__('translation.quantity')" :value="$qty ?? '1'" class="form-control quantity onlyinteger default-zero" maxlength="5" mainrows="4" required :readonly="isset($requisition_item_id) && !empty($requisition_item_id)"  />
                        {{-- Reason Dropdown --}}
                        @if(in_array($adjustment ?? '', ['damage', 'deduct', 'expired']))
                        <x-select-dropdown name="reason" :label="__('translation.reason')" :options="[ 
                                    'expired' => 'Expired',
                                    'spoiled' => 'Spoiled',
                                    'broken' => 'Broken',
                                    'damage' => 'Damage',
                                    'leakage' => 'Leakage',
                                    'manual' => 'Manual Deduct'
                                ]" :selected="old('reason')" required class="form-control required-select reason" mainrows="4" />
                        @endif
                        {{-- Transaction Date --}}
                        <x-date-input name="date" :label="__('translation.transaction') . ' ' . __('translation.date')" :value="\App\Helpers\Settings::getFormattedDate(date('Y-m-d'))" required class="flatdatepickr date" data-mindate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" data-maxdate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" readonly />
                        {{-- Note --}}
                        <x-textarea-input name="note" :label="in_array($adjustment ?? '', ['damage', 'deduct']) ? __('translation.reason_note') : __('translation.note')" :value="$masterItemName ?? ''" class="" :placeholder="__('translation.please_enter_note')" :required="!empty($adjustment ?? false)" :mainrows="12" rows="2" />
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

        // Initialize Select2 validation (only if applicable)
        validateSelect2Form('addnewstockform', ['product_id', 'reason']);

        let isSubmitting = false;

        $('#addnewstockform').on('submit', function (e) {

            e.preventDefault();

            if (isSubmitting) {
                return false;
            }

            let form = this;

            // Bootstrap validation
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return false;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to save this stock adjustment?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Save',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {

                if (result.isConfirmed) {

                    isSubmitting = true;

                    let submitBtn = $(form).find('button[type="submit"]');

                    submitBtn.prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

                    form.submit();
                }

            });

        });

    });
</script>
@endsection