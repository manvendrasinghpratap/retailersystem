@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}}
@endsection
@section('css')
@endsection
@section('content')
@include('backend.components.breadcrumb')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title d-inline-block">@lang('translation.filter')</h4>
            </div>
            <div class="card-body">
                <form name="cartlistingform" id="cartlistingform" method="GET">
                    <div class="row">
                        <x-select-dropdown name="designation_id" label="Designation" :options="$designations" :selected="request()->get('designation_id') ?? ''" class="designation" mainrows='3' />
                        <x-select-dropdown name="route_id" label="Route" :options="$routes" :selected="request()->get('route_id') ?? ''" class="routedropdown" mainrows='2' />
                        <div class="col-xl-2 col-md-2">
                            <div class="form-group mb-3">
                                <label class="d-inline-block w-100">&nbsp;</label>
                                <x-filter-submit-button name="submit" label="Filter" value="Filter" class="" />
                                <x-filter-href-button name="reset" href="{{ isset($breadcrumb['reset_route']) ? route($breadcrumb['reset_route']) : '#' }}" label="Reset" class="" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title d-inline-block">{{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}}</h4>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table id="datatable-buttons-" class="table dt-responsive table-bordered table-hover table-striped table-nowrap w-100">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Route Name</th>
                                <th>Designation Name</th>
                                <th>Status</th>
                                <th>Created At </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($i = 1)
                            @foreach($accessControlList as $acl)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $acl->route->name }}</td>
                                    <td>{{ $acl->designation->name }}</td>
                                    <td><input type="checkbox" class="acl-toggle" data-designationid="{{ $acl->designation_id }}" data-routeid="{{ $acl->route_id }}" {{ $acl->is_allowed ? 'checked' : '' }} data-routeurl="{{ route('administrator.acl.update') }}"></td>
                                    <td>{{ App\Helpers\Settings::getFormattedDatetime($acl->created_at)}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="right user-navigation right">{!! $accessControlList->appends(request()->input())->links() !!}</div>
                </div>
            </div>
        </div>
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection
@section('script')
@endsection