@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection

@section('content')
    @include('backend.components.breadcrumb')

    {{-- Filter Section --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                    <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                            'pdfId' => 'downloadsalespdf',
                            'pdfRoute' => route('admin.sales.exportPdf'),
                            'pdfClass' => 'downloadsalespdf',
                            'csvId' => 'downloadsalescsv',
                            'csvRoute' => route('admin.sales.exportCsv'),
                            'csvClass' => 'downloadsalescsv',
                        ])
                    </div>
                </div>
                <div class="card-body">
                    <form name="cartlistingform" id="cartlistingform" method="GET">
                        <div class="row">
                            <x-text-input name="invoice_no" :label="__('translation.invoice_no')" :value="request('invoice_no')" mainrows="2" />
                            <x-text-input name="from_date" :label="__('translation.from_date')" :value="request('from_date')" mainrows="2" class="flatdatepickr" />
                            <x-text-input name="to_date" :label="__('translation.to_date')" :value="request('to_date')" mainrows="2" class="flatdatepickr" />
                            <x-select-dropdown name="payment_type" label="Payment Type" :options="$paymentTypes" :selected="request('payment_type')" class="payment_type" mainrows="2" />
                            <x-select-dropdown name="approval_status" :label="__('translation.approval_status')" :options="\Config::get('constants.approvalStatus')" :selected="request('approval_status')" class="approval_status" mainrows="2" />
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="Filter" value="Filter" class="" />
                                    <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" class="" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Table Section --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ array_key_exists('route2Title', $breadcrumb) ? $breadcrumb['route2Title'] : '' }}
                        {{ request()->has('invoice_no') ? '' : (request('from_date') ? request('from_date') : App\Helpers\Settings::getFormattedDate(date('Y-m-d'))) }}
                        {{ request()->has('invoice_no') ? '' : '-' }}
                        {{ request()->has('invoice_no') ? '' : (request('to_date') ? request('to_date') : App\Helpers\Settings::getFormattedDate(date('Y-m-d'))) }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('translation.customer_name') }}</th>
                                    <th>{{ __('translation.invoice_no') }}</th>
                                    <th>{{ __('translation.cashier') }}</th>
                                    <th>{{ __('translation.payment_type') }}</th>
                                    <th>{{ __('translation.payment_status') }}</th>
                                    <th>{{ __('translation.payment_method') }}</th>
                                    <th>{{ __('translation.amount') }}</th>
                                    <th>{{ __('translation.b_ngn') }} {{ __('translation.tax') }}</th>
                                    <th>{{ __('translation.fullfillment_method') }}</th>
                                    <th>{{ __('translation.delivery_charges') }}</th>
                                    <th>{{ __('translation.b_ngn') }} {{ __('translation.total_amount') }}</th>
                                    <th>{{ __('translation.approval_status') }}</th>
                                    <th>{{ __('translation.transaction_date') }}</th>
                                    <th width="80">{{ __('translation.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    <tr>
                                        <td>
                                            <x-customer-hover :customer="$sale->customer">
                                                {{ $sale->customer->name ?? '-' }}
                                            </x-customer-hover>
                                        </td>
                                        <td>{{ $sale->invoice_no }}</td>
                                        <td>{{ $sale->user->name ?? '-' }}</td>
                                        <td>{{ ucfirst($paymentTypes[$sale->payment_type] ?? '-') }}</td>
                                        <td>
                                            <a href="javascript:void(0)" class="payment-status-btn" data-heading-title="{{ ucfirst($paymentTypes[$sale->payment_type] ?? '-') }} Details" data-sale-id="{{ $sale->id }}">
                                                {{ ucfirst($sale->payment_status) }}
                                            </a>
                                        </td>
                                        <td>{{ ucfirst($sale->payment_methods ?? '-') }}</td>
                                        <td>{{ __('translation.b_ngn') }} {{ $sale->subtotal }}</td>
                                        <td>{{ __('translation.b_ngn') }} {{ $sale->tax }}</td>
                                        <td>
                                            <x-customer-hover :customer="$sale->delivery_type == 'delivery' ? $sale : ''">
                                                {{ \App\Helpers\Settings::getDataTitle($sale->delivery_type) }}
                                            </x-customer-hover>
                                        </td>
                                        <td>{{ __('translation.b_ngn') }} {{ $sale->delivery_charge }}</td>
                                        <td>
                                            <span class="text-success">
                                                {{ __('translation.b_ngn') }}{{ \App\Helpers\Settings::getcustomnumberformat($sale->total) }}
                                            </span>
                                        </td>
                                        <td>{{ ucfirst($sale->payment_approval_status ?? '-') }}</td>
                                        <td>{{ \App\Helpers\Settings::getFormattedDatetime($sale->created_at) }}</td>
                                        <td class="text-center">
                                            <div class="d-inline-flex align-items-center justify-content-center gap-2">
                                                {{-- Approval / Rejection Action --}}
                                                @if(empty($sale->payment_approved_by) && ($sale->payment_approval_status === 'pending'))
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-soft-warning approve-credit-sale-btn px-2 py-1" data-customer-name="{{ $sale->customer->name ?? 'Guest' }}" data-customer-contact="{{ $sale->customer->phone ?? $sale->customer->email ?? '-' }}" data-total-amount="{{ __('translation.b_ngn') }}{{ \App\Helpers\Settings::getcustomnumberformat($sale->total) }}" data-sale-id="{{ \App\Helpers\Settings::getEncodeCodeWithHashids($sale->id) }}" data-invoice-no="{{ $sale->invoice_no }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Approve / Reject Credit Sale">
                                                        <i class="fas fa-file-signature text-warning"></i>
                                                    </a>
                                                @endif

                                                {{-- View Details Action --}}
                                                <a href="{{ route('admin.sales.show', \App\Helpers\Settings::getEncodeCodeWithHashids($sale->id)) }}" class="btn btn-sm btn-soft-primary px-2 py-1" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                    <i class="fas fa-eye text-primary"></i>
                                                </a>

                                                {{-- Approved Actions (Download & Payment Details) --}}
                                                @if(!empty($sale->payment_approved_by) && ($sale->payment_approval_status === 'approve'))
                                                    <a href="{{ route('downloadinvoice', \App\Helpers\Settings::getEncodeCodeWithHashids($sale->id)) }}" class="btn btn-sm btn-soft-success px-2 py-1" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Download Invoice">
                                                        <i class="fas fa-download text-success"></i>
                                                    </a>

                                                    <a href="javascript:void(0)" class="btn btn-sm btn-soft-info payment-status-btn px-2 py-1" data-heading-title="{{ ucfirst($paymentTypes[$sale->payment_type] ?? '-') }} Details" data-sale-id="{{ $sale->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Payment Details">
                                                        <i class="fas fa-credit-card text-info"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center">{{ __('translation.no_sales_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(!empty($sales) && method_exists($sales, 'links'))
                        <div class="right user-navigation right">
                            {!! $sales->appends(request()->input())->links() !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('backend.modal.sale-payment-approval')
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            // Send invoice logic
            $('.send-invoice-btn').on('click', function () {
                let btn = $(this);
                let saleId = btn.data('sale-id');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

                $.ajax({
                    url: '{{ route("sendinvoice") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        sale_id: saleId
                    },
                    success: function (response) {
                        if (response.success) {
                            showAlert('success', 'Success', 'Invoice sent successfully!');
                        } else {
                            showAlert('error', 'Error', response.message);
                        }
                    },
                    error: function (xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'Failed to send invoice. Please try again.';
                        showAlert('error', 'Error', errorMsg);
                    },
                    complete: function () {
                        btn.prop('disabled', false).html('<i class="fas fa-envelope"></i>');
                    }
                });
            });

            // Export Helpers Setup
            setupPdfDownload('.downloadsalespdf', 'data-downloadroutepdf');
            setupPdfDownload('.downloadsalescsv', 'data-downloadroutepdf');

            // Open Approval Modal
            $(document).on('click', '.approve-credit-sale-btn', function () {
                let btn = $(this);

                $('#modal_sale_id').val(btn.data('sale-id'));
                $('#modal_invoice_no').text('#' + btn.data('invoice-no'));
                $('#modal_customer_name').text(btn.data('customer-name'));
                $('#modal_customer_contact').text(btn.data('customer-contact'));
                $('#modal_total_amount').text(btn.data('total-amount'));

                $('#modal_note').val('').removeClass('is-invalid');
                $('#actionApprove').prop('checked', true);
                $('#modalAlert').addClass('d-none').text('');

                $('#approvalModal').modal('show');
            });

            // Submit Approval Form
            $('#approvalForm').on('submit', function (e) {
                e.preventDefault();

                let noteInput = $('#modal_note');
                let noteValue = $.trim(noteInput.val());

                if (noteValue === '') {
                    noteInput.addClass('is-invalid').focus();
                    $('#modalAlert').removeClass('d-none').text('Please enter a note before submitting.');
                    return false;
                } else {
                    noteInput.removeClass('is-invalid');
                }

                let saleId = $('#modal_sale_id').val();
                let submitBtn = $('#submitApprovalBtn');
                let url = "{{ route('admin.sales.approve-credit', ':id') }}".replace(':id', saleId);

                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
                $('#modalAlert').addClass('d-none');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.success) {
                            $('#approvalModal').modal('hide');
                            location.reload();
                        }
                    },
                    error: function (xhr) {
                        submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Submit Decision');
                        let errorMsg = xhr.responseJSON?.message || 'An error occurred during processing.';
                        $('#modalAlert').removeClass('d-none').text(errorMsg);
                    }
                });
            });
        });

        // Open Payment Status Details Modal
        $(document).on('click', '.payment-status-btn', function () {
            let btn = $(this);
            let saleId = btn.data('sale-id');
            let headingTitle = btn.data('heading-title');

            $('#creditPaymentForm').show();
            $('.payment_modal_title').text(headingTitle);
            showLoader();

            let url = '{{ route("admin.sales.payment-details", ":saleId") }}'.replace(':saleId', saleId);

            $.get(url, function (res) {
                if (res.sale.payment_status === 'paid') {
                    $('#creditPaymentForm').hide();
                }

                let options = '<option value="">Select Payment Method</option>';
                $.each(res.payment_methods, function (id, name) {
                    options += `<option value="${id}">${name}</option>`;
                });

                $('#payment_method_id').html(options);
                hideLoader();

                let sale = res.sale;
                $('#sale_id').val(sale.id);

                $('#paymentDetails').html(`
                                                                                                                                                                                                <table class="table table-bordered">
                                                                                                                                                                                                    <tr><th>Invoice</th><td>${sale.invoice_no}</td></tr>
                                                                                                                                                                                                    <tr><th>Customer</th><td>${sale.customer?.name ?? '-'}</td></tr>
                                                                                                                                                                                                    <tr><th>Payable Amount</th><td>{{ __('translation.b_ngn') }} ${sale.payable_amount}</td></tr>
                                                                                                                                                                                                    <tr><th>Paid Amount</th><td>{{ __('translation.b_ngn') }} ${sale.paid_amount}</td></tr>
                                                                                                                                                                                                    <tr><th>Pending Amount</th><td>{{ __('translation.b_ngn') }} ${sale.balance_amount}</td></tr>
                                                                                                                                                                                                    <tr><th>Due Date</th><td>${sale.formatted_due_date ?? '-'}</td></tr>
                                                                                                                                                                                                </table>
                                                                                                                                                                                            `);

                let history = `
                                                                                                                                                                                                <table class="table table-sm">
                                                                                                                                                                                                    <thead>
                                                                                                                                                                                                        <tr>
                                                                                                                                                                                                            <th>{{ __('translation.date') }}</th>
                                                                                                                                                                                                            <th>{{ __('translation.method') }}</th>
                                                                                                                                                                                                            <th>{{ __('translation.b_ngn') }} {{ __('translation.amount') }}</th>
                                                                                                                                                                                                            <th>{{ __('translation.payment_received_by') }}</th>
                                                                                                                                                                                                        </tr>
                                                                                                                                                                                                    </thead>
                                                                                                                                                                                                    <tbody>`;

                if (sale.payments && sale.payments.length > 0) {
                    sale.payments.forEach(function (row) {
                        history += `
                                                                                                                                                                                                        <tr>
                                                                                                                                                                                                            <td>${row.formatted_date}</td>
                                                                                                                                                                                                            <td>${row.method ? row.method.charAt(0).toUpperCase() + row.method.slice(1) : '-'}</td>
                                                                                                                                                                                                            <td>{{ __('translation.b_ngn') }} ${row.amount}</td>
                                                                                                                                                                                                            <td>${row.payment_received_by?.name ?? '-'}</td>
                                                                                                                                                                                                        </tr>`;
                    });
                } else {
                    history += `<tr><td colspan="4" class="text-center">No payment history available</td></tr>`;
                }

                history += `</tbody></table>`;

                $('#paymentHistory').html(history);
                $('#paymentStatusModal').modal('show');
            });
        });
    </script>
@endsection