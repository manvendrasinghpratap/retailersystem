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
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>  
                    <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloadstaffpdf',    
                        'pdfRoute' => route('staff.pdf'),
                        'pdfClass' => 'downloadstaffpdf',
                        'csvId' =>'downloadstaffcsv',    
                        'csvRoute' => route('staff.csv'),
                        'csvClass' => 'downloadstaffcsv',
                        ])                 
                    </div>
                </div>
                <div class="card-body">
                    <form name="cartlistingform" id="cartlistingform" method="GET">
                        <div class="row">
                            <x-text-input name="staff_name" :label="__('translation.staff')"  :value="request('staff_name')" :placeholder="__('translation.search').' '. __('translation.staff')" class="form-control" mainrows="3"/>
                            <x-select-dropdown name="designation_id" label="{{ __('translation.designation') }}" :options="$designation" :selected="request('designation_id')" class="designation" mainrows="2"/>
                            <x-date-input name="hired_date" :label="__('translation.hired_date')" value="{{ request('hired_date') ?? ''}}" class="flatdatepickr  hired_date" data-mindate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-20 years')) )}}" data-maxdate="{{\App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('+10 days')) )}}" mainrows="2"/>
                            <x-select-dropdown name="is_active" label="{{ __('translation.status') }}" :options="\Config::get( 'constants.accountstatus' )" :selected="request('is_active')" class="is_ative form-control" mainrows="2"/>
                            <x-button submitText="Filter" resetText="Reset" url="{{ route(array_key_exists('route',$breadcrumb)?$breadcrumb['route']:'') }}" isbutton="1" iscancel="1" mainrows="2"/>                             
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
                            <th>{{__('translation.staff')}}</th>
                            <th>{{__('translation.email')}}</th>
                            <th>{{__('translation.username')}}</th>
                            <th>{{__('translation.designation')}}</th>
                            <th>{{__('translation.hired_date')}}</th>
                            <th>{{__('translation.status')}} </th>
                            <th>{{__('translation.createdat')}}</th>
                            <th>{{__('translation.action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($i=0)
                        @foreach($userList as $user)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><a data-id="{{ $user->id }}" data-orderid="{{ $user->id }}"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                data-bs-original-title="Click here to Change Password" href="javascript:void(0);"
                                class="changepassword @if (!empty($user->id)) link-danger @endif">{{ $user->username }}</a></td>
                            <td>{{ $user->designation->name }}</td>
                            <td> {{ $user->hire_date }}</td>
                            <td>{{(array_key_exists($user->is_active,$staffstatus))? $staffstatus[$user->is_active]:''}}</td>
                            <td> {{ $user->created_at }}</td>
                            <td>
                                <x-href-input name="edit" label="Edit"  required href="{{ route('staff.edit',['id' => \App\Helpers\Settings::getEncodeCode($user->id)]) }}" />
                                @if(auth()->check() && auth()->id() != optional($user)->id)
                                <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData"  data-deleteid="{{ $user->id }}"  data-routeurl="{{ route('destroy') }}"/> 
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="right user-navigation" style="float:right">{!! $userList->appends(request()->input())->links() !!}</div>
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->

    
@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloadstaffpdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloadstaffcsv', 'data-downloadroutepdf');
    });
</script>
@endsection
