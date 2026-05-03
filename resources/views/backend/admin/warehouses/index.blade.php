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
                <div class="d-inline-block"></div>
            </div>

            <div class="card-body">
                <form method="GET">
                    <div class="row">
                        <x-text-input name="name" label="{{ __('translation.warehouse_name') }}" value="{{request('name')}}" mainrows="3"/>
                        <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="request('status')" mainrows="3" class="accountstatus"/>
                        <div class="col-xl-2 col-md-2">
                            <div class="form-group mb-3">
                                <label class="d-inline-block w-100">&nbsp;</label>
                                <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" value="Filter" class="" />
                                <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" class="" />
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title d-inline-block">
                    {{ $breadcrumb['title'] ?? '' }} {{ __('translation.listing') }}
                </h4>
                <div class="d-inline-block"></div>
            </div>

            <div class="card-body">
                <div class="table-responsive overflowx">

                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('translation.code') }}</th>
                                <th>{{ __('translation.warehouse_name') }}</th>
                                <th>{{ __('translation.manager') }}</th>
                                <th>{{ __('translation.phone') }}</th>
                                <th>{{ __('translation.email') }}</th>
                                <th>{{ __('translation.status') }}</th>
                                <th>{{ __('translation.createdat') }}</th>
                                <th>{{ __('translation.actions') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @if($warehouses->count() > 0)
                                @foreach($warehouses as $warehouse)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $warehouse->warehouse_code }}</td>
                                        <td>{{ $warehouse->name }}</td>
                                        <td>{{ $warehouse->manager_name ?? '-' }}</td>
                                        <td>{{ $warehouse->phone ?? '-' }}</td>
                                        <td>{{ $warehouse->email ?? '-' }}</td>
                                        <td>
                                            @if($warehouse->status == 1)
                                                <span class="badge bg-success">{{ __('translation.active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('translation.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ \App\Helpers\Settings::getFormattedDatetime($warehouse->created_at) }}</td>
                                        <td> 
                                            <x-href-input action="products" name="products" label="{{ __('translation.products') }}" href="{{ route('admin.warehouses.products', \App\Helpers\Settings::getEncodeCode($warehouse->id)) }}"/>  
                                            <x-href-input action="edit" name="edit" label="{{ __('translation.edit') }}" href="{{ route('admin.warehouses.edit', ['id' => \App\Helpers\Settings::getEncodeCode($warehouse->id)]) }}"/>
                                            <x-href-input action="delete" name="delete" label="{{ __('translation.delete') }}" href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($warehouse->id) }}" data-routeurl="{{ route('admin.warehouses.softdelete', $warehouse->id) }}"/>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" class="text-center">{{ __('translation.no_data_found') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                    <div class="right user-navigation">
                        {!! $warehouses->appends(request()->input())->links() !!}
                    </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function () {
    setupPdfDownload('.downloadwarehousepdf', 'data-downloadroutepdf');
    setupPdfDownload('.downloadwarehousecsv', 'data-downloadroutepdf');
});
</script>
@endsection