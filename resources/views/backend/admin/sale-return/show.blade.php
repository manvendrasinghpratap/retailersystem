@extends('backend.layouts.master-horizontal')
@section('title')
    {{ $return->return_no ?? 'Sale Return Details' }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    <div class="row">
        {{-- Return Information --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ __('translation.sale_return_details') }}</h4>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i>{{ __('translation.back') }}</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Return Number --}}
                        <div class="col-md-3 mb-3"><label class="fw-bold">{{ __('translation.return_no') }}</label>
                            <div>{{ $return->return_no ?? '-' }}</div>
                        </div>
                        {{-- Invoice Number --}}
                        <div class="col-md-3 mb-3"><label class="fw-bold">{{ __('translation.invoice_no') }}</label>
                            <div>{{ $return->sale->invoice_no ?? '-' }}</div>
                        </div>
                        {{-- Customer --}}
                        <div class="col-md-3 mb-3"><label class="fw-bold">{{ __('translation.customer') }}</label>
                            <div>{{ $return->customer->name ?? '-' }}</div>
                        </div>
                        {{-- Return Date --}}
                        <div class="col-md-3 mb-3"><label class="fw-bold">{{ __('translation.return_date') }}</label>
                            <div>{{ $return->created_at ? \App\Helpers\Settings::getFormattedDatetime($return->created_at) : '-' }}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        {{-- Total Return Amount --}}
                        <div class="col-md-3 mb-3"> <label class="fw-bold">{{ __('translation.total_return_amount') }}</label>
                            <div class="text-danger fs-5"> {{ __('translation.b_ngn') }} {{ \App\Helpers\Settings::getcustomnumberformat($return->total_amount ?? 0, 2) }}</div>
                        </div>
                        {{-- Refund Type --}}
                        <div class="col-md-3 mb-3"><label class="fw-bold">{{ __('translation.refund_type') }}</label>
                            <div>{{ ucfirst($return->refund_type ?? '-') }}</div>
                        </div>
                        {{-- Payment Method --}}
                        <div class="col-md-3 mb-3"><label class="fw-bold">{{ __('translation.payment_method') }}</label>
                            <div>{{ ucfirst($return->payment_method ?? __('translation.wallet_balance')) }}</div>
                        </div>
                        <div class="col-md-3 mb-3"><label class="fw-bold">{{ __('translation.status') }}</label>
                            <div>
                                @if($return->status === 'completed')
                                    <span class="badge bg-success">{{ ucfirst($return->status ?? '-') }}</span>
                                @elseif($return->status === 'cancelled')
                                    <span class="badge bg-danger">{{ ucfirst($return->status ?? '-') }}</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ ucfirst($return->status ?? '-') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Status --}}

                        {{-- Reason --}}
                        <div class="col-md-9 mb-3">
                            <label class="fw-bold">{{ __('translation.reason') }}</label>
                            <div>{{ $return->reason ?? '-' }}</div>
                        </div>
                    </div>
                    @if(!empty($return->note))
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="fw-bold">{{ __('translation.note') }}</label>
                                <div>{{ $return->note }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        {{-- Returned Items --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('translation.returned_items') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.product') }}</th>
                                    <th>{{ __('translation.barcode') }}</th>
                                    <th>{{ __('translation.sku') }}</th>
                                    <th>{{ __('translation.quantity') }}</th>
                                    <th>{{ __('translation.price') }}</th>
                                    <th>{{ __('translation.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($return->items as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        {{-- Product --}}
                                        <td>{{ $item->product->name ?? '-' }}</td>
                                        {{-- Barcode --}}
                                        <td>@if($item->tracking) {!! DNS1D::getBarcodeHTML($item->tracking->barcode, \Config::get('constants.BARCODEDECODE')) !!} <small>
                                            {{ $item->tracking->barcode }}
                                        </small> @else - @endif</td>
                                        {{-- Sku --}}
                                        <td>{{ $item->product->sku ?? '-' }}</td>
                                        {{-- Quantity --}}
                                        <td>{{ $item->quantity ?? 0 }}</td>
                                        {{-- Price --}}
                                        <td>{{ __('translation.b_ngn') }}{{ \App\Helpers\Settings::getcustomnumberformat($item->price ?? 0) }}</td>
                                        {{-- Total --}}
                                        <td><span class="text-danger">{{ __('translation.b_ngn') }}{{ \App\Helpers\Settings::getcustomnumberformat($item->total ?? 0) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('translation.no_returned_items_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($return->items && $return->items->count())
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th colspan="5" class="text-end">{{ __('translation.total_returned') }}</th>
                                        <th class="text-danger">{{ __('translation.b_ngn') }}{{ \App\Helpers\Settings::getcustomnumberformat($return->items->sum('total')) }}</th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- Return Payment Details --}}
        @if($return->payments && $return->payments->count())
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('translation.refund_payments') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('translation.payment_method') }}</th>
                                        <th>{{ __('translation.amount') }}</th>
                                        <th>{{ __('translation.payment_received_by') }}</th>
                                        <th>{{ __('translation.created_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($return->payments as $payment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ ucfirst($payment->method ?? '-') }}</td>
                                            <td>{{ __('translation.b_ngn') }}{{ \App\Helpers\Settings::getcustomnumberformat($payment->amount ?? 0) }}</td>
                                            <td>{{ $payment->payment_received_by ?? '-' }}</td>
                                            <td>{{ $payment->created_at ? \App\Helpers\Settings::getFormattedDatetime($payment->created_at) : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection