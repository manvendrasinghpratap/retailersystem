<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8" />
        <title> @yield('title') | Havana Worlds | Admin Panel </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta content="Havana Worlds | Admin Panel" name="description" />
        <meta content="Havana Worlds | Admin Panel" name="author" />
        <!-- App favicon -->
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
        @include('backend.layouts.head-css')
  </head>

    @yield('body')

    @yield('content')

    @include('backend.layouts.vendor-scripts')
    </body>
</html>
