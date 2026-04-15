@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading',$pdfHeaderdata)?$pdfHeaderdata['heading']:'')
@section('content')
    <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('translation.product')}}</th>
                    <th>{{ __('translation.quantity')}}</th>
                    <th>{{ __('translation.status')}}</th>
                    <th>{{ __('translation.updated_at')}}</th>
                </tr>
            </thead>
            <tbody> 
                @forelse($stocks as $stock)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $stock->product->title }}</td>
                        <td>{{ $stock->quantity }}</td>
                        <td>{{ ($stock->status && array_key_exists($stock->status,\Config::get('constants.accountstatus'))) ? \Config::get('constants.accountstatus')[$stock->status]:'' }}</td>
                        {{-- <td>{{ App\Helper\Settings::getFormattedDate($stock->updated_at) }}</td> --}}
                        <td>{{ App\Helpers\Settings::getFormattedDate($stock->updated_at)}}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">@lang('translation.no_data_found')</td>
                    </tr>
                @endforelse
            </tbody>
    </table>

@endsection
