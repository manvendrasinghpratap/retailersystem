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
                    <h4 class="card-title d-inline-block">{{ __('translation.invoice_details') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>{{ __('translation.invoice') }}: {{ $sale->invoice_no }}</h4>
                            <p>
                                <strong>{{ __('translation.date') }}:</strong>
                                {{ App\Helpers\Settings::getFormattedDatetime($sale->created_at) }}
                            </p>
                            <p>
                                <strong>{{ __('translation.cashier') }}:</strong>
                                {{ $sale->user->name ?? '-' }}
                            </p>
                            <p>
                                <strong>{{ __('translation.customer_name') }}:</strong>
                                {{ $sale->customer->name ?? '-' }}
                            </p>
                            <p>
                                <strong>{{ __('translation.customer_phone') }}:</strong>
                                {{ $sale->customer->phone ?? '-' }}
                            </p>
                            @if(!empty($sale->customer?->email))
                                <p>
                                    <strong>{{ __('translation.customer_email') }}:</strong>
                                    {{ $sale->customer->email }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>{{ __('translation.payment_type') }}:</strong>
                                {{ $sale->customerPaymentType->name ?? ucfirst($sale->payment_type) }}
                            </p>
                            <p>
                                <strong>{{ __('translation.payment_status') }}:</strong>
                                @if($sale->payment_status == 'paid')
                                    <span class="badge bg-success">{{ ucfirst($sale->payment_status) }}</span>
                                @elseif($sale->payment_status == 'partial')
                                    <span class="badge bg-warning">{{ ucfirst($sale->payment_status) }}</span>
                                @else
                                    <span class="badge bg-danger">{{ ucfirst($sale->payment_status) }}</span>
                                @endif
                            </p>
                            <hr>
                            <p>
                                <strong>{{ __('translation.subtotal') }}:</strong> {{ __('translation.b_ngn') }} {{ number_format($sale->subtotal, 2) }}
                            </p>
                            @if($sale->tax > 0)
                                <p>
                                    <strong>{{ __('translation.tax') }}({{ account_setting('general.tax') }}%) :</strong> {{ __('translation.b_ngn') }} {{ number_format($sale->tax, 2) }}
                                </p>
                            @endif
                            @if($sale->discount > 0)
                                <p>
                                    <strong>{{ __('translation.discount') }}:</strong>
                                    {{ __('translation.b_ngn') }}
                                    {{ number_format($sale->discount, 2) }}
                                </p>
                            @endif
                            @if($sale->payment_type == 'credit')
                                <p>
                                    <strong>{{ __('translation.credit_duration') }}:</strong> {{ $sale->creditDuration->name ?? '-' }}
                                </p>
                                <p>
                                    <strong>{{ __('translation.due_date') }}:</strong> {{ $sale->due_date ? App\Helpers\Settings::getFormattedDate($sale->due_date) : '-' }}
                                </p>
                                <p>
                                    <strong>{{ __('translation.interest_rate') }}:</strong> {{ number_format($sale->interest_rate, 2) }}%
                                </p>
                                @if($sale->interest_amount > 0)
                                    <p>
                                        <strong>{{ __('translation.interest_amount') }}:</strong> {{ __('translation.b_ngn') }} {{ number_format($sale->interest_amount, 2) }}
                                    </p>
                                @endif
                            @endif
                            <hr>
                            <p>
                                <strong>{{ __('translation.total_amount') }}:</strong> {{ __('translation.b_ngn') }} {{ number_format($sale->payable_amount > 0 ? $sale->payable_amount : $sale->total, 2) }}
                            </p>
                            @if($sale->payment_type == 'credit')
                                <p>
                                    <strong>{{ __('translation.paid_amount') }}:</strong> {{ __('translation.b_ngn') }} {{ number_format($sale->paid_amount, 2) }}
                                </p>
                                <p>
                                    <strong>{{ __('translation.balance_amount') }}:</strong>
                                    <span class="{{ $sale->balance_amount > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                        {{ __('translation.b_ngn') }} {{ number_format($sale->balance_amount, 2) }}
                                    </span>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{array_key_exists('route2Title', $breadcrumb) ? $breadcrumb['route2Title'] : ''}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('translation.s_no') }}</th>
                                    <th>{{ __('translation.product_name') }}</th>
                                    <th>{{ __('translation.quantity') }}</th>
                                    <th>{{ __('translation.b_ngn') . ' ' . __('translation.price') }}</th>
                                    <th>{{ __('translation.b_ngn') . ' ' . __('translation.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->items as $item)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{!! $item->product->name ?? '-' !!}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ __('translation.b_ngn') . ' ' . number_format($item->price, 2) }}</td>
                                        <td>{{ __('translation.b_ngn') . ' ' . number_format($item->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No Sales Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">
                                        {{ __('translation.subtotal') }}
                                    </th>
                                    <th>
                                        {{ __('translation.b_ngn') }} {{ number_format($sale->subtotal, 2) }}
                                    </th>
                                </tr>
                                @if($sale->tax > 0)
                                    <tr>
                                        <th colspan="4" class="text-end">{{ __('translation.tax') }}({{ account_setting('general.tax') }}%)</th>
                                        <th>{{ __('translation.b_ngn') }} {{ number_format($sale->tax, 2) }}</th>
                                    </tr>
                                @endif
                                @if($sale->discount > 0)
                                    <tr>
                                        <th colspan="4" class="text-end">{{ __('translation.discount') }}</th>
                                        <th class="text-danger">- {{ __('translation.b_ngn') }} {{ number_format($sale->discount, 2) }}
                                        </th>
                                    </tr>
                                @endif
                                @if($sale->interest_amount > 0)
                                    <tr>
                                        <th colspan="4" class="text-end">{{ __('translation.interest_amount') }}</th>
                                        <th>{{ __('translation.b_ngn') }} {{ number_format($sale->interest_amount, 2) }}</th>
                                    </tr>
                                @endif
                                <tr>
                                    <th colspan="4" class="text-end fw-bold">{{ __('translation.grand_total') }}</th>
                                    <th class="fw-bold">{{ __('translation.b_ngn') }} {{ number_format($sale->payable_amount > 0 ? $sale->payable_amount : $sale->total, 2) }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{array_key_exists('route3Title', $breadcrumb) ? $breadcrumb['route3Title'] : ''}} :- {{$sale->customerPaymentType->name}}
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
                                        <td> {{ $payment->paymentMethod->name ?? App\Helpers\Settings::getDataUcfirst($payment->method) ?? '-' }}</td>
                                        <td>{{ __('translation.b_ngn') }} {{ number_format($payment->amount, 2) }}</td>
                                        <td> {{ $payment->paymentReceivedBy->name ?? '-' }} </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center"> {{ __('translation.no_payments_found') }} </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                @if($sale->payment_type == 'credit')
                                    <tr>
                                        <th colspan="4" class="text-end"> {{ __('translation.payable_amount') }}</th>
                                        <th>{{ __('translation.b_ngn') }} {{ number_format($sale->payable_amount, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-end"> {{ __('translation.paid_amount') }}</th>
                                        <th>{{ __('translation.b_ngn') }} {{ number_format($sale->paid_amount, 2) }}
                                        </th>
                                    </tr>

                                    <tr>
                                        <th colspan="4" class="text-end">{{ __('translation.balance_amount') }}</th>
                                        <th class="{{ $sale->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ __('translation.b_ngn') }} {{ number_format($sale->balance_amount, 2) }}
                                        </th>
                                    </tr>
                                @else
                                    <tr>
                                        <th colspan="3" class="text-end"> {{ __('translation.total') }}</th>
                                        <th colspan="2"> {{ __('translation.b_ngn') }} {{ number_format($sale->total, 2) }}</th>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection