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
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/icons/apple-touch-icon.png')}}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/icons/favicon-32x32.png')}}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/icons/favicon-16x16.png')}}">
  <!-- <link rel="manifest" href="assets/images/icons/site.webmanifest"> -->
  <link rel="mask-icon" href="assets/images/icons/safari-pinned-tab.svg" color="#666666">
  <!-- <link rel="shortcut icon" href="assets/images/icons/favicon.ico"> -->
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  @include('backend.layouts.head-css')
</head>
@section('body')
<input type="hidden" name="passwordlength" value="{{Config::get('constants.passwordlength')}}" class="passwordlength" id="passwordlength">
@include('backend.layouts.loader')
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
<script>
  feather.replace();
  $(function () {
    window.sessionManager = new SessionManager(window.sessionConfig);
  });
  window.sessionConfig = {
    timeout: {{ account_setting('general.session_timeout', 5) }},
    warning: {{ account_setting('general.warning_before', 60) }},
    keepAlive: {{ account_setting('general.keep_alive', 1) }},
    csrf: "{{ csrf_token() }}",
    logoutUrl: "{{ route('logout') }}",
    keepAliveUrl: "{{ route('admin.keepalive') }}"
  };

</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-customer-popover]').forEach(function (element) {
      let hideTimeout;

      const popover = new bootstrap.Popover(element, {
        trigger: 'manual',
        html: true,
        placement: 'right',
        container: 'body'
      });

      element.addEventListener('mouseenter', function () {
        clearTimeout(hideTimeout);
        popover.show();

        // Keep popover open when hovering directly over the popover box itself
        const popoverEl = document.getElementById(element.getAttribute('aria-describedby'));
        if (popoverEl) {
          popoverEl.addEventListener('mouseenter', function () {
            clearTimeout(hideTimeout);
          });
          popoverEl.addEventListener('mouseleave', function () {
            hideTimeout = setTimeout(() => popover.hide(), 200);
          });
        }
      });

      element.addEventListener('mouseleave', function () {
        hideTimeout = setTimeout(function () {
          if (!document.querySelector('.popover:hover')) {
            popover.hide();
          }
        }, 200);
      });
    });

  });
</script>

</html>