@extends('backend.layouts.master-horizontal')
@section('title')
    @lang('translation.Data_Tables')
@endsection
@section('css')
    <link href="{{ URL::asset('assets/libs/datatables.net-bs4/datatables.net-bs4.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('assets/libs/datatables.net-buttons-bs4/datatables.net-buttons-bs4.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('assets/libs/datatables.net-responsive-bs4/datatables.net-responsive-bs4.min.css') }}" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        .user-navigation svg{ width: 20px; }
        .user-navigation nav, .user-navigation{ width:100%; margin-top: 10px; }
        .user-navigation nav>div:nth-child(1){ display: none !important; }
        .user-navigation .px-4 {
            padding-right: 1rem!important;
            padding-left: 1rem!important;
        }
        .user-navigation nav>div:nth-child(2)>div:nth-child(1){
            float: left;
        }
        .user-navigation nav>div:nth-child(2)>div:nth-child(2){ float: right;}

        @media screen and (max-width: 576px) {
          .user-navigation nav>div:nth-child(2)>div:nth-child(1){ 
            width: 100%; text-align: center;
           }
           .user-navigation nav>div:nth-child(2)>div:nth-child(2){ float: right; width: 100%; text-align: center;}
           .table-container{ float: left; width:100%; overflow-x: auto; }
        }
    </style>
@endsection
@section('content')
    @component('frontend.components.breadcrumb')
        @slot('li_1')
            Users
        @endslot
        @slot('title')
           User Listing
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">User Listing</h4>
                    <p class="card-title-desc">
                    </p>
                </div>
                <div class="card-body">
                    <div class="table-container">
                    <table id="datatable-buttons-" class="table dt-responsive table-bordered table-hover table-striped table-nowrap w-100">
                        <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Avater</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Active Status </th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($i=1)
                        @foreach($userList as $user)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $user->avatar }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <input type="checkbox" id="switch3{{$user->id}}" onchange="statusSwitch(this.checked,{{ $user->id }})" switch="bool"  @if($user->is_active==1) checked @endif/>
                                <label for="switch3{{$user->id}}" data-on-label="Yes" data-off-label="No"></label>
                            </td>
                            <td>
                                <a href="#"><i onclick="deleteData('1',{{ $user->id }})" style="color:darkred" class="fas fa-trash action-btn"></i></a>
                            </td>
                        </tr>
                        @php($i++)
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
        function  statusSwitch(data,id) {
            var selectedStatus = data ? 1:0;
            $.ajax({
                url: '{{ route("user.statusUpdate") }}',
                type: 'POST',
                data: {
                    id: id,
                    status: selectedStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    location.reload(true);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        function  deleteData(data,id) {
                if (confirm("Are you sure you want to proceed?")) {
                    $.ajax({
                        url: '{{ route("user.destroy") }}',
                        type: 'POST',
                        data: {
                            id: id,
                            is_deleted: data,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            location.reload(true);
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                        }
                    });
                } else {
                }
            }
    </script>
    <script src="{{ URL::asset('assets/libs/datatables.net/datatables.net.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/datatables.net-bs4/datatables.net-bs4.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/datatables.net-buttons/datatables.net-buttons.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/datatables.net-buttons-bs4/datatables.net-buttons-bs4.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/datatables.net-responsive/datatables.net-responsive.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/datatables.net-responsive-bs4/datatables.net-responsive-bs4.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('assets/js/app.min.js') }}"></script>
@endsection
