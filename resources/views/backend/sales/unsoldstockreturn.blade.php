@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? __('translation.sales_entry') }}
@endsection

@section('content')
@include('backend.components.breadcrumb')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">{{ $breadcrumb['routeTitle'] ?? __('translation.stock_loading') }}</h4>
            </div>

            <div class="card-body">
                <form action="{{ isset($sales) ? route($breadcrumb['updateroute'], $id) : route($breadcrumb['addroute']) }}" 
                      method="POST" class="needs-validation" novalidate id="stockloadingForm">
                    @csrf

                    {{-- Sales Executive and Date --}}
                    <div class="row mb-3">
                        <x-select-dropdown 
                            name="user_id" 
                            id="user_id"
                            label="{{ __('translation.sales_executive') }}" 
                            :options="$users" 
                            :selected="request()->get('user_id')" 
                            class="staff required-select staff-select"
                            required 
                        />
                        <x-date-input 
                            name="date" 
                            :label="__('translation.date')"
                            value="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}"
                            id='date'
                            value="{{ request()->get('date') ?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}"
                            required 
                            class="flatdatepickr ordered_at fourtyper--"
                            data-mindate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-1 month'))) }}"
                            data-maxdate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('0 days'))) }}" 
                        />
                    </div>

                    {{-- Products Table --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5 class="mb-3">{{ __('translation.Products') }}</h5>
                            <div class="table-responsive overflowx">
                                <table class="table table-bordered table-sm align-middle" id="productTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="2%">#</th>
                                            <th width="15%">{{ __('translation.product') }}</th>
                                            <th width="2%">{{ __('translation.stock') }}</th>
                                            <th width="5%">{{ __('translation.taken') }}</th>
                                            <th width="6%">{{ __('translation.incentive') }}</th>
                                            {{-- <th width="10%">{{ __('translation.remarks') }}</th> --}}
                                            <th width="10%">{{ __('translation.sold') }}</th>
                                            <th width="10%">{{ __('translation.return') }}</th>
                                            <th width="10%">{{ __('translation.remarks') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($records) > 0)
                                            @foreach($records as $index => $item)
                                                <tr class="product-row">
                                                    <td class="row-index">{{ $index + 1 }}</td>
                                                    <td>
                                                        <select name="records[{{ $index }}][menu_id]" 
                                                                class="form-control product-select customer-select" 
                                                                required readonly>
                                                            <option value="">{{ __('translation.select_product') }}</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}" 
                                                                    {{ $item->menu_id == $product->id ? 'selected' : '' }}>
                                                                    {{ $product->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text"  value="{{ $item->stock->quantity }}" data-initial-stock="{{ $item->stock->quantity }}" readonly  class="form-control onlyinteger quantity-stock default-zero">
                                                    </td>
                                                    
                                                    <td>
                                                        <input type="number" name="records[{{ $index }}][quantity_taken]" 
                                                            value="{{ $item->quantity_taken ?? 0 }}" 
                                                            min="1" 
                                                            class="form-control onlyinteger quantity-taken" 
                                                            required readonly />
                                                    </td>

                                                    <td>
                                                        <input type="number" name="records[{{ $index }}][incentive_taken]" 
                                                            value="{{ $item->incentive_taken ?? 0 }}" 
                                                            min="0" 
                                                            class="form-control onlyinteger default-zero" 
                                                            readonly />
                                                    </td>

                                                    {{-- <td>
                                                        <input type="text" name="records[{{ $index }}][remarks]"    value="{{ $item->remarks ?? '' }}"    class="form-control" readonly/>
                                                    </td> --}}

                                                    <td>
                                                        {{-- Sold quantity input --}}
                                                        <input 
                                                            type="number" 
                                                            name="records[{{ $index }}][quantity_sold]" 
                                                            value="{{ $item->quantity_sold ?? 0 }}" 
                                                            min="0" 
                                                            class="form-control onlyinteger quantity-sold default-zero" />

                                                        {{-- Hidden previous sold quantity --}}
                                                        <input 
                                                            type="hidden" 
                                                            name="records[{{ $index }}][previous_sold_quantity]" 
                                                            value="{{ $item->quantity_sold ?? 0 }}" 
                                                            class="previous-sold" />
                                                    </td>

                                                    <td>
                                                        <input type="number" name="records[{{ $index }}][quantity_returned]"  
                                                            value="{{ $item->quantity_returned ?? 0 }}"  
                                                            class="form-control onlyinteger quantity-returned default-zero" 
                                                            readonly />
                                                    </td>

                                                    <td>
                                                        <input type="text" name="records[{{ $index }}][remarks]"  
                                                            value="{{ $item->remarks ?? '' }}"  
                                                            class="form-control" />
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <x-form-buttons 
                                submitText="{{ $submitText ?? 'Update' }}"
                                resetText="{{ __('translation.cancel') }}"
                                url="{{ route(array_key_exists('add_new_route', $breadcrumb) ? $breadcrumb['add_new_route'] : 'javascript:void(0)') }}"
                                class="btn-success" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ✅ Initialize Select2
    $('.customer-select').select2();
    $('.customer-select').on('select2:opening', function (e) { e.preventDefault(); });

    const $staffSelect = $('#user_id');
    const $dateInput = $('#date');

    function redirectWithParams() {
        const staffId = $staffSelect.val();
        const dateVal = $dateInput.val();
        if (!staffId) return;

        const baseUrl = window.location.origin + window.location.pathname;
        const params = new URLSearchParams();
        params.set('user_id', staffId);
        if (dateVal) params.set('date', dateVal);

        window.location.href = `${baseUrl}?${params.toString()}`;
    }

    if ($.fn.select2) {
        $staffSelect.select2({
            placeholder: 'Select Staff',
            allowClear: true,
            width: '100%',
        });

        $staffSelect.on('select2:select select2:unselect change', function () {
            setTimeout(redirectWithParams, 200);
        });
    }

    $dateInput.on('change', function () { redirectWithParams(); });

    // ✅ Auto calculate return quantity
    // ✅ Auto calculate return quantity + update stock
    $('#productTable').on('input', '.quantity-sold', function () {
            const $row = $(this).closest('tr');

            const taken = parseFloat($row.find('.quantity-taken').val()) || 0;
            const sold = parseFloat($(this).val()) || 0;
            const $returnedInput = $row.find('.quantity-returned');
            const $stockInput = $row.find('.quantity-stock');

            const initialStock = parseFloat($stockInput.data('initial-stock')) || 0;

            // ✅ Prevent over-selling
            const validSold = Math.min(sold, taken);
            $(this).val(validSold);

            // ✅ Calculate returned quantity
            const returned = taken - validSold;
            $returnedInput.val(returned);

            // ✅ New stock formula: (initialStock - sold) + returned
            // const updatedStock = (initialStock - validSold) + returned;
            const updatedStock = (initialStock + returned);

            // ✅ Ensure stock never goes below zero
            $stockInput.val(updatedStock >= 0 ? updatedStock : 0);
    });





});
</script>
@endsection
