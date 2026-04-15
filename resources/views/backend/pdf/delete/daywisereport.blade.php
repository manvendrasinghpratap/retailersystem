@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')
    @php
        $grandTaken = 0;
        $grandSold = 0;
        $grandReturned = 0;
        $grandIncentive = 0;
        $grandAmount = 0;
    @endphp

    @foreach($grouped as $date => $staffs)
        @php
            $dateTaken = 0;
            $dateSold = 0;
            $dateReturned = 0;
            $dateIncentive = 0;
            $dateAmount = 0;
        @endphp

        <h4 class="mt-4 text-primary">Date: {{ \App\Helpers\Settings::getFormattedDate($date) }}</h4>

        @foreach($staffs as $staffId => $staffData)
            <h5 class="mt-3">{{ $staffData['staff'] }}</h5>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>{{ __('translation.product') }}</th>
                        <th>{{ __('translation.ngn') }} {{ __('translation.price') }}</th>
                        <th>{{ __('translation.taken') }}</th>
                        <th>{{ __('translation.sold') }}</th>
                        <th>{{ __('translation.returned') }}</th>
                        <th>{{ __('translation.incentive') }}</th>
                        <th>{{ __('translation.ngn') }} {{ __('translation.amount') }} </th>
                        <th>{{ __('translation.remarks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sum = 0; @endphp
                    @foreach($staffData['records'] as $rec)
                        @php
                            $amount = $rec['price'] * $rec['sold'];
                            $sum += $amount;

                            // accumulate per-date totals
                            $dateTaken += $rec['taken'];
                            $dateSold += $rec['sold'];
                            $dateReturned += $rec['returned'];
                            $dateIncentive += $rec['incentive'];
                            $dateAmount += $amount;

                            // accumulate grand totals
                            $grandTaken += $rec['taken'];
                            $grandSold += $rec['sold'];
                            $grandReturned += $rec['returned'];
                            $grandIncentive += $rec['incentive'];
                            $grandAmount += $amount;
                        @endphp
                        <tr>
                            <td>{{ $rec['product'] }}</td>
                            <td>{{ __('translation.ngn') }} {{ \App\Helpers\Settings::getcustomnumberformat($rec['price']) }}</td>
                            <td>{{ $rec['taken'] }}</td>
                            <td>{{ $rec['sold'] }}</td>
                            <td>{{ $rec['returned'] }}</td>
                            <td>{{ $rec['incentive'] }}</td>
                            <td>{{ __('translation.ngn') }} {{ \App\Helpers\Settings::getcustomnumberformat($amount) }}</td>
                            <td>{!! $rec['remarks'] !!}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-secondary fw-bold">
                        <td colspan="2">Staff Totals</td>
                        <td style="text-align: center">{{ $staffData['totals']['taken'] ?? 0 }}</td>
                        <td style="text-align: center">{{ $staffData['totals']['sold'] ?? 0 }}</td>
                        <td style="text-align: center">{{ $staffData['totals']['returned'] ?? 0 }}</td>
                        <td style="text-align: center">{{ $staffData['totals']['incentive'] ?? 0 }}</td>
                        <td style="text-align: center">{{ __('translation.ngn') }} {{ \App\Helpers\Settings::getcustomnumberformat($sum) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @endforeach
    @endforeach

    {{-- GRAND TOTAL SECTION ON NEW PAGE --}}
    <div style="page-break-before: always;"></div>
    <h3 class="mt-4 text-center text-danger">Grand Total Summary</h3>

    <table class="table table-bordered table-sm mt-3">
        <thead class="table-dark">
            <tr>
                <th>{{ __('translation.total') }} {{ __('translation.taken') }}</th>
                <th>{{ __('translation.total') }} {{ __('translation.sold') }}</th>
                <th>{{ __('translation.total') }} {{ __('translation.returned') }}</th>
                <th>{{ __('translation.total') }} {{ __('translation.incentive') }}</th>
                <th>{{ __('translation.ngn') }} {{ __('translation.total') }} {{ __('translation.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr class="fw-bold">
                <td>{{ $grandTaken }}</td>
                <td>{{ $grandSold }}</td>
                <td>{{ $grandReturned }}</td>
                <td>{{ $grandIncentive }}</td>
                <td>{{ __('translation.ngn') }} {{ \App\Helpers\Settings::getcustomnumberformat($grandAmount) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
