@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    {{-- Vendor Info --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>{{ __('translation.company_name') }}:</strong> {{ $vendor->company_name }}
                        </div>
                        <div class="col-md-4">
                            <strong>{{ __('translation.managed_by') }}:</strong> {{ $vendor->name }}
                        </div>

                        <div class="col-md-4">
                            <strong>{{ __('translation.phone') }}:</strong> {{ $vendor->phone }}
                        </div>

                        <div class="col-md-4 text-end">
                            <strong>{{ __('translation.current_balance') }}:</strong>
                            {{ __('translation.currency') }}
                            {{ number_format($vendor->current_balance, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('translation.date') }}</th>
                <th>{{ __('translation.type') }}</th>
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
                    <td>{{ array_key_exists($row->type, $types) ? ucfirst($types[$row->type]) : '---'}}</td>
                    <td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($row->debit) }}</td>
                    <td> {{ __('translation.currency')}} {{ \App\Helpers\Settings::getcustomnumberformat($row->credit) }}</td>
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
@endsection