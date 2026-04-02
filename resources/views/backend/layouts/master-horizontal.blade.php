<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <title>
    @lang('translation.webname') |
    {{ auth()->user()->user_type_id == 1 ? __('translation.administrationpanel') : __('translation.adminpanel') }}
    | @yield('title')
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta content="@lang('translation.webname') | @lang('translation.administrationpanel')" name="description" />
  <meta content="@lang('translation.webname')" name="author" />
  <!-- App favicon -->
  <link rel="shortcut icon" href="/favicon.ico">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  @include('backend.layouts.head-css')
</head>
@section('body')
<input type="hidden" name="passwordlength" value="{{Config::get('constants.passwordlength')}}" class="passwordlength" id="passwordlength">
@include('backend.layouts.body', ['fullBg' => true])
@show
<div id="layout-wrapper">
  @include('backend.layouts.administrator.topbar')
  <div class="topnav">
    <div class="container-fluid">
      @if(Auth::user()->user_type_id == 1)
        @include('backend.layouts.administrator.menubar')
      @elseif(Auth::user()->user_type_id == 2)
        @include('backend.layouts.menubar')
      @else
        @include('backend.layouts.othermenubar')
      @endif

    </div>
  </div>

  <!-- ============================================================== -->
  <!-- Start right Content here -->
  <!-- ============================================================== -->
  <div class="main-content">
    <div class="page-content">
      <div class="container-fluid">
        @yield('content')
      </div>
      <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    @include('backend.layouts.footer')
  </div>
  <!-- end main content-->
</div>
<!-- END layout-wrapper -->


<!-- JAVASCRIPT -->
@include('backend.layouts.vendor-scripts')
@include('components.SweetAlert')
@include('components.ajax')
@include('backend.modal.modalpopup')
</body>

</html>