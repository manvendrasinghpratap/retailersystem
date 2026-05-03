@extends('backend.layouts.master-horizontal')
@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection
@section('content')
@include('backend.components.breadcrumb')
<div class="row">
    <div class="col-lg-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title d-inline-block">
                    {{ __('translation.vendor_ledger') }}
                </h4>

                <div class="card-title-right">
                    @include('backend.components.exportpdfcsv', [
                        'pdfId' => 'downloadpdf',
                        'pdfRoute' => route('admin.vendors.exportPdf', ['id' => $vendor->id]),
                        'pdfClass' => 'downloadpdf',
                        'pdfLabel' => __('translation.pdf'),
                        'csvId' => 'downloadcsv',
                        'csvRoute' => route('admin.vendors.exportCsv', ['vendor' => $vendor->id]),
                        'csvClass' => 'downloadcsv',
                        'csvLabel' => __('translation.csv'),
                    ])
                </div>
            </div>

            <div class="card-body">

                {{-- Vendor Info --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>{{ __('translation.name') }}:</strong> {{ $vendor->name }}
                    </div>

                    <div class="col-md-4">
                        <strong>{{ __('translation.phone') }}:</strong> {{ $vendor->phone }}
                    </div>

                    <div class="col-md-4 text-end">
                        <strong>{{ __('translation.current_balance') }}:</strong>
                        {{ __('translation.currency') }}
                        {{ number_format($vendor->current_balance,2) }}
                    </div>
                </div>

                {{-- Ledger Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered- table-striped">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('translation.date') }}</th>
                                <th>{{ __('translation.payment_method') }}</th>
                                <th>{{ __('translation.debit') }}</th>
                                <th>{{ __('translation.credit') }}</th>
                                <th>{{ __('translation.balance') }}</th>
                                <th>{{ __('translation.remarks') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($ledgers as $row) 
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \App\Helpers\Settings::getFormattedDatetime($row->created_at) }}</td>
                                <td>{{ (!in_array($row->type,[2,3]) ) ? ucfirst($row->vendorPayment->payment_method) : '---' }}</td>
                                <td>  {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($row->debit) }}</td>
                                <td>  {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($row->credit) }}</td>
                                <td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($row->balance) }}</td>
                                <td>{{ $row->remarks }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ __('translation.no_ledger_found') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $ledgers->links() }}
                </div>

            </div>
        </div>

    </div>
</div>

@endsection