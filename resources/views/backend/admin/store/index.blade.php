@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? '' }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    {{-- FILTER SECTION --}}
    {{------------------------
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                    <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'showPdf'=> false,
                        'pdfId' => 'downloadstorepdf',
                        'pdfRoute' => route('admin.stores.exportPdf'),
                        'pdfClass' => 'downloadstorepdf',
                        'showCsv' => false,
                        'csvId' => 'downloadstorecsv',
                        'csvRoute' => route('admin.stores.exportCsv'),
                        'csvClass' => 'downloadstorecsv',
                        ])
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="name" label="Store Name" value="{{ request('name') }}" mainrows="3" />
                            <x-text-input name="code" label="Store Code" value="{{ request('code') }}" mainrows="3" />
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="request('status')" mainrows="2" class="accountstatus" />
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" />
                                    <x-filter-href-button name="reset" href="{{ route($breadcrumb['route2']) }}" label="Reset" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    ------------------------}}

    {{-- LISTING SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ $breadcrumb['title'] ?? '' }} {{-- __('translation.listing') --}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.storename') }}</th>
                                    <th>{{ __('translation.storecode') }}</th>
                                    <th>{{ __('translation.logo') }}</th>
                                    <th>{{ __('translation.manager') }}</th>
                                    <th>{{ __('translation.email') }}</th>
                                    <th>{{ __('translation.phone') }}</th>
                                    <th>{{ __('translation.address') }}</th>
                                    <!-- <th>{{ __('translation.status') }}</th>-->
                                    <!--<th>{{ __('translation.createdat') }}</th>-->
                                    <th>{{ __('translation.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stores as $store)
                                    <tr>
                                        <td>{{ $stores->firstItem() + $loop->index }}</td>
                                        <td>{{ $store->name }}</td>
                                        <td>{{ $store->code }}</td>
                                        <td>
                                            @if($store->logo)
                                                <img src="{{ $store->logo }}" alt="" width="50" height="50">
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>
                                        <td>{{ $store->manager->name ?? '-' }}</td>
                                        <td>{{ $store->email }}</td>
                                        <td>{{ $store->phone }}</td>
                                        <td>{{ $store->address }}</td>
                                        <!-- <td>@if($store->status) <span class="badge bg-success">{{ __('translation.active') }}</span>@else<span class="badge bg-danger">{{ __('translation.inactive') }}</span>@endif</td> -->
                                        <!-- <td>{{ $store->created_at?->format(config('constants.dateformat.slashdmyonly')) }}</td> -->
                                        <td>
                                            <x-href-input name="edit" label="Edit" href="{{ route('admin.stores.edit', \App\Helpers\Settings::getEncodeCode($store->id)) }}" />
                                            <!-- <x-deletehref-input name="DeleteButton" label="Delete" href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($store->id) }}" data-routeurl="{{ route('admin.stores.soft.delete', $store->id) }}" /> -->
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">{{ __('translation.no_stores_available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- PAGINATION --}}
                    @if($stores->count())
                        <div class="right user-navigation">
                            {!! $stores->appends(request()->input())->links() !!}
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

            setupPdfDownload(
                '.downloadstorepdf',
                'data-downloadroutepdf'
            );

            setupPdfDownload(
                '.downloadstorecsv',
                'data-downloadroutepdf'
            );

        });

    </script>

@endsection