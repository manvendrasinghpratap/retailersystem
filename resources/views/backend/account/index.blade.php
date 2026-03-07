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
                            data-downloadroutepdf = "{{ route('downloadHotelpdf') }}" class="downloadpdf"
                            value="Reset"><img width = "16" src="{{ URL::asset('frontend/images/icon-pdf.png') }}" /></a>
                        <a download href="javascript:void(0);" title="Download as CSV" id="downloadproductcsv"
                            data-downloadroutepdf = "{{ route('downloadhotellistcsv') }}" class="downloadpdf"
                            value="Reset"><img width = "24" src="{{ URL::asset('frontend/images/csv.png') }}" /></a>
                    </div> --}}
                </div>
                <div class="card-body">
                    <form name="cartlistingform" id="cartlistingform" method="GET">
                        <div class="row">
                            <x-select-dropdown name="subscription_id" label="Subscription Plan" :options="$subscriptionPlan" :selected="request()->get('subscription_id') ?? ''" class="subscription_id"  mainrows='2'/>
                            <x-text-input name="accountname" label="Account Name" value="{{ request()->get('accountname') ?? '' }}" class="" mainrows='3'/>
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
                    <h4 class="card-title d-inline-block">{{array_key_exists('title',$breadcrumb)? $breadcrumb['title'] :''}} @lang('translation.listing')</h4>
                </div>
                <div class="card-body">
                    <div class="table-container overflowx">
                    <table id="datatable-buttons-" class="table dt-responsive table-bordered table-hover table-striped table-nowrap w-100">
                        <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Username</th>
                            <th>Subscription Plan</th>
                            <th>Sub.Start Date</th>
                            <th>Sub.Expire Date</th>
                            <th>Payment</th>
                            <th>Discount @lang('translation.b_ngn')</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($i=1)
                        @foreach($accountList as $account)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $account->name }}</td>
                            <td>{{ $account->user->email }}</td>
                            <td>{{ $account->user->mobile_no }}</td>
                            <td>
                                <x-href-input name="edit" label="{{ $account->user->username }}"  href="javascript:void(0);" class="btn- btn-secondary- error changepassword" data-id="{{ $account->user_id }}" data-orderid="{{ $account->user_id }}"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Click here to Change Password" />
                            </td>  
                            <td>
                            @if(!empty($account->user->subscriptionplan))
                            <x-href-input name="edit" label="{{ $account->user->subscriptionplan->name ?? '' }}"  href="javascript:void(0)" class="btn btn-primary" />
                            @else
                            <x-href-input name="edit" label="{{ __('translation.subscribenow') }}"  href="{{ route('subscribe',\App\Helpers\Settings::getEncodeCode($account->id)) }}" class="btn btn-danger" />
                            @endif
                            </td>
                            <td>{{ (!empty($account->subscriptiondetails->start_date)) ? App\Helpers\Settings::getFormattedDate($account->subscriptiondetails->start_date):''  }}</td>
                            <td>{{ (!empty($account->subscriptiondetails->end_date)) ? App\Helpers\Settings::getFormattedDate($account->subscriptiondetails->end_date):''  }}</td>
                            <td><button data-subscriptionid = "{{ (!empty($account->subscriptiondetails) && $account->subscriptiondetails->is_expired == 0)? $account->subscriptiondetails->id : '0' }}" class="btn {{ (!empty($account->subscriptiondetails) && $account->subscriptiondetails->is_expired == 0)? 'btn-primary accountsubscriptionpaymentdetails':'btn-danger accountsubscriptionpaymentdetailsnotactive' }}">{{ (!empty($account->subscriptiondetails) && $account->subscriptiondetails->is_expired == 0)? 'Paid': 'In-active' }}</button></td>
                            <td>{{ (!empty($account->subscriptiondetails) && !empty($account->subscriptiondetails->discount) )? $account->subscriptiondetails->discount: @$account->subscriptiondetails->discount }}</td>                            
                            <td><button  class="btn {{ (array_key_exists($account->status,$account_status) && $account->status == 1)? 'btn-primary':'btn-danger' }}">{{ (array_key_exists($account->status,$account_status))? $account_status[$account->status]:'' }}</button></td>
                            <td>{{ App\Helpers\Settings::getFormattedDatetime($account->created_at)}}</td>
                            <td>
                                <x-href-input name="edit" label="Edit"  required href="{{ route('account.edit',['id' => \App\Helpers\Settings::getEncodeCode($account->id)]) }}" />
                                <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData"  data-deleteid="{{ $account->id }}"  data-routeurl="{{ route('account.destroy') }}"/>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="right user-navigation right">{!! $accountList->appends(request()->input())->links() !!}</div>
                </div>
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection
@section('script')
@endsection
