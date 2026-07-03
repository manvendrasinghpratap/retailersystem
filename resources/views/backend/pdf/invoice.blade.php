<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ $sale->store->name ?? Config::get('constants.shop_name') }} || {{ __('translation.invoice') }} </title>
  @include('backend.pdf.layouts.pdfcss')
</head>

<body>
  <div class="watermark">{{ $sale->store->name ?? Config::get('constants.shop_name') }}</div>
  <!-- HEADER -->
  <table class="header" width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <!-- Company Details -->
      <td width="60%" style="vertical-align:top;">
        @if($sale->store->logo)
          <img src="{{$sale->store->logo}}" alt="{{ $sale->store->name ?? Config::get('constants.shop_name') }}" width="70" height="70" style="float:left;margin-right:10px" />
        @endif
        <div style="font-size:22px; font-weight:bold; color:#2563eb; margin-bottom:5px; float:left; margin-right:10px;">{{ $sale->store->name ?? Config::get('constants.shop_name') }}</div>
        <div style="font-size:11px; color:#555; line-height:13px;"><strong>{{ __('translation.address') }}:</strong>{{ $sale->store->address ?? '' }}<br><strong>{{ __('translation.phone') }}:</strong>{{ $sale->store->phone ?? '' }} @if(!empty($sale->store->alternate_phone)) || {{ $sale->store->alternate_phone }} @endif<br>
          @if(!empty($sale->store->email))<strong>{{ __('translation.email') }}:</strong>{{ $sale->store->email }}<br> @endif

          @if(!empty($sale->store->website)) <strong>{{ __('translation.website') }}:</strong>{{ $sale->store->website }} @endif
        </div>
      </td>
      <!-- Invoice Details -->
      <td width="40%" class="text-right" style="vertical-align:top;">
        <div style="font-size:28px; font-weight:bold; color:#111827; margin-bottom:10px;">{{ __('translation.invoice') }}</div>
        <table style="font-size:11px; margin-left:auto;">
          <tr>
            <td style="padding:2px 8px;"><strong>{{ __('translation.invoice_no') }}</strong></td>
            <td style="padding:2px 0;">: {{ $sale->invoice_no }}</td>
          </tr>
          <tr>
            <td style="padding:2px 8px;"><strong>{{ __('translation.date') }}</strong></td>
            <td style="padding:2px 0;">: {{ \Carbon\Carbon::parse($sale->created_at ?? now())->format(Config::get('constants.dateformat.showdate')) }}</td>
          </tr>
          <tr>
            <td style="padding:2px 8px;"><strong>{{ __('translation.time') }}</strong></td>
            <td style="padding:2px 0;">: {{ \Carbon\Carbon::parse($sale->created_at ?? now())->format(Config::get('constants.dateformat.showtimeAMAPM')) }}
            </td>
          </tr>
          @if(!empty($sale->user->name))
            <tr>
              <td style="padding:2px 8px;"><strong>{{ __('translation.cashier') }}</strong></td>
              <td style="padding:2px 0;">: {{ $sale->user->name }}</td>
            </tr>
          @endif
        </table>
      </td>
    </tr>
  </table>
  <table class="section" width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <!-- Customer Information -->
      <td width="55%" style="vertical-align:top; padding-right:20px;">
        <div style="font-size:13px;font-weight:bold;color:#2563eb;margin-bottom:8px;">{{ __('translation.customer_information') }}</div>
        <table width="100%" style="font-size:11px; line-height:3px;">
          <tr>
            <td width="90"><strong>{{ __('translation.customer') }}</strong></td>
            <td>: {{ $sale->customer->name ?? __('translation.walk_in_customer') }}</td>
          </tr>
          @if(!empty($sale->customer?->phone))
            <tr>
              <td><strong>{{ __('translation.phone') }}</strong></td>
              <td>: {{ $sale->customer->phone }}</td>
            </tr>
          @endif
          @if(!empty($sale->customer?->email))
            <tr>
              <td><strong>{{ __('translation.email') }}</strong></td>
              <td>: {{ $sale->customer->email }}</td>
            </tr>
          @endif
          @if(!empty($sale->customer?->address))
            <tr>
              <td style="vertical-align:top;"><strong>{{ __('translation.address') }}</strong></td>
              <td>: <div style="display:inline-block;max-width:220px;">{{ $sale->customer->address }}</div>
              </td>
            </tr>
          @endif
        </table>
      </td>
      <!-- Payment Information -->
      <td width="45%" style="vertical-align:top;">
        <div style="font-size:13px;font-weight:bold;color:#2563eb;margin-bottom:8px;">
          {{ __('translation.payment_information') }}
        </div>
        <table width="100%" style="font-size:11px; line-height:3px;">
          <tr>
            <td width="45%"><strong>{{ __('translation.payment_type') }}</strong></td>
            <td class="text-right">{{ $sale->customerPaymentType->name ?? App\Helpers\Settings::getDataUcfirst($sale->payment_type) }}</td>
          </tr>
          <tr>
            <td><strong>{{ __('translation.payment_status') }}</strong></td>
            <td class="text-right">
              @if($sale->payment_status == 'paid')
                <span style="color:#16a34a;font-weight:bold;">
                  {{ App\Helpers\Settings::getDataUcfirst($sale->payment_status) }}
                </span>
              @elseif($sale->payment_status == 'partial')
                <span style="color:#d97706;font-weight:bold;">
                  {{ App\Helpers\Settings::getDataUcfirst($sale->payment_status) }}
                </span>
              @else
                <span style="color:#dc2626;font-weight:bold;">
                  {{ App\Helpers\Settings::getDataUcfirst($sale->payment_status) }}
                </span>
              @endif
            </td>
          </tr>
        </table>
      </td>

    </tr>
  </table>

  <!-- ITEMS -->
  <table class="items" style="margin-top:10px;">
    <thead>
      <tr>
        <th class="text-left">#</th>
        <th class="text-left">{{ __('translation.product') }}</th>
        <th class="text-left">{{ __('translation.description') }}</th>
        <th class="text-center">{{ __('translation.quantity') }}</th>
        <th class="text-right">{{ __('translation.price') }}</th>
        <th class="text-right">{{ __('translation.total') }}</th>
      </tr>
    </thead>
    <tbody>
      @foreach($sale->items ?? [] as $index => $item)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $item->product->name ?? 'Product' }}</td>
          <td>{{ $item->product->description ?? '-' }}</td>
          <td class="text-center">{{ $item->quantity }}</td>
          <td class="text-right">{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($item->price) }}</td>
          <td class="text-right">{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($item->total) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <!-- TOTALS -->
  <table width="100%" style="margin-top:20px;">
    <tr>
      <td width="55%" style="vertical-align:top;">

        @if($sale->payment_type == 'credit')
          <div style="font-size:12px; line-height:5px;">
            <strong>{{ __('translation.credit_information') }}</strong>
            <hr style="margin:5px 0;">
            <table width="100%">
              <tr>
                <td>{{ __('translation.credit_duration') }}</td>
                <td class="text-right">{{ $sale->creditDuration->name ?? '-' }}</td>
              </tr>
              <tr>
                <td>{{ __('translation.due_date') }}</td>
                <td class="text-right">{{ $sale->due_date ? \App\Helpers\Settings::getFormattedDate($sale->due_date) : '-' }}</td>
              </tr>
              @if($sale->interest_rate > 0)
                <tr>
                  <td>{{ __('translation.interest_rate') }}</td>
                  <td class="text-right">{{ App\Helpers\Settings::getcustomnumberformat($sale->interest_rate) }}%</td>
                </tr>
              @endif
            </table>
          </div>
        @endif
      </td>
      <td width="45%">
        <div class="totals-box">
          <table width="100%">
            {{-- Subtotal --}}
            <tr>
              <td>{{ __('translation.subtotal') }}</td>
              <td class="text-right">{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->subtotal) }}</td>
            </tr>
            {{-- Tax --}}
            @if($sale->tax > 0)
              <tr>
                <td>{{ __('translation.tax') }} @if(account_setting('general.tax')) ({{ account_setting('general.tax') }}%) @endif</td>
                <td class="text-right">{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->tax) }}</td>
              </tr>
            @endif
            {{-- Discount --}}
            @if($sale->discount > 0)
              <tr>
                <td>{{ __('translation.discount') }}</td>
                <td class="text-right">- {{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->discount) }}</td>
              </tr>
            @endif
            {{-- Interest --}}
            @if($sale->interest_amount > 0)
              <tr>
                <td>{{ __('translation.interest_amount') }}</td>
                <td class="text-right">{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->interest_amount) }}</td>
              </tr>
            @endif
            {{-- Grand Total --}}
            <tr>
              <td style="border-top:1px solid #ddd;font-weight:bold;">{{ __('translation.grand_total') }}</td>
              <td class="text-right" style="border-top:1px solid #ddd;font-weight:bold;">{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->payable_amount > 0 ? $sale->payable_amount : $sale->total) }}</td>
            </tr>
            {{-- Credit Payment Details --}}
            @if($sale->payment_type == 'credit')
              <tr>
                <td>{{ __('translation.paid_amount') }}</td>
                <td class="text-right">{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->paid_amount) }}</td>
              </tr>
              <tr>
                <td><strong>{{ __('translation.balance_amount') }}</strong></td>
                <td class="text-right"><strong>{{ __('translation.currency') }} {{ App\Helpers\Settings::getcustomnumberformat($sale->balance_amount) }}</strong></td>
              </tr>
            @endif
          </table>
        </div>
      </td>
    </tr>
  </table>
  <!-- PAYMENT INFORMATION -->
  <div style="margin-top:20px; font-size:11px;">
    <table width="100%" cellpadding="3" cellspacing="0">
      @if($sale->payments && $sale->payments->count())
        <tr>
          <td style="vertical-align:top;"><strong>{{ __('translation.payment_method') }}</strong></td>
          <td colspan="3">
            @foreach($sale->payments as $payment)
              {{ $payment->paymentMethod->name ?? ucfirst($payment->method) }}
              ({{ __('translation.currency') }}{{ App\Helpers\Settings::getcustomnumberformat($payment->amount) }})
              @if(!$loop->last),@endif
            @endforeach
          </td>
        </tr>
      @endif
    </table>
  </div>
  <hr style="margin:20px 0;">
  <!-- FOOTER -->
  <div style="text-align:center;font-size:11px;color:#6b7280;line-height:20px;">
    <strong style="font-size:13px;color:#111827;">{{ __('translation.thank_you_for_your_business') }}</strong>
    <br>
    {{ __('translation.we_appreciate_your_trust_and_look_forward_to_serving_you_again') }}
    @if(!empty($sale->store->phone))<br><strong>{{ __('translation.contact') }}:</strong> {{ $sale->store->phone }} @endif
    @if(!empty($sale->store->email)) | {{ $sale->store->email }} @endif
    @if(!empty($sale->store->website))
      <br>
      <a href="{{ $sale->store->website }}" style="color:#2563eb;text-decoration:none;">{{ $sale->store->website }}</a>
    @endif
  </div>
</body>

</html>