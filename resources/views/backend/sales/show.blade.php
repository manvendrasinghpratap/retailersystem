@extends('backend.layouts.master-horizontal')
@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection

@section('content')
    @include('backend.components.breadcrumb')

    <!-- Invoice Summary Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.invoice_details') }}</h4>
                    <span class="badge {{ $sale->payment_approval_status == 'approve' ? 'bg-success' : ($sale->payment_approval_status == 'pending' ? 'bg-warning' : 'bg-danger') }} fs-6">
                        {{ __('translation.approval_status') }}: {{ ucfirst($sale->payment_approval_status ?? 'Pending') }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Column 1: Customer & Invoice Info -->
                        <div class="col-md-4">
                            <h5 class="text-primary mb-3">{{ __('translation.invoice') }}: {{ $sale->invoice_no }}</h5>
                            <p class="mb-2">
                                <strong>{{ __('translation.date') }}:</strong>
                                {{ App\Helpers\Settings::getFormattedDatetime($sale->created_at) }}
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('translation.cashier') }}:</strong>
                                {{ $sale->user->name ?? '-' }}
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('translation.customer_name') }}:</strong>
                                {{ $sale->customer->name ?? '-' }}
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('translation.customer_phone') }}:</strong>
                                {{ $sale->customer->phone ?? '-' }}
                            </p>
                            @if(!empty($sale->customer?->email))
                                <p class="mb-2">
                                    <strong>{{ __('translation.customer_email') }}:</strong>
                                    {{ $sale->customer->email }}
                                </p>
                            @endif
                        </div>

                        <!-- Column 2: Payment & Approval Info -->
                        <div class="col-md-4">
                            <h5 class="text-secondary mb-3">{{ __('translation.payment_information') }}</h5>
                            <p class="mb-2">
                                <strong>{{ __('translation.payment_type') }}:</strong>
                                {{ $sale->customerPaymentType->name ?? ucfirst($sale->payment_type) }}
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('translation.payment_status') }}:</strong>
                                @if($sale->payment_status == 'paid')
                                    <span class="badge bg-success">{{ ucfirst($sale->payment_status) }}</span>
                                @elseif($sale->payment_status == 'partial')
                                    <span class="badge bg-warning">{{ ucfirst($sale->payment_status) }}</span>
                                @else
                                    <span class="badge bg-danger">{{ ucfirst($sale->payment_status) }}</span>
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('translation.approval_status') }}:</strong>
                                @if($sale->payment_approval_status == 'approve')
                                    <span class="badge bg-success">{{ ucfirst($sale->payment_approval_status) }}</span>
                                @elseif($sale->payment_approval_status == 'pending')
                                    <span class="badge bg-warning">{{ ucfirst($sale->payment_approval_status) }}</span>
                                @else
                                    <span class="badge bg-danger">{{ ucfirst($sale->payment_approval_status ?? 'N/A') }}</span>
                                @endif
                            </p>
                            @if(!empty($sale->payment_approved_by))
                                <p class="mb-2">
                                    <strong>{{ __('translation.approved_by') }}:</strong>
                                    {{ $sale->paymentApprovedBy->name ?? '-' }}
                                </p>
                            @endif

                            @if($sale->payment_type == 'credit')
                                <hr class="my-2">
                                <p class="mb-1">
                                    <strong>{{ __('translation.credit_duration') }}:</strong> {{ $sale->creditDuration->name ?? '-' }}
                                </p>
                                <p class="mb-1">
                                    <strong>{{ __('translation.due_date') }}:</strong> {{ $sale->due_date ? App\Helpers\Settings::getFormattedDate($sale->due_date) : '-' }}
                                </p>
                                <p class="mb-1">
                                    <strong>{{ __('translation.interest_rate') }}:</strong> {{ number_format($sale->interest_rate, 2) }}%
                                </p>
                            @endif
                        </div>

                        <!-- Column 3: Fulfillment & Delivery Details -->
                        <div class="col-md-4">
                            <h5 class="text-secondary mb-3">{{ __('translation.fullfillment_method') }}</h5>
                            <p class="mb-2">
                                <strong>{{ __('translation.fullfillment_method') }}:</strong>
                                <span class="badge bg-info text-dark">
                                    {{ \App\Helpers\Settings::getDataTitle($sale->delivery_type ?? 'pickup') }}
                                </span>
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('translation.delivery_type') }}:</strong>
                                @if(($sale->delivery_status ?? 'delivered') == 'delivered')
                                    <span class="badge bg-success">{{ \App\Helpers\Settings::getDataTitle($sale->delivery_type ?? 'Home Delivered') }}</span>
                                @elseif(($sale->delivery_status ?? '') == 'pending')
                                    <span class="badge bg-warning">{{ \App\Helpers\Settings::getDataTitle($sale->delivery_status) }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ \App\Helpers\Settings::getDataTitle($sale->delivery_status ?? 'N/A') }}</span>
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('translation.delivery_charges') }}:</strong>
                                {{ __('translation.b_ngn') }} {{ number_format($sale->delivery_charge ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ array_key_exists('route2Title', $breadcrumb) ? $breadcrumb['route2Title'] : '' }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('translation.s_no') }}</th>
                                    <th>{{ __('translation.product_name') }}</th>
                                    <th class="text-center">{{ __('translation.barcode') }}</th>
                                    <th class="text-center">{{ __('translation.quantity') }}</th>
                                    <th class="text-end">{{ __('translation.b_ngn') . ' ' . __('translation.price') }}</th>
                                    <th class="text-end">{{ __('translation.b_ngn') . ' ' . __('translation.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->items as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{!! $item->product->name ?? '-' !!}</td>
                                        <td class="text-center">
                                            @if(!empty($item->product?->barcode))
                                                <div class="d-inline-block text-center">
                                                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($item->product->barcode, 'C128') }}" alt="barcode" style="width:120px; height:35px;" /><br>
                                                    <small class="text-muted">{{ $item->product->barcode }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ __('translation.b_ngn') . ' ' . number_format($item->price, 2) }}</td>
                                        <td class="text-end">{{ __('translation.b_ngn') . ' ' . number_format($item->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('translation.no_sales_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">{{ __('translation.subtotal') }}</th>
                                    <th class="text-end">{{ __('translation.b_ngn') }} {{ number_format($sale->subtotal, 2) }}</th>
                                </tr>
                                @if($sale->tax > 0)
                                    <tr>
                                        <th colspan="5" class="text-end">{{ __('translation.tax') }}({{ account_setting('general.tax') }}%)</th>
                                        <th class="text-end">{{ __('translation.b_ngn') }} {{ number_format($sale->tax, 2) }}</th>
                                    </tr>
                                @endif
                                @if($sale->discount > 0)
                                    <tr>
                                        <th colspan="5" class="text-end">{{ __('translation.discount') }}</th>
                                        <th class="text-danger text-end">- {{ __('translation.b_ngn') }} {{ number_format($sale->discount, 2) }}</th>
                                    </tr>
                                @endif
                                @if(($sale->delivery_charge ?? 0) > 0)
                                    <tr>
                                        <th colspan="5" class="text-end">{{ __('translation.delivery_charges') }}</th>
                                        <th class="text-end">{{ __('translation.b_ngn') }} {{ number_format($sale->delivery_charge, 2) }}</th>
                                    </tr>
                                @endif
                                @if($sale->interest_amount > 0)
                                    <tr>
                                        <th colspan="5" class="text-end">{{ __('translation.interest_amount') }}</th>
                                        <th class="text-end">{{ __('translation.b_ngn') }} {{ number_format($sale->interest_amount, 2) }}</th>
                                    </tr>
                                @endif
                                <tr>
                                    <th colspan="5" class="text-end fw-bold">{{ __('translation.grand_total') }}</th>
                                    <th class="fw-bold text-end">{{ __('translation.b_ngn') }} {{ number_format($sale->payable_amount > 0 ? $sale->payable_amount : $sale->total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ array_key_exists('route3Title', $breadcrumb) ? $breadcrumb['route3Title'] : '' }} :- {{ $sale->customerPaymentType->name ?? ucfirst($sale->payment_type) }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.date') }}</th>
                                    <th>{{ __('translation.payment_method') }}</th>
                                    <th>{{ __('translation.amount') }}</th>
                                    <th>{{ __('translation.payment_received_by') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->payments as $payment)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ App\Helpers\Settings::getFormattedDatetime($payment->created_at) }}</td>
                                        <td>{{ $payment->paymentMethod->name ?? App\Helpers\Settings::getDataUcfirst($payment->method) ?? '-' }}</td>
                                        <td>{{ __('translation.b_ngn') }} {{ App\Helpers\Settings::getcustomnumberformat($payment->amount, 2) }}</td>
                                        <td>{{ $payment->paymentReceivedBy->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ __('translation.no_payments_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                @if($sale->payment_type == 'credit')
                                    <tr>
                                        <th colspan="4" class="text-end">{{ __('translation.payable_amount') }}</th>
                                        <th>{{ __('translation.b_ngn') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->payable_amount, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-end">{{ __('translation.paid_amount') }}</th>
                                        <th>{{ __('translation.b_ngn') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->paid_amount, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-end">{{ __('translation.balance_amount') }}</th>
                                        <th class="{{ $sale->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ __('translation.b_ngn') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->balance_amount, 2) }}
                                        </th>
                                    </tr>
                                @else
                                    <tr>
                                        <th colspan="3" class="text-end">{{ __('translation.total') }}</th>
                                        <th colspan="2">{{ __('translation.b_ngn') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->total) }}</th>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection