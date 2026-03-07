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
                                required class="flatdatepickr ordered_at fourtyper--"
                                data-mindate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-1 month'))) }}"
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
                                               <th>{{ __('translation.product') }}</th>
                                               <th>{{ __('translation.taken') }}</th>
                                               <th>{{ __('translation.incentive') }}</th>
                                               <th>{{ __('translation.remarks') }}</th>
                                               <th width="5%">{{ __('translation.action') }}</th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <tr class="product-row">
                                               <td>
                                                   <select name="records[0][menu_id]" class="form-control product-select  customer-select" required>
                                                       <option value="">{{ __('translation.select_product') }}</option>
                                                       @foreach($products as $product)
                                                           <option value="{{ $product->id }}">{{ $product->title }}</option>
                                                       @endforeach
                                                   </select>
                                               </td>
                                               <td>
                                                   <input type="number" name="records[0][quantity_taken]" value="" min="1" class="form-control onlyinteger default-zero" required />
                                               </td>
                                               <td>
                                                   <input type="number" name="records[0][incentive]" value="0" min="0" class="form-control onlyinteger default-zero" />
                                               </td>
                                               <td>
                                                   <input type="text" name="records[0][remarks]" class="form-control" />
                                               </td>
                                               <td class="text-center">
                                                   <button type="button" class="btn btn-danger btn-sm removeRow">
                                                       <i class="fas fa-trash-alt"></i>
                                                   </button>
                                               </td>
                                           </tr>
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
                            <x-form-buttons 
                                submitText="{{ __('translation.save') }}" 
                                resetText="{{ __('translation.cancel') }}" 
                                class="btn-primary"
                            />
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
{{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

    let rowIndex = 1;
    const addRowBtn = document.getElementById('addRow');
    const tableBody = document.querySelector('#productTable tbody');
    const form = document.querySelector('form');

    function initSelect2() {
        $('.product-select').select2({
            placeholder: "{{ __('translation.select_product') }}",
            width: '100%'
        });
    }
    initSelect2();

    const productOptions = `{!! collect($products)->map(function($p){
        return "<option value='{$p->id}'>".addslashes($p->title)."</option>";
    })->implode('') !!}`;

    function validateRow(row) {
        let valid = true;
        const productSelect = row.querySelector('.product-select');
        const takenInput = row.querySelector('[name*="[quantity_taken]"]');

        if (row.querySelector('.error-product')) row.querySelector('.error-product').remove();
        if (row.querySelector('.error-taken')) row.querySelector('.error-taken').remove();
        $(productSelect).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        takenInput.classList.remove('is-invalid');

        // Product required
        if (!productSelect.value) {
            const span = document.createElement('small');
            span.classList.add('text-danger', 'error-product');
            span.innerText = "Please select a product";
            productSelect.parentElement.appendChild(span);
            $(productSelect).next('.select2-container').find('.select2-selection').addClass('is-invalid');
            valid = false;
        }

        // Taken > 0
        if (!takenInput.value || parseInt(takenInput.value) <= 0) {
            const span = document.createElement('small');
            span.classList.add('text-danger', 'error-taken');
            span.innerText = "Taken must be greater than 0";
            takenInput.parentElement.appendChild(span);
            takenInput.classList.add('is-invalid');
            valid = false;
        }

        return valid;
    }

    function attachRowListeners(row) {
        const productSelect = row.querySelector('.product-select');
        const takenInput = row.querySelector('[name*="[quantity_taken]"]');

        $(productSelect).on('change.select2', function() {
            validateRow(row);
        });

        takenInput.addEventListener('input', function() {
            validateRow(row);
        });
    }

    attachRowListeners(document.querySelector('.product-row'));

    addRowBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.classList.add('product-row');
        newRow.innerHTML = `
            <td>
                <select name="records[${rowIndex}][menu_id]" class="form-control product-select customer-select" required>
                    <option value="">{{ __('translation.select_product') }}</option>
                    ${productOptions}
                </select>
            </td>
            <td><input type="number" name="records[${rowIndex}][quantity_taken]" value="" min="1" class="form-control onlyinteger default-zero" required /></td>
            <td><input type="number" name="records[${rowIndex}][incentive]" value="0" min="0" class="form-control onlyinteger default-zero" /></td>
            <td><input type="text" name="records[${rowIndex}][remarks]" class="form-control" /></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm removeRow">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(newRow);
        initSelect2();
        attachRowListeners(newRow);
        rowIndex++;
    });

    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.removeRow')) {
            const row = e.target.closest('tr');
            if (document.querySelectorAll('.product-row').length > 1) {
                row.remove();
            } else {
                alert("At least one product row is required.");
            }
        }
    });

    // Prevent Enter key
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });

    // **Important**: fully stop native submission
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // always prevent

        let formValid = true;
        document.querySelectorAll('.product-row').forEach(row => {
            if (!validateRow(row)) formValid = false;
        });

        if (!formValid) {
            const firstError = document.querySelector('.is-invalid');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

        // Submit the form manually if valid
        form.submit();
    });

})
</script>
<script>
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

    document.addEventListener('DOMContentLoaded', function () {
    const staffSelect = document.getElementById('user_id');
    const dateInput = document.getElementById('date');

    if (!staffSelect || !dateInput) return;

    function redirectWithParams() {
        const staffId = staffSelect.value;
        const dateVal = dateInput.value;

        if (staffId) {
            const baseUrl = window.location.origin + window.location.pathname;
            const params = new URLSearchParams();
            params.set('user_id', staffId);
            if (dateVal) params.set('date', dateVal);
            window.location.href = `${baseUrl}?${params.toString()}`;
        }
    }

    // Works for both normal select and select2
    $(staffSelect).on('change', redirectWithParams);
    $(staffSelect).on('change.select2', redirectWithParams);

    // Trigger on date change
    dateInput.addEventListener('change', redirectWithParams);
});


    </script>
@endsection



