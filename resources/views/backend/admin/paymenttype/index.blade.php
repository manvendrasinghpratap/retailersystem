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
                            'pdfId' => 'downloadpaymenttypepdf',
                            'pdfRoute' => route('admin.payment-types.exportPdf'),
                            'pdfClass' => 'downloadpaymenttypepdf',

                            'csvId' => 'downloadpaymenttypecsv',
                            'csvRoute' => route('admin.payment-types.exportCsv'),
                            'csvClass' => 'downloadpaymenttypecsv',
                        ])
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="name" label="{{ __('translation.name') }}" value="{{ request()->get('name') }}" mainrows="3" />
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="request()->get('status')" class="accountstatus" mainrows="2" />
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" />
                                    <x-filter-href-button name="reset" href="{{ route($breadcrumb['route2']) }}" label="{{ __('translation.reset') }}" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- LISTING --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"> {{ $breadcrumb['title'] }} {{ __('translation.listing') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <!-- <th>{{ __('translation.short_name') }}</th> -->
                                    <th>{{ __('translation.name') }}</th>
                                    <th>{{ __('translation.status') }}</th>
                                    <th>{{ __('translation.createdat') }}</th>
                                    <th>{{ __('translation.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentTypes as $paymentType)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <!-- <td>{{ $paymentType->short_name }}</td> -->
                                        <td>{{ $paymentType->name }}</td>
                                        <td>
                                            @if($paymentType->status)
                                                <span class="badge bg-success">
                                                    {{ __('translation.active') }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    {{ __('translation.inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $paymentType->created_date }}</td>
                                        <td>
                                            <x-href-input name="edit" label="{{ __('translation.edit') }}" href="{{ route('admin.payment-types.edit', \App\Helpers\Settings::getEncodeCode($paymentType->id)) }}" />
                                            <x-deletehref-input name="DeleteButton" label="{{ __('translation.delete') }}" href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($paymentType->id) }}" data-routeurl="{{ route('admin.payment-types.softdelete', \App\Helpers\Settings::getEncodeCode($paymentType->id)) }}" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('translation.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($paymentTypes->count())
                        <div class="right user-navigation">
                            {!! $paymentTypes->appends(request()->input())->links() !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            setupPdfDownload('.downloadpaymenttypepdf', 'data-downloadroutepdf');
            setupPdfDownload('.downloadpaymenttypecsv', 'data-downloadroutepdf');
        });
    </script>
@endsection