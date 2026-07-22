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
                            'pdfId' => 'downloadvendorpdf',
                            'pdfRoute' => route('admin.vendors.exportPdf'),
                            'pdfClass' => 'downloadvendorpdf',

                            'csvId' => 'downloadvendorcsv',
                            'csvRoute' => route('admin.vendors.exportCsv'),
                            'csvClass' => 'downloadvendorcsv',
                        ])
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="company_name" label="{{ __('translation.company_name') }}" value="{{ request()->company_name ?? '' }}" mainrows="2" />
                            <x-text-input name="vendor_name" label="{{ __('translation.vendor_name') }}" value="{{ request()->vendor_name ?? '' }}" mainrows="2" />
                            <x-text-input name="phone" label="{{ __('translation.phone') }}" value="{{ request()->phone ?? '' }}" mainrows="2" class="onlyinteger" />
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="request()->status ?? ''" class="accountstatus" mainrows="2" />
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

    {{-- LISTING SECTION --}}
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
                                    <th>{{ __('translation.vendor_code') }}</th>
                                    <th>{{ __('translation.company_name') }}</th>
                                    <th>{{ __('translation.vendor_name') }}</th>
                                    <th>{{ __('translation.phone') }}</th>
                                    <th>{{ __('translation.email') }}</th>
                                    <th>{{ __('translation.opening_balance') }}</th>
                                    <th>{{ __('translation.current_balance') }}</th>
                                    <th>{{ __('translation.status') }}</th>
                                    <th>{{ __('translation.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($vendors) && $vendors->count() > 0)
                                    @foreach($vendors as $vendor)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $vendor->vendor_code }}</td>
                                            <td>{{ $vendor->company_name }}</td>
                                            <td>{{ $vendor->name }}</td>
                                            <td>{{ $vendor->phone }}</td>
                                            <td>{{ $vendor->email }}</td>
                                            <td>{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($vendor->opening_balance) }}</td>
                                            <td>{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($vendor->current_balance) }}</td> 
                                            <td>
                                                @if($vendor->status == 1)
                                                    <span class="badge bg-success">{{ __('translation.active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('translation.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <x-href-input name="edit" label="Edit" required href="{{ route('admin.vendors.edit', ['id' => \App\Helpers\Settings::getEncodeCode($vendor->id)]) }}" />
                                                <x-href-input name="payment" action="payment" method="get" label="Payment" required href="{{ route('admin.vendors.paymentForm', ['id' => \App\Helpers\Settings::getEncodeCode($vendor->id)]) }}" />
                                                <x-href-input name="ledger" action="ledger" method="get" label="Ledger" required href="{{ route('admin.vendors.ledger', \App\Helpers\Settings::getEncodeCode($vendor->id)) }}" />
                                                <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($vendor->id) }}" data-routeurl="{{ route('admin.vendors.delete', $vendor->id) }}" />
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="10" class="text-center">{{ __('translation.no_data_found') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if(!empty($vendors) && $vendors->count() > 0)
                        <div class="right user-navigation">{!! $vendors->appends(request()->input())->links() !!}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    $(document).ready(function () {
        setupPdfDownload('.downloadvendorpdf', 'data-downloadroutepdf');
        setupPdfDownload('.downloadvendorcsv', 'data-downloadroutepdf');
    });
</script>
@endsection