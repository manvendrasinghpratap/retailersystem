<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <!--<title> @yield('title') | Havana Bakery Shop | Admin Panel </title>-->
  <title> @lang('translation.webname') | @lang('translation.administrationpanel') | @yield('title')</title>
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
@include('backend.layouts.body')
@show
<div id="layout-wrapper">
  @if(Auth::user()->user_type == 1)
    @include('backend.layouts.administrator.topbar')
  @else
    @include('backend.layouts.topbar')  
  @endif
  <div class="topnav">
    <div class="container-fluid">
      @if(Auth::user()->user_type == 1)
            @include('backend.layouts.administrator.menubar')  
     @else
            @include('backend.layouts.menubar')  
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

<!-- Right Sidebar -->
@include('backend.layouts.right-sidebar')
<!-- /Right-bar -->

<!-- JAVASCRIPT -->
@include('backend.layouts.vendor-scripts')
@include('components.SweetAlert')
@include('components.ajax')
@include('backend.modal.modalpopup')
</body>

</html>