@extends('backend.layouts.master-horizontal')
@section('title')
    {{Config::get('constants.admin') || $breadcrumb['title'] ?? __('translation.dashboard') }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')

    {{-- ================= FILTER SECTION ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                </div>

                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="from_date" :label="__('translation.from_date')" value="{{ request('from_date') }}" mainrows="2" class="flatdatepickr" />
                            <x-text-input name="to_date" :label="__('translation.to_date')" value="{{ request('to_date')  }}" mainrows="2" class="flatdatepickr" />
                            <x-text-input name="invoice_no" :label="__('translation.invoice_no')" value="{{ request('invoice_no') }}" mainrows="2" />
                            @if(Auth::user()->hasDesignation())
                                <x-select-dropdown name="staff_id" label="Staff" :options="$staffs ?? []" :selected="request('staff_id') ?? ''" class="staff" mainrows="3" />
                            @endif
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
    {{-- ================= LISTING SECTION ================= --}}
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
                                    <th>{{ __('translation.invoice_no') }}</th>
                                    <th>{{ __('translation.customer_name') }}</th>
                                    <th>{{ __('translation.payment_type') }}</th>

                                    <!-- Payment Methods -->
                                    <th class="text-center">{{ __('translation.currency') }} {{ __('translation.cash') }}</th>
                                    <th class="text-center">{{ __('translation.currency') }} {{ __('translation.card') }}</th>
                                    <th class="text-center">{{ __('translation.currency') }} {{ __('translation.transfer') }}</th>

                                    @if(Auth::user()->hasDesignation())
                                        <th>{{ __('translation.staff_name') }}</th>
                                    @endif

                                    <th>{{ __('translation.currency') }} {{ __('translation.total_amount') }}</th>
                                    <th>{{ __('translation.transaction_date') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($sales as $sale)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $sale->invoice_no }}</td>
                                        <td>{{ $sale->customer->name ?? '-' }}</td>
                                        <!-- Payment Type -->
                                        <td>{{ $sale->payment_method == null ? 'Partial Payment' : 'Full Payment' }}</td>
                                        <!-- Payment Breakdown -->
                                        <td class="text-center">{{ __('translation.currency') }} {{ $sale->payments->where('method', 'cash')->sum('amount') ?? 0 }}</td>
                                        <td class="text-center">{{ __('translation.currency') }} {{ $sale->payments->where('method', 'card')->sum('amount') ?? 0 }}</td>
                                        <td class="text-center">{{ __('translation.currency') }} {{ $sale->payments->where('method', 'transfer')->sum('amount') ?? 0 }}</td>
                                        @if(Auth::user()->hasDesignation())
                                            <td>{{ $sale->user->name ?? '-' }}</td>
                                        @endif
                                        <td>{{ __('translation.currency') }}{{ number_format($sale->total, 2) }}</td>
                                        <td>{{ \App\Helpers\Settings::getFormattedDatetime($sale->created_at) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->hasDesignation() ? 10 : 9 }}" class="text-center">{{ __('translation.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="right user-navigation right">
                        {!! $sales->appends(request()->input())->links() !!}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection