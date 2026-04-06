<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <title>
    @lang('translation.webname') | @yield('title')
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta content="@lang('translation.webname') | @lang('translation.administrationpanel')" name="description" />
  <meta content="@lang('translation.webname')" name="author" />
  <!-- App favicon -->
  <!-- <link rel="shortcut icon" href="/favicon.ico"> -->
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/icons/favicon-16x16.png')}}">
  <!-- <link rel="manifest" href="assets/images/icons/site.webmanifest"> -->
  <link rel="mask-icon" href="assets/images/icons/safari-pinned-tab.svg" color="#666666">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  @include('backend.layouts.head-css')
</head>
@section('body')
@include('backend.layouts.body', ['fullBg' => true])
@show
<div id="layout-wrapper">
  <div class="main-content-">
    <div class="container-fluid">
      @yield('content')
    </div>
    @include('backend.layouts.footer')
  </div>
</div>
</body>

</html>