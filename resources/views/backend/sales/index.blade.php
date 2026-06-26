@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}}
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                    <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloadsalespdf',    
                        'pdfRoute' => route('admin.sales.exportPdf'),
                        'pdfClass' => 'downloadsalespdf',
                        'csvId' =>'downloadsalescsv',    
                        'csvRoute' => route('admin.sales.exportCsv'),
                        'csvClass' => 'downloadsalescsv',
                        ])                 
                    </div>      
                </div>
                <div class="card-body">
                    <form name="cartlistingform" id="cartlistingform" method="GET">
                        <div class="row">
                            {{-- Sale Date --}}
                            <x-text-input name="from_date" :label="__('translation.from_date')" value="{{ request('from_date') }}" mainrows="2" class="flatdatepickr" />
                            <x-text-input name="to_date" :label="__('translation.to_date')" value="{{ request('to_date')  }}" mainrows="2" class="flatdatepickr" />
                            <x-text-input name="invoice_no" :label="__('translation.invoice_no')" value="{{ request('invoice_no') }}" mainrows="3" />
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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{array_key_exists('route2Title', $breadcrumb) ? $breadcrumb['route2Title'] : ''}} {{ request()->has('invoice_no') ? '' : (request('from_date') ? request('from_date') : App\Helpers\Settings::getFormattedDate(date('Y-m-d'))) }} {{ request()->has('invoice_no') ? '' : '-' }} {{ request()->has('invoice_no') ? '' : (request('to_date') ? request('to_date') : App\Helpers\Settings::getFormattedDate(date('Y-m-d'))) }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('translation.customer_name') }}</th>
                                    <th>{{ __('translation.customer_phone') }}</th>
                                    <th>{{ __('translation.customer_email') }}</th>
                                    <th>{{ __('translation.invoice_no') }}</th>
                                    <th>{{ __('translation.cashier') }}</th>
                                    <th>{{ __('translation.payment_type') }}</th>
                                    <th>{{ __('translation.payment_status') }}</th>
                                    <th>{{ __('translation.payment_method') }}</th>
                                    <th>{{ __('translation.b_ngn') . ' ' . __('translation.total_amount') }}</th>
                                    <th>{{ __('translation.transaction_date') }}</th>
                                    <th width="80">{{ __('translation.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->customer->name ?? '-' }}</td>
                                        <td>{{ $sale->customer->phone ?? '-' }}</td>
                                        <td>{{ $sale->customer->email ?? '-' }}</td>
                                        <td>{{ $sale->invoice_no }}</td>
                                        <td>{{ $sale->user->name ?? '-' }}</td>
                                        <td>{{ ucfirst($paymentTypes[$sale->payment_type]) ?? '-' }}</td>
                                        <td><a href="javascript:void(0)" class="payment-status-btn" data-sale-id="{{ $sale->id }}"> {{ ucfirst($sale->payment_status) }}</a></td>
                                        <td>{{ ucfirst($sale->payment_methods) }}</td>
                                        <td>{{ __('translation.b_ngn') . ' ' . number_format($sale->total, 2) }}</td>
                                        <td>{{ App\Helpers\Settings::getFormattedDatetime($sale->created_at)}}</td>
                                        <td><a href="{{ route('admin.sales.show', \App\Helpers\Settings::getEncodeCodeWithHashids($sale->id)) }}" class="" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('printinvoice', \App\Helpers\Settings::getEncodeCodeWithHashids($sale->id)) }}" class="" title="Receipt" target="_blank"><i class="fas fa-receipt"></i></a>
                                            <a href="{{ route('downloadinvoice', \App\Helpers\Settings::getEncodeCodeWithHashids($sale->id)) }}" class="" title="Download Invoice" target="_blank"><i class="fas fa-download"></i></a>
                                            <a href="javascript:void(0)" class="send-invoice-btn" data-sale-id="{{ $sale->id }}" title="Send Invoice via Email"><i class="fas fa-envelope"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">{{ __('translation.no_sales_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(!empty($products) && $products->count() > 0)
                        <div class="right user-navigation right">{!! $products->appends(request()->input())->links() !!}</div>
                    @endif
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('.send-invoice-btn').on('click', function () {
                var saleId = $(this).data('sale-id');
                var btn = $(this);

                // Disable button and show loading state
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
                        var errorMsg = 'Failed to send invoice. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showAlert('error', 'Error', errorMsg);
                    },
                    complete: function () {
                        // Re-enable button
                        btn.prop('disabled', false).html('<i class="fas fa-envelope"></i>');
                    }
                });
            });
        });

        $(document).on('click', '.payment-status-btn', function () {
            let saleId = $(this).data('sale-id');
            showLoader();
             $('#creditPaymentForm').show();
            $.get(
                '{{ route("admin.sales.payment-details", "saleId") }}'.replace('saleId', saleId),
                function (res) {
                    console.log(res);
                    if (res.sale.payment_status == 'paid') {
                        $('#creditPaymentForm').hide();
                        hideLoader();
                       // return;
                    }                   
                    let options = '<option value="">Select Payment Method</option>';

                    $.each(res.payment_methods, function(id, name) {
                        options += `<option value="${id}">${name}</option>`;
                    });

                    $('#payment_method_id').html(options);
                    hideLoader();
                    let sale = res.sale;
                    $('#sale_id').val(sale.id);
                    $('#paymentDetails').html(`
                    <table class="table table-bordered">
                        <tr>
                            <th>Invoice</th>
                            <td>${sale.invoice_no}</td>
                        </tr>
                        <tr>
                            <th>Customer</th>
                            <td>${sale.customer?.name ?? '-'}</td>
                        </tr>
                        <tr>
                            <th>Payable Amount</th>
                            <td>{{ __('translation.b_ngn') }} ${sale.payable_amount}</td>
                        </tr>
                        <tr>
                            <th>Paid Amount</th>
                            <td>{{ __('translation.b_ngn') }} ${sale.paid_amount}</td>
                        </tr>
                        <tr>
                            <th>Pending Amount</th>
                            <td>{{ __('translation.b_ngn') }} ${sale.balance_amount}</td>
                        </tr>
                        <tr>
                            <th>Due Date</th>
                            <td>${sale.formatted_due_date ?? '-'}</td>
                        </tr>
                    </table>
                `);
                let history = `
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('translation.date') }}</th>
                                <th>{{ __('translation.method') }}</th>
                                <th>{{ __('translation.b_ngn') . ' ' . __('translation.amount') }}</th>
                                <th>{{ __('translation.payment_received_by') }}</th>
                            </tr>
                        </thead>
                        <tbody>`;
                sale.payments.forEach(function (row) {
                    history += `
                        <tr> 
                            <td>${row.formatted_date}</td>
                            <td>${row.method ? row.method.charAt(0).toUpperCase() + row.method.slice(1) : '-'}</td>
                            <td>{{ __('translation.b_ngn') }} ${row.amount}</td>
                            <td>${row.payment_received_by?.name ?? '-'}</td>
                        </tr>
                    `;
                });
                history += `
                        </tbody>
                    </table>
                `;
                $('#paymentHistory').html(history);
                $('#paymentStatusModal').modal('show');
            });
        });

        


        $(document).ready(function() {
            setupPdfDownload('.downloadsalespdf', 'data-downloadroutepdf');
            setupPdfDownload('.downloadsalescsv', 'data-downloadroutepdf');
        });
    </script>
@endsection