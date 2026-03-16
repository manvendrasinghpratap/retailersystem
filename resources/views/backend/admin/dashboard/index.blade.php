@extends('backend.layouts.master-horizontal')

@section('title')
    {{Config::get('constants.admin') ||  $breadcrumb['title'] ?? __('translation.dashboard') }}
@endsection

@section('content')
@include('backend.components.breadcrumb')


@endsection
