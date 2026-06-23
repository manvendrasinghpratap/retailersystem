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
                    <h4>{{ __('translation.invoice') }}: {{ $sale->invoice_no }}</h4>

                    <p><strong>{{ __('translation.date') }}:</strong> {{ App\Helpers\Settings::getFormattedDatetime($sale->created_at)}}</p>
                    <p><strong>{{ __('translation.cashier') }}:</strong> {{ $sale->user->name ?? '-' }}</p>
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
                                        <td>{{ $item->product->name ?? '-' }}</td>
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
                                    <th colspan="4" class="text-right">{{ __('translation.total') }}</th>
                                    <th>{{ __('translation.b_ngn') . ' ' . number_format($sale->total, 2) }}</th>
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
                        {{array_key_exists('route3Title', $breadcrumb) ? $breadcrumb['route3Title'] : ''}} {{ is_null($sale->payment_method) ? '( Partial Payments )' : '( Full Payment )' }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('translation.s_no') }}</th>
                                    <th>{{ __('translation.payment_method') }}</th>
                                    <th>{{ __('translation.b_ngn') . ' ' . __('translation.amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->payments as $payment)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{ ucfirst($payment->method) }}</td>
                                        <td>{{ __('translation.b_ngn') . ' ' . number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No Sales Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right">{{ __('translation.total') }}</th>
                                    <th>{{ __('translation.b_ngn') . ' ' . number_format($sale->total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection