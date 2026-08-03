<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>@lang('translation.webname') | @yield('title')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta content="@lang('translation.webname') | @lang('translation.administrationpanel')" name="description" />
  <meta content="@lang('translation.webname')" name="author" />
  <!-- App favicon -->
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/icons/apple-touch-icon.png')}}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/icons/favicon-32x32.png')}}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/icons/favicon-16x16.png')}}">
  <!-- <link rel="manifest" href="assets/images/icons/site.webmanifest"> -->
  <link rel="mask-icon" href="assets/images/icons/safari-pinned-tab.svg" color="#666666">
  @include('backend.pdf.layouts.downloadpdfcss')
</head>

<body>
  <div class="report-header">
    <!-- @if(isset(auth()->user()->store) && auth()->user()->store->logo)
      <img src="{{auth()->user()->store->logo}}" alt="{{ auth()->user()->store->name ?? Config::get('constants.shop_name') }}" width="70" height="70" style="float:left;margin-right:10px" />
    @else
      <div> @include('backend.pdf.datalogo') </div>
    @endif -->
    <h1>{{ auth()->user()->store->name ?? Config::get('constants.shop_name') }}</h1>
    <!-- <p>{{\Config::get('constants.slogan')}}</p> -->
    <p><strong>{!! (!empty($pdfHeaderdata) && (array_key_exists('heading', $pdfHeaderdata))) ? $pdfHeaderdata['heading'] : '' !!}</strong> — {{\App\Helpers\Settings::getFormattedDate(date('Y-m-d'))}} </p>
    <p> {{ auth()->user()->store->phone ?? '' }} @if(!empty(auth()->user()->store->alternate_phone)) || {{ auth()->user()->store->alternate_phone }} @endif</p>
    @if(!empty(auth()->user()->store->website))
    <p>{{ auth()->user()->store->website }}</p> @endif
    @if(!empty(auth()->user()->store->address))
    <p>{{ auth()->user()->store->address }}</p> @endif
  </div>
  <main>
    @yield('content')
  </main>
  <div class="footer">
    <p> {{ auth()->user()->store->phone ?? '' }} @if(!empty(auth()->user()->store->alternate_phone)) || {{ auth()->user()->store->alternate_phone }} @endif </p>
    @if(!empty(auth()->user()->store->website))
    <p>{{ auth()->user()->store->website }}</p> @endif
  </div>

</body>

</html>