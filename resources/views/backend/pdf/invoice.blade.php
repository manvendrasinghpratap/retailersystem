<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Invoice : @lang('translation.webname')</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 13px;
      color: #1f2937;
      margin: 0;
      background: #fff;
    }

    @page {
      margin: 10mm;
      @bottom-center {
        content: "Page " counter(page) " of " counter(pages);
        font-size: 11px;
        color: #555;
      }
    }

    h2, h4 { margin: 0; }
    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 6px; vertical-align: top; }
    .header td { vertical-align: top; }

    .watermark {
      position: fixed;
      top: 42%;
      left: 18%;
      opacity: 0.15; /* darker watermark */
      font-size: 80px;
      transform: rotate(-30deg);
      color: #777;
      z-index: -1;
    }

    .meta td { font-size: 12px; }
    .section-title { font-weight: bold; margin-bottom: 4px; }
    .box { border: 1px solid #eee; border-radius: 6px; padding: 5px; }
    .totals td { padding: 4px; }
    .totals strong { font-weight: bold; }
  </style>
</head>
  <body>
      <div class="watermark">{{\Config::get('constants.shopname')}}</div>
      <table class="header">
          <tr>
                <td style="width:60%; vertical-align: middle;">
                  <!-- Logo and business name -->
                  <div style="display:flex; align-items:center;">
                    @include('backend.pdf.datalogo')        
                    <div>
                      <h3 style="margin:0;">{{\Config::get('constants.shopname')}}</h3>
                      <div style="font-size:10px; color:#7b8098;">{{\Config::get('constants.slogan')}}</div>
                      <div style="font-size:12px; color:#7b8098;">{{\Config::get('constants.address')}}</div>
                    </div>
                  </div>
                </td>
                <td style="text-align:right; float:right;">
                  <h3 style="color:#5b6bff; margin:0;">Invoice</h3>
                  <table class="meta" style="margin-top:5px; float:right; text-align:right;">
                    <tr><td>Invoice #:<strong>   #INV-{{date('Y',strtotime($order->ordered_at))}}-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong></td></tr>
                    <tr><td>Date: <span>{{\App\Helpers\Settings::getFormattedDate($order->ordered_at)}}</span></td></tr>
                  </table>
                </td>
          </tr>
      </table>


      <table style="margin-top:50px;">
        <tr>
          <td style="width:50%;">
            <div class="section-title">Bill To</div>
            <div>
                @if(!empty($order->customer->customer_name))
                    {{$order->customer->customer_name}}<br>
                @endif
                @if(!empty($order->customer->business_name))
                  {{$order->customer->business_name}}<br>
                @endif
                @if(!empty($order->customer->current_location))
                  {{$order->customer->current_location}}<br>
                @endif
                 @if(!empty($order->customer->near_by))
                  {{$order->customer->near_by}}<br>
                @endif
                @if(!empty($order->customer->email))
                    Email: {{$order->customer->email}}<br>
                @endif

                @if(!empty($order->customer->phone_no))
                  Phone: {{$order->customer->phone_no}}
                @endif
                @if(!empty($order->customer->alternate_phone_no))
                  , {{$order->customer->alternate_phone_no}}<br>
                @endif
            </div>
          </td>
          <td style="width:50%; text-align:right;">
            <div class="section-title">From</div>
            <div>
            @lang('translation.webname')<br>
              {{\Config::get('constants.emailcontact')}}<br>
              Phone: {{\Config::get('constants.phonenumber')}}<br>
                     {{\Config::get('constants.hbphone')}}
            </div>
          </td>
        </tr>
      </table>
      <table style="margin-top:10px; border:1px solid #ddd; border-radius:6px;">
        <tr>
            <td style="width:70%; padding:8px;">
              <strong>Payment Method:</strong> 
                  @foreach($order->orderpaymentdetails as $payment)
                      @if($payment->payment_cash > 0)
                          @lang('translation.cash')
                      @elseif($payment->payment_pos > 0)
                          @lang('translation.pos')
                      @elseif($payment->payment_transfer > 0)
                        @lang('translation.bank_transfer')
                          
                      @endif
                      @if(!$loop->last), @endif
                  @endforeach
            </td>
            <td style="text-align:right; padding:8px;">
            <strong>Status:</strong>
            <span style="color:#10b981; font-weight:bold;">Paid</span>
          </td>
        </tr>
      </table>

      <table style="margin-top:10px;">
        <thead>
          <tr style="background:#f5f6ff;">
            <th style="text-align:left;">@lang('translation.product')</th>
            <th style="text-align:right;">@lang('translation.quantity')</th>
            <th style="text-align:right;"> @lang('translation.ngn') @lang('translation.price')</th>
            <th style="text-align:right;">@lang('translation.total') @lang('translation.ngn')</th>
          </tr>
        </thead>
        <tbody>
          @php($subquantity = 0 )
          @foreach($order->orderdetails as $detail)
                <tr>
                    <td>{{ $detail->menus->title ?? 'N/A' }}</td>
                    <td style="text-align:right;">{{ $detail->quantity }}</td>
                    <td style="text-align:right;"> @lang('translation.ngn') {{ \App\Helpers\Settings::getcustomnumberformat($detail->price) }}</td>
                    <td style="text-align:right;"> @lang('translation.ngn') {{ \App\Helpers\Settings::getcustomnumberformat($detail->quantity * $detail->price ) }}</td>
                </tr>
                {{ $subquantity += $detail->quantity}}
            @endforeach
        </tbody>
      </table>

      <table style="margin-top:10px;">
        <tr>
          <td style="width:60%;"></td>
          <td style="width:40%;">
            <table class="totals box" style="width:100%;">
              <tr><td>@lang('translation.subquantity')</td><td style="text-align:right;">{{$subquantity}}</td></tr>
              @if($order->total_free_quantity > 0)
              <tr><td>@lang('translation.free_quantity')</td><td style="text-align:right;">{{$order->total_free_quantity}}</td></tr>
              @endif
              <tr><td><strong>@lang('translation.total') @lang('translation.quantity')</strong></td><td style="text-align:right;"><strong>{{$order->total_quantity}}</strong></td></tr>
              <tr><td><strong>@lang('translation.total_amount')(@lang('translation.ngn'))</strong></td><td style="text-align:right;"><strong>@lang('translation.ngn') {{ \App\Helpers\Settings::getcustomnumberformat($order->total_amount)}}</strong></td></tr>
            </table>
          </td>
        </tr>
      </table>
      @if(!empty($order->orderdelivery))
        <div style="margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 10px;">
            <h4 style="margin-bottom: 6px; color:#5b6bff;">@lang('translation.delivery_instruction')</h4>
            <div class="box" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                <table style="width:100%; font-size: 13px;">
                    <tr>
                        <td style="width:50%; vertical-align: top;">
                            <strong>@lang('translation.delivery_option'):</strong> 
                            {{ $order->orderdelivery->delivery_option ?? '-' }}
                        </td>
                        <td style="width:50%; vertical-align: top;">
                            <strong>@lang('translation.delivery_location'):</strong> 
                            {{ $order->orderdelivery->delivery_location ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>@lang('translation.near_by'):</strong> 
                            {{ $order->orderdelivery->customer?->near_by ?? '-' }}
                        </td>
                        <td>
                            <strong>@lang('translation.processed_delivery_staff'):</strong> 
                            {{ $order->orderdelivery->staff?->name ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>@lang('translation.delivery_date'):</strong> 
                            {{ $order->orderdelivery->delivery_date 
                                ??'-' 
                            }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
      @endif

      {{-- Processed / Received Section --}}
      <div style="margin-top: 30px; font-size: 13px;">
          <table style="width:100%; border-collapse: collapse;">
              <tr>
                  <td style="width:60%; vertical-align: top;">
                      <strong>@lang('translation.processed_delivery_staff'):</strong>
                      {{ $order->orderdelivery?->staff?->name ?? 'Ebere Ogbonnah' }}
                  </td>
                  <td style="width:40%; text-align:right; vertical-align: top;">
                      <strong>Received by:</strong>
                      ____________________________
                  </td>
              </tr>
          </table>
      </div>

  <div class="footer" style="margin-top: 50px; text-align: center; font-family: 'Nunito', sans-serif; color: #6B2E2E; border-top: 2px solid #FFC0CB; padding-top: 15px;">
    <p style="margin: 5px 0; font-size: 14px;">
        Thank you for choosing 
        <strong style="color:#FF69B4;">{{ \Config::get('constants.shopname') }}</strong>! 
        We hope our treats made your day sweeter.
    </p>
    
    <p style="margin: 5px 0; font-size: 13px;">
         {{\Config::get('constants.address')}} &nbsp; | &nbsp; {{\Config::get('constants.phonenumber')}} | {{\Config::get('constants.hbphone')}}<br><br>
        <a href="{{\Config::get('constants.emailcontact')}}" style="color:#8B4513; text-decoration:none;">{{\Config::get('app.url')}}</a>
    </p>

    <p style="margin-top:8px; font-size: 13px; color:#A0522D;">
        <em>Freshly baked with love every day!</em>
    </p>
</div>
  </body>
</html>
