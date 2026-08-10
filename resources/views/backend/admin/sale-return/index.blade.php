@extends('backend.layouts.master-horizontal')
@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    {{-- FILTER SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                    <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                            'pdfId' => 'downloadcouponpdf',
                            'pdfRoute' => route('admin.coupons.exportPdf'),
                            'pdfClass' => 'downloadcouponpdf',
                            'csvId' => 'downloadcouponcsv',
                            'csvRoute' => route('admin.coupons.exportCsv'),
                            'csvClass' => 'downloadcouponcsv',
                        ])
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="code" label="{{__('translation.invoice_no')  }}" value="{{ request('code') ?? '' }}" mainrows="3" />
                            <!-- <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="request('status') ?? ''" mainrows="2" class="accountstatus" /> -->
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" />
                                    <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" />
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
                        {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
                        {{ __('translation.listing') }}
                    </h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.return_no') }}</th>
                                    <th>{{ __('translation.invoice_no') }}</th>
                                    <th>{{ __('translation.customer') }}</th>
                                    <th>{{ __('translation.products') }}</th>
                                    <th>{{ __('translation.return_amount') }}</th>
                                    <th>{{ __('translation.refund') }}</th>
                                    <th>{{ __('translation.status') }}</th>
                                    <th>{{ __('translation.created_at') }}</th>
                                    <th>{{ __('translation.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returns as $return)
                                    <tr>
                                        <td>{{ $returns->firstItem() + $loop->index }}</td>
                                        <td><strong>{{ $return->return_no }}</strong></td>
                                        <td>{{ $return->sale->invoice_no ?? '-' }}</td>
                                        <td>{{ $return->customer->name ?? 'Walk-in' }}</td>
                                        <td style="max-width: 250px;">
                                            @php
                                                $products = $return->items->pluck('product.name')->filter()->implode(', ');
                                            @endphp
                                            {{ Str::limit($products, 50) }}
                                        </td>
                                        <td>{{ __('translation.currency') }}{{ number_format($return->total_amount, 2) }} </td>
                                        <td>
                                            @if($return->refund_type === 'refund')
                                                <span class="badge bg-warning">Refund</span>
                                            @else
                                                <span class="badge bg-info">Credit Adjustment</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($return->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>{{ \App\Helpers\Settings::formatDate($return->created_at, Config::get('constants.dateformat.slashdmy')) }} </td>
                                        <td><a href="{{ route('admin.sale-returns.show', \App\Helpers\Settings::getEncodeCode($return->id)) }}" class="btn btn-sm btn-primary">View</a></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">{{ __('translation.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    @if(!empty($returns) && $returns->count() > 0)
                        <div class="right user-navigation">{!! $returns->appends(request()->input())->links() !!}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>




@endsection