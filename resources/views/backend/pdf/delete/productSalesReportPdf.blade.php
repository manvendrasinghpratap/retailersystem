@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('title',$pdfHeaderdata)?$pdfHeaderdata['title']:'')
@section('content')
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('translation.date') }}</th>
                    <th>{{ __('translation.product') }}</th>
                    <th>{{ __('translation.ngn') .' '.__('translation.price')}}</th>
                    <th>{{ __('translation.sold').' '. __('translation.quantity')}}</th>
                    <th>{{ __('translation.ngn').' '.__('translation.amount')}}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ App\Helpers\Settings::getFormattedDate($row->date) }}</td>
                        <td>{{ $row->product_name }}</td>
                        <td>{{ __('translation.ngn').' '. \App\Helpers\Settings::getcustomnumberformat($row->unit_price) }}</td>
                        <td>{{ $row->total_sold }}</td>
                        <td>{{ __('translation.ngn').' '.\App\Helpers\Settings::getcustomnumberformat($row->total_amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No records found</td>
                    </tr>
                @endforelse
            </tbody>
            @if($orders->isNotEmpty())
                <tfoot>
                    <tr class="font-weight-bold bg-light text-end">
                        <td colspan="4" class="text-start"><strong>{{ __('translation.total') }}</strong></td>
                        <td style="text-align: center !important;">
                            <strong>{{ $orders->sum('total_sold') }}</strong>
                        </td>
                        <td style="text-align: center !important;">
                            <strong>{{ __('translation.ngn').' '. \App\Helpers\Settings::getcustomnumberformat($orders->sum('total_amount')) }}</strong>
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>

@endsection
