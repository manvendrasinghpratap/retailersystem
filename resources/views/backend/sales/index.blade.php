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
                                    <th>{{ __('translation.transaction_date') }}</th>
                                    <th>{{ __('translation.cashier') }}</th>
                                    <th>{{ __('translation.b_ngn') . ' ' . __('translation.total_amount') }}</th>
                                    <th>{{ __('translation.payment_type') }}</th>
                                    <th>{{ __('translation.payment_method') }}</th>
                                    <th>{{ __('translation.status') }}</th>
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
                                        <td>{{ App\Helpers\Settings::getFormattedDatetime($sale->created_at)}}</td>
                                        <td>{{ $sale->user->name ?? '-' }}</td>
                                        <td>{{ __('translation.b_ngn') . ' ' . number_format($sale->total, 2) }}</td>
                                        <td>{{ ($sale->payment_method == null) ? 'Partial Payment' : 'Full Payment' }}</td>
                                        <td>{{ $sale->payment_methods }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                {{ ucfirst($sale->status) }}
                                            </span>
                                        </td>
                                        <td><a href="{{ route('admin.sales.show', $sale->id) }}" class="" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('printinvoice', \App\Helpers\Settings::getEncodeCodeWithHashids($sale->id)) }}" class="" title="Receipt" target="_blank"><i class="fas fa-receipt"></i></a>
                                            <a href="{{ route('downloadinvoice', \App\Helpers\Settings::getEncodeCodeWithHashids($sale->id)) }}" class="" title="Download Invoice" target="_blank"><i class="fas fa-download"></i></a>
                                            <a href="javascript:void(0)" class="send-invoice-btn" data-sale-id="{{ $sale->id }}" title="Send Invoice via Email"><i class="fas fa-envelope"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('translation.no_sales_found') }}</td>
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
                        alert('Error: ' + errorMsg);
                    },
                    complete: function () {
                        // Re-enable button
                        btn.prop('disabled', false).html('<i class="fas fa-envelope"></i>');
                    }
                });
            });
        });
    </script>
@endsection