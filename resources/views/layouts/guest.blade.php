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
	@php($cssRefresh = Config::get('app.css_refresh'))
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/icons/apple-touch-icon.png')}}">
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/icons/favicon-32x32.png')}}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/icons/favicon-16x16.png')}}">
	<link rel="mask-icon" href="assets/images/icons/safari-pinned-tab.svg" color="#666666">
	<meta name="apple-mobile-web-app-title" content="Molla">
	<meta name="application-name" content="{{ config('app.name') }}">
	<meta name="msapplication-TileColor" content="#cc9966">
	<meta name="theme-color" content="#ffffff">
	<link rel="preconnect" href="https://cdnjs.cloudflare.com">
	<link rel="preconnect" href="https://cdn.jsdelivr.net">

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
	<!-- <script src="{{ asset('assets/plugins/ionicons/ionicons.js') }}"></script> -->
	<script src="{{ asset('assets/plugins/moment/moment.js') }}"></script>
	<script src="{{ asset('assets/plugins/eva-icons/eva-icons.min.js') }}"></script>
	<script src="{{ asset('assets/plugins/rating/jquery.rating-stars.js') }}"></script>
	<script src="{{ asset('assets/plugins/rating/jquery.barrating.js') }}"></script>
	<script src="{{ asset('assets/js/custom.js') }}"></script>
	@if(session('error'))
		<script>
			@if (session('error'))
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: "{{ session('error') }}",
					confirmButtonColor: '#dc3545'
				});
			@endif
		</script>
	@endif
</body>
<script>
	$(document).on('click', '.toggle-password', function (e) {

		e.preventDefault();

		let input = $('#' + $(this).data('target'));
		let icon = $(this).find('i');
		let library = $(this).data('library');

		input.attr(
			'type',
			input.attr('type') === 'password' ? 'text' : 'password'
		);

		switch (library) {

			case 'bootstrap':
				icon.toggleClass('bi-eye bi-eye-slash');
				break;

			case 'fontawesome':
				icon.toggleClass('fa-eye fa-eye-slash');
				break;

			default:
				icon.toggleClass('la-eye la-eye-slash');
				break;
		}

	});
</script>

</html>