<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Augodordesigns') }}</title>
	<title> @lang('translation.webname') | @lang('translation.administrationpanel') | @yield('title')</title>
	
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/brand/favicon.png') }}" type="image/x-icon"/>

    <!-- Bootstrap css -->
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.css') }}" rel="stylesheet" />

    <!-- Icons css -->
    <link href="{{ asset('assets/plugins/icons/icons.css') }}" rel="stylesheet">

    <!-- Sidebar css -->
    <link href="{{ asset('assets/plugins/sidebar/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sidemenu.css') }}" rel="stylesheet">

    <!-- Main css -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-dark.css') }}" rel="stylesheet">
    <link id="theme" href="{{ asset('assets/css/colors/color.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/skin-modes.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/animate.css') }}" rel="stylesheet">

</head>
<body class="main-body light-theme">

	 {{ $slot }}
		<!-- Scripts -->
	<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
	<script src="{{ asset('assets/plugins/bootstrap/popper.min.js') }}"></script>
	<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('assets/plugins/ionicons/ionicons.js') }}"></script>
	<script src="{{ asset('assets/plugins/moment/moment.js') }}"></script>
	<script src="{{ asset('assets/plugins/eva-icons/eva-icons.min.js') }}"></script>
	<script src="{{ asset('assets/plugins/rating/jquery.rating-stars.js') }}"></script>
	<script src="{{ asset('assets/plugins/rating/jquery.barrating.js') }}"></script>
	<script src="{{ asset('assets/js/custom.js') }}"></script>

</body>
</html>
