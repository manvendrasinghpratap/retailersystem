@extends('backend.layouts.master-horizontal')
@section('title')
{{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}}
@endsection
@section('css')
@endsection
@section('content')
@include('backend.components.breadcrumb')
<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">Filter</h4>
                    {{-- <div class="d-inline-block ">
                        <a href="javascript:void(0);" title="Download as PDF" id="downloadpdf"
                            data-downloadroutepdf = "{{ route('downloadsubscriptionpdf') }}" class="downloadpdf"
                            value="Reset"><img width = "16" src="{{ URL::asset('frontend/images/icon-pdf.png') }}" /></a>
                        <a download href="javascript:void(0);" title="Download as CSV" id="downloadproductcsv"
                            data-downloadroutepdf = "{{ route('downloadsubscriptionlistcsv') }}" class="downloadpdf"
                            value="Reset"><img width = "24" src="{{ URL::asset('frontend/images/csv.png') }}" /></a>
                    </div> --}}
                </div>
                <div class="card-body">
                    <form name="cartlistingform" id="cartlistingform" method="GET">
                        <div class="row">
                             <x-select-dropdown name="subscription_id" label="Subscription Plan" :options="$subscriptionDropdown" :selected="request()->get('subscription_id') ?? ''" class="subscription_id"  mainrows='3'/>
                            <x-select-dropdown name="is_active" label="Status" :options="$account_status" :selected="request()->get('is_active') ?? ''" class="is_active accountstatus"  mainrows='2'/>                          
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                	<label class="d-inline-block w-100">&nbsp;</label>
                                     <x-filter-submit-button name="submit" label="Filter" value="Filter" class=""/>
                                     <x-filter-href-button name="reset" href="{{ route(array_key_exists('route',$breadcrumb)?$breadcrumb['route']:'') }}" label="Reset" class=""/>
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
                    <h4 class="card-title d-inline-block">{{array_key_exists('listing',$breadcrumb)?$breadcrumb['listing']:''}}</h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                    <table id="datatable-buttons-" class="table dt-responsive table-bordered table-hover table-striped table-nowrap w-100">
                        <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price @lang('translation.b_ngn')</th>
                            <th>Duration (Month)</th>
                            <th>Status </th>
                            <th>Created At </th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($i=1)
                        @foreach($subscriptionList as $plan)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $plan->name }}</td>
                            <td>{{ $plan->description }}</td>
                            <td>{{ $plan->price }}</td>
                            <td>{{ $plan->duration }}</td>
                            <td>
                                <input type="checkbox" id="switch3{{$plan->id}}" onchange="statusSwitch(this.checked,{{ $plan->id }})" switch="bool"  @if($plan->status=='1') checked @endif/>
                                <label for="switch3{{$plan->id}}" data-on-label="Yes" data-off-label="No"></label>
                            </td>
                            <td>{{ App\Helpers\Settings::getFormattedDatetime($plan->created_at)}}</td>
                            <td>
                                <x-href-input name="edit" label="Edit"  required href="{{ route('subscription.edit',['id' => \App\Helpers\Settings::getEncodeCode($plan->id)]) }}" />
                                <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData"  data-deleteid="{{ $plan->id }}"  data-routeurl="{{ route('subscription.destroy') }}"/> 
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="right user-navigation right">{!! $subscriptionList->appends(request()->input())->links() !!}</div>
                </div>
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection
@section('script')
@endsection
