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
      <div> @include('backend.pdf.datalogo') </div>
          <h1>{{\Config::get('constants.shopname')}}</h1>
          <!-- <p>{{\Config::get('constants.slogan')}}</p> -->
          <p><strong>{!! (!empty($pdfHeaderdata) && (array_key_exists('heading',$pdfHeaderdata)))? $pdfHeaderdata['heading']:'' !!}</strong> — {{\App\Helpers\Settings::getFormattedDate(date('Y-m-d'))}} </p>
          <p> {{\Config::get('constants.phonenumber')}} |  {{\Config::get('constants.website')}}</p>
      </div>
    <main>
      @yield('content')
    </main>   
    <div class="footer">
         <!-- <p>{{\Config::get('constants.slogan')}}</p> -->
        <p> {{\Config::get('constants.phonenumber')}} | {{\Config::get('constants.website')}}</p>
    </div>

  </body>
</html>
