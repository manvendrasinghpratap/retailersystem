<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Havana Bakery')</title>
    @include('backend.pdf.layouts.pdfcss')
  </head>
  <body>
    <div class="report-header">
      <div> @include('backend.pdf.datalogo') </div>
          <h1>{{\Config::get('constants.shopname')}}</h1>
          <p>{{\Config::get('constants.slogan')}}</p>
          <p><strong>{!! (!empty($pdfHeaderdata) && (array_key_exists('heading',$pdfHeaderdata)))? $pdfHeaderdata['heading']:'' !!}</strong> — {{\App\Helpers\Settings::getFormattedDate(date('Y-m-d'))}} </p>
          <p> {{\Config::get('constants.phonenumber')}} | {{\Config::get('constants.hbphone')}} |  {{\Config::get('constants.website')}}</p>
      </div>
    <main>
      @yield('content')
    </main>   
    

    <div class="footer">
         <p>{{\Config::get('constants.slogan')}}</p>
        <p> {{\Config::get('constants.phonenumber')}} | {{\Config::get('constants.hbphone')}} |  {{\Config::get('constants.website')}}</p>
    </div>

  </body>
</html>
