@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title',$breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection
@section('content')
@include('backend.components.breadcrumb')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>  
                    <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'customerspdf',    
                        'pdfRoute' => route('customer.pdf'),
                        'pdfClass' => 'customerspdf',
                        'csvId' =>'customerscsv',    
                        'csvRoute' => route('customer.csv'),
                        'csvClass' => 'customerscsv',
                        ])                 
                    </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : '') }}"> 
                    <div class="row">
                        <x-text-input name="customer_name" :value="request('customer_name')" :placeholder="__('translation.search_customer_name')" class="form-control" mainrows="3"/>
                        <x-text-input name="phone_no" :label="__('translation.phone') .' '. __('translation.number') " value="{{request('phone_no') }}"  id="phone_no" class="onlyinteger phonenumber nocutcopypaste setusername" placeholder="Enter Phone Number" mainrows="3"/>
                        <x-button submitText="Filter" resetText="Reset" url="{{ route($breadcrumb['route']??'') }}" isbutton="1" iscancel="1" mainrows="2"/> 
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive overflowx">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('translation.username') }}</th>
                                <th>{{ __('translation.first_name') }}</th>
                                <th>{{ __('translation.last_name') }}</th>
                                <th>{{ __('translation.phone') .' '. __('translation.number') }}</th>
                                <th>{{__('translation.status')}}</th>
                                <th>{{__('translation.createdat')}}</th>
                                <th>{{__('translation.action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><a data-id="{{ $customer->user->id}}" data-orderid="{{ $customer->user->id }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-original-title="Click here to Change Password" href="javascript:void(0);"
                                    class="changepassword @if (!empty($customer->user->id)) link-danger @endif">{{ $customer->user->username }}</a>
                                    </td>
                                    <td>{{ $customer->customer_suffix .' '. $customer->first_name }}</td>
                                    <td>{{ $customer->last_name }}</td>
                                    <td>{{ $customer->phone_no }}</td>
                                    <td>
                                        <span class="badge bg-{{ $customer->status ? 'success' : 'danger' }}">
                                            {{ $customer->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ \App\Helpers\Settings::getFormattedDatetime($customer->created_at) }}</td>
                                    <td>
                                    <x-href-input name="edit" label="Edit"  required href="{{ route('customer.edit',['id' => \App\Helpers\Settings::getEncodeCode($customer->id)]) }}" />
                                    <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData"  data-deleteid="{{ $customer->id }}"  data-routeurl="{{ route('customer.destroy') }}"/> 
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No customers found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="right user-navigation" style="float:right">{!! $customers->appends(request()->input())->links() !!}</div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.customerspdf', 'data-downloadroutepdf');
       setupPdfDownload('.customerscsv', 'data-downloadroutepdf');
    });
</script>
@endsection
