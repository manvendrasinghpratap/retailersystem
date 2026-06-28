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

                <h4 class="card-title d-inline-block">
                    {{ __('translation.filter') }}
                </h4>

                <div class="d-inline-block">

                    @include('backend.components.exportpdfcsv',[
                        'pdfId'=>'downloadcreditdurationpdf',
                        'pdfRoute'=>route('admin.credit-durations.exportPdf'),
                        'pdfClass'=>'downloadcreditdurationpdf',

                        'csvId'=>'downloadcreditdurationcsv',
                        'csvRoute'=>route('admin.credit-durations.exportCsv'),
                        'csvClass'=>'downloadcreditdurationcsv',
                    ])

                </div>

            </div>

            <div class="card-body">

                <form method="GET">

                    <div class="row">

                        <x-text-input
                            name="name"
                            label="{{ __('translation.name') }}"
                            value="{{ request()->get('name') }}"
                            mainrows="3"
                        />

                        <x-text-input
                            name="duration_days"
                            label="{{ __('translation.duration_days') }}"
                            type="number"
                            value="{{ request()->get('duration_days') }}"
                            mainrows="2"
                        />

                        <x-select-dropdown
                            name="status"
                            label="{{ __('translation.status') }}"
                            :options="config('constants.accountstatus')"
                            :selected="request()->get('status')"
                            mainrows="2"
                            class="accountstatus"
                        />

                        <div class="col-xl-2 col-md-2">

                            <div class="form-group mb-3">

                                <label class="d-inline-block w-100">&nbsp;</label>

                                <x-filter-submit-button
                                    name="submit"
                                    label="{{ __('translation.filter') }}"
                                />

                                <x-filter-href-button
                                    name="reset"
                                    href="{{ route($breadcrumb['route2']) }}"
                                    label="{{ __('translation.reset') }}"
                                />

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

                <h4 class="card-title">

                    {{ $breadcrumb['title'] }}

                    {{ __('translation.listing') }}

                </h4>

            </div>

            <div class="card-body">

                <div class="table-responsive overflowx">

                    <table class="table table-striped align-middle">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>{{ __('translation.name') }}</th>

                                <th>{{ __('translation.duration_days') }}</th>

                                <th>{{ __('translation.interest') }}</th>

                                <th>{{ __('translation.status') }}</th>

                                <th>{{ __('translation.createdat') }}</th>

                                <th>{{ __('translation.actions') }}</th>

                            </tr>

                        </thead>

                        <tbody>

                        @forelse($creditDurations as $creditDuration)

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $creditDuration->name }}</td>

                                <td>

                                    {{ $creditDuration->duration_days }}

                                    {{ __('translation.days') }}

                                </td>

                                <td>

                                    {{ number_format($creditDuration->interest,2) }}%

                                </td>

                                <td>

                                    @if($creditDuration->status)

                                        <span class="badge bg-success">

                                            {{ __('translation.active') }}

                                        </span>

                                    @else

                                        <span class="badge bg-danger">

                                            {{ __('translation.inactive') }}

                                        </span>

                                    @endif

                                </td>

                                <td>

                                    {{ $creditDuration->created_date ?? \App\Helpers\Settings::getFormattedDatetime($creditDuration->created_at) }}

                                </td>

                                <td>

                                    <x-href-input
                                        name="edit"
                                        label="{{ __('translation.edit') }}"
                                        href="{{ route('admin.credit-durations.edit',\App\Helpers\Settings::getEncodeCode($creditDuration->id)) }}"
                                    />

                                    <x-deletehref-input
                                        name="DeleteButton"
                                        label="{{ __('translation.delete') }}"
                                        href="javascript:void(0)"
                                        class="deleteData"
                                        data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($creditDuration->id) }}"
                                        data-routeurl="{{ route('admin.credit-durations.destroy',$creditDuration->id) }}"
                                    />

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="text-center">

                                    {{ __('translation.no_data_found') }}

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

                @if($creditDurations->count())

                    <div class="right user-navigation">

                        {!! $creditDurations->appends(request()->input())->links() !!}

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection

@section('script')

<script>

$(function(){

    setupPdfDownload('.downloadcreditdurationpdf','data-downloadroutepdf');

    setupPdfDownload('.downloadcreditdurationcsv','data-downloadroutepdf');

});

</script>

@endsection