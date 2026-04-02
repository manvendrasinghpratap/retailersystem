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
                <form action="{{ isset($sales) ? route($breadcrumb['updateroute'], $id) : route($breadcrumb['addroute']) }}" method="POST" class="needs-validation" novalidate id="stockloadingForm">
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
                        <x-date-input name="date" :label="__('translation.date')"
                                value="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}"
                                id='date'
                                value="{{ request()->get('date')?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}"
                                required class="flatdatepickr ordered_at fourtyper--"
                                data-mindate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('0 days'))) }}"
                                data-maxdate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('0 days'))) }}" 
                               :disabled="!empty($order) && $order->date"
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
                                               <th>#</th>
                                               <th>{{ __('translation.product') }}</th>
                                               <th>{{ __('translation.taken') }}</th>
                                               <th>{{ __('translation.incentive') }}</th>
                                               <th>{{ __('translation.remarks') }}</th>
                                               <th width="5%">{{ __('translation.action') }}</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                        @if(count($records)>0)
                                               @foreach($records as $index => $item)
                                                    <tr class="product-row">
                                                     <td class="row-index">{{ $index + 1 }}</td>
                                                        <td>
                                                            <select name="records[{{ $index }}][menu_id]" class="form-control product-select customer-select" required>
                                                                <option value="">{{ __('translation.select_product') }}</option>
                                                                @foreach($products as $product)
                                                                    <option value="{{ $product->id }}" {{ $item->menu_id == $product->id ? 'selected' : '' }}>
                                                                        {{ $product->title }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>

                                                        <td>
                                                            <input type="number" name="records[{{ $index }}][quantity_taken]" value="{{ $item->quantity_taken ?? '' }}" min="1"   class="form-control onlyinteger default-zero" required />
                                                        </td>

                                                        <td>
                                                            <input type="number" name="records[{{ $index }}][incentive_taken]" 
                                                                   value="{{ $item->incentive_taken ?? 0 }}" 
                                                                   min="0" 
                                                                   class="form-control onlyinteger default-zero" />
                                                        </td>

                                                        <td>
                                                            <input type="text" name="records[{{ $index }}][remarks]" 
                                                                   value="{{ $item->remarks ?? '' }}" 
                                                                   class="form-control" />
                                                        </td>

                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-danger btn-sm removeRow">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                         @else
                                         @php( $index = 0) 
                                                 <tr class="product-row">
                                                  <td class="row-index">{{ $index + 1 }}</td>
                                                     <td>
                                                         <select name="records[0][menu_id]" class="form-control product-select" required>
                                                             <option value="">{{ __('translation.select_product') }}</option>
                                                             @foreach($products as $product)
                                                                 <option value="{{ $product->id }}">{{ $product->title }}</option>
                                                             @endforeach
                                                         </select>
                                                     </td>
                                                      <td>
                                                            <input type="number" name="records[0][quantity_taken]" value="{{ $item->quantity_taken ?? '' }}" min="1"   class="form-control onlyinteger default-zero" required />
                                                        </td>

                                                        <td>
                                                            <input type="number" name="records[0][incentive_taken]" 
                                                                   value="{{ $item->incentive_taken ?? 0 }}" 
                                                                   min="0" 
                                                                   class="form-control onlyinteger default-zero" />
                                                        </td>

                                                        <td>
                                                            <input type="text" name="records[0][remarks]" 
                                                                   value="{{ $item->remarks ?? '' }}" 
                                                                   class="form-control" />
                                                        </td>

                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-danger btn-sm removeRow">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                  </tr>
                                         @endif
                                       </tbody>
                                   </table>
                               </div>

                               {{-- Add More Button --}}
                               <div class="text-end mt-2">
                                   <button type="button" id="addRow" class="btn btn-secondary">
                                       <i class="fas fa-plus-circle"></i> {{ __('translation.add_more_product') }}
                                   </button>
                               </div>
                           </div>
                     </div>
                    
                    {{-- Submit Buttons --}}
                    <div class="row mt-4">
                        <div class="col-12">
                         <x-form-buttons submitText="{{ $submitText ?? 'Update' }}"
                                    resetText="{{ __('translation.cancel') }}"
                                    url="{{ route(array_key_exists('add_new_route', $breadcrumb) ? $breadcrumb['add_new_route'] : 'javascript:void(0)') }}"
                                    class="btn-success"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
{{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
<style>
.select2-container--default .select2-selection--single {
    border: 1px solid #ced4da;
    height: 38px;
    border-radius: 4px;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
}
.select2-container .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
.is-invalid .select2-selection {
    border-color: #dc3545 !important;
}
</style>
@endsection
@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('stockloadingForm');
    const addRowBtn = document.getElementById('addRow');
    const tableBody = document.querySelector('#productTable tbody');
    let rowIndex = document.querySelectorAll('#productTable tbody tr').length; // start count from existing rows

    // ====== INIT SELECT2 ======
    function initSelect2() {
        $('.product-select').select2({
            placeholder: "{{ __('translation.select_product') }}",
            width: '100%'
        }).on('change', function () {
            updateProductOptions(); // prevent duplicates
        });
    }
    initSelect2();

    // ====== PRODUCT OPTIONS ======
    const productOptions = `{!! collect($products)->map(function($p){
        return "<option value='{$p->id}'>".addslashes($p->title)."</option>";
    })->implode('') !!}`;

    // ====== VALIDATE ROW ======
    function validateRow(row) {
        let valid = true;
        const productSelect = row.querySelector('.product-select');
        const takenInput = row.querySelector('[name*="[quantity_taken]"]');

        // clear errors
        row.querySelectorAll('.text-danger').forEach(e => e.remove());
        $(productSelect).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        takenInput.classList.remove('is-invalid');

        if (!productSelect.value) {
            const span = document.createElement('small');
            span.classList.add('text-danger');
            span.innerText = "Please select a product";
            productSelect.parentElement.appendChild(span);
            $(productSelect).next('.select2-container').find('.select2-selection').addClass('is-invalid');
            valid = false;
        }

        if (!takenInput.value || parseInt(takenInput.value) <= 0) {
            const span = document.createElement('small');
            span.classList.add('text-danger');
            span.innerText = "Taken must be greater than 0";
            takenInput.parentElement.appendChild(span);
            takenInput.classList.add('is-invalid');
            valid = false;
        }

        return valid;
    }

    // ====== ATTACH EVENTS TO ROW ======
   // ====== ATTACH LISTENERS TO A ROW ======
function attachRowListeners(row) {
    const productSelect = row.querySelector('.product-select');
    const takenInput = row.querySelector('[name*="[quantity_taken]"]');

    if (productSelect) {
        // When select2 option is selected
        $(productSelect).on('select2:select', function() {
            // Force dropdown to close first
            $(this).select2('close');
            validateRow(row);

            // Delay the refresh slightly so dropdown can close smoothly
            setTimeout(() => {
                updateProductOptions();
            }, 200);
        });

        // Also handle deselect / clear (if you enable "allowClear" later)
        $(productSelect).on('select2:clear', function() {
            setTimeout(() => {
                updateProductOptions();
            }, 100);
        });
    }

    if (takenInput) {
        takenInput.addEventListener('input', function() {
            validateRow(row);
        });
    }
}


    // attach existing rows
    document.querySelectorAll('.product-row').forEach(row => attachRowListeners(row));

    // ====== ADD ROW ======
    addRowBtn.addEventListener('click', function() {
        rowIndex++;

        const newRow = document.createElement('tr');
        newRow.classList.add('product-row');
        newRow.innerHTML = `
            <td class="row-index">${rowIndex}</td>
            <td>
                <select name="records[${rowIndex - 1}][menu_id]" class="form-control product-select" required>
                    <option value="">{{ __('translation.select_product') }}</option>
                    ${productOptions}
                </select>
            </td>
            <td><input type="number" name="records[${rowIndex - 1}][quantity_taken]" min="1" class="form-control onlyinteger default-zero" required /></td>
            <td><input type="number" name="records[${rowIndex - 1}][incentive_taken]" value="0" min="0" class="form-control onlyinteger default-zero" /></td>
            <td><input type="text" name="records[${rowIndex - 1}][remarks]" class="form-control" /></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm removeRow">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;

        tableBody.appendChild(newRow);
        initSelect2();
        attachRowListeners(newRow);
        updateProductOptions(); // refresh availability for all
    });

    // ====== REMOVE ROW ======
    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.removeRow')) {
            const row = e.target.closest('tr');
            if (tableBody.querySelectorAll('.product-row').length > 0) {
                row.remove();

                // reindex
                tableBody.querySelectorAll('.product-row').forEach((r, i) => {
                    r.querySelector('.row-index').textContent = i + 1;
                });
                rowIndex = tableBody.querySelectorAll('.product-row').length;
                updateProductOptions();
            } else {
                alert("At least one product row is required.");
            }
        }
    });

    // ====== PREVENT DUPLICATE PRODUCT SELECTION ======
    function updateProductOptions() {
        const selectedValues = Array.from(document.querySelectorAll('.product-select'))
            .map(sel => sel.value)
            .filter(v => v !== '');

        $('.product-select').each(function() {
            const currentValue = $(this).val();
            $(this).find('option').prop('disabled', false);

            // Disable options already selected in other dropdowns
            selectedValues.forEach(val => {
                if (val !== currentValue) {
                    $(this).find(`option[value="${val}"]`).prop('disabled', true);
                }
            });

            // Refresh select2 display
            $(this).trigger('change.select2');
        });
    }

    // ====== FORM VALIDATION ======
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let valid = true;

        document.querySelectorAll('.product-row').forEach(row => {
            if (!validateRow(row)) valid = false;
        });

        if (!valid) {
            const firstError = document.querySelector('.is-invalid');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        form.submit();
    });

    // ====== STAFF + DATE CHANGE REDIRECT ======


    // Initial cleanup (disable already selected)
    updateProductOptions();

});

$(document).ready(function() {
    // Function to initialize Select2
            function initSelect2(select) {
                    select.select2({
                        placeholder: "Select an option",
                        allowClear: true,
                        width: '100%'
                    });

                    // Remove error highlight on change
                    select.on('change', function() {
                        $(this).next('.select2-container').find('.select2-selection').removeClass('error');
                    });
            }

            // Initialize Select2 for all existing required selects
            $('.required-select').each(function() {
                initSelect2($(this));
            });

    // Form submit validation
            $('#stockloadingForm').on('submit', function(e) {
                    var isValid = true;

                    $('.required-select').each(function() {
                        var value = $(this).val();
                        var container = $(this).next('.select2-container');

                        container.find('.select2-selection').removeClass('error');

                        if (!value || value === '') {
                            isValid = false;
                            container.find('.select2-selection').addClass('error');
                        }
                    });

                    if (!isValid) {
                        e.preventDefault(); // Stop form submission if invalid
                    }
            });

    
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $staffSelect = $('#user_id');
    const $dateInput = $('#date');
    function redirectWithParams() {
        const staffId = $staffSelect.val();
        const dateVal = $dateInput.val();

        if (!staffId) {
            return;
        }

        const baseUrl = window.location.origin + window.location.pathname;
        const params = new URLSearchParams();
        params.set('user_id', staffId);
        if (dateVal) params.set('date', dateVal);

        const redirectUrl = `${baseUrl}?${params.toString()}`;
        window.location.href = redirectUrl;
    }

    // ✅ Initialize Select2 safely
    if ($.fn.select2) {
        $staffSelect.select2({
            placeholder: 'Select Staff',
            allowClear: true,
            width: '100%',
        });

        // These MUST fire
        $staffSelect.on('select2:select', function () {
            setTimeout(redirectWithParams, 200);
        });

        $staffSelect.on('select2:unselect', function () {
            setTimeout(redirectWithParams, 200);
        });

        $staffSelect.on('change', function () {
            setTimeout(redirectWithParams, 200);
        });

    }

    // ✅ Handle date change
    $dateInput.on('change', function () {
        redirectWithParams();
    });

});
</script>

@endsection





