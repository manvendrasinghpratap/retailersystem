@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
<table class="table table-striped align-middle">
    <thead>
        <tr>
            <th>#</th>
            <th>{{__('translation.staff_name')}}</th>
            <th>{{__('translation.email')}}</th>
            <th>{{__('translation.username')}}</th>
            <th>{{__('translation.designation')}}</th>
            <th>{{__('translation.hired_date')}}</th>
            <th>{{__('translation.status')}} </th>
            <th>{{__('translation.createdat')}}</th>
        </tr>
    </thead>

    <tbody>
        @php($i = 0)
        @foreach($userList as $user)
            <tr>
                <td>{{ ++$i }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><a data-id="{{ $user->id }}" data-orderid="{{ $user->id }}" data-routeurl="{{ route('admin.staff.updatepassword') }}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Click here to Change Password" href="javascript:void(0);" class="changepassword @if (!empty($user->id)) link-danger @endif">{{ $user->username }}</a></td>
                <td>{{ $user->designation->name }}</td>
                <td> {{ $user->detail->hire_date }}</td>
                <td>{{(array_key_exists($user->is_active, $staffstatus)) ? $staffstatus[$user->is_active] : ''}}</td>
                <td> {{ $user->created_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection