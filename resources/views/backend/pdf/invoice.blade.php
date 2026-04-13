<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ __('translation.webname') }} | {{ __('translation.invoice') }} | {{ Config::get('constants.shop_name')}}</title>
  @include('backend.pdf.layouts.pdfcss')
</head>

<body>

  <div class="watermark">{{ config('constants.shopname') }}</div>

  <!-- HEADER -->
  <table class="header">
    <tr>
      <td>
        <strong style="font-size:18px; color:#4f46e5;">
          {{ config('constants.shop_name') }}
        </strong><br>
        <span style="font-size:11px;">
          {{ config('constants.address') }}<br>
          {{ config('constants.phone_number') }}
        </span>
      </td>

      <td class="text-right">
        <div style="font-size:18px; font-weight:bold;">INVOICE</div>
        <div style="font-size:11px;">
          #INV-{{ date('Y', strtotime($sale->created_at ?? now())) }}-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}<br>
          {{ \Carbon\Carbon::parse($sale->created_at ?? now())->format('d M Y h:i A') }}
        </div>
      </td>
    </tr>
  </table>

  <!-- CUSTOMER + PAYMENT -->
  <table class="section">
    <tr>
      <td width="50%">
        <strong>Bill To</strong><br><br>

        {{ $sale->customer->name ?? 'Walk-in Customer' }}<br>

        @if(!empty($sale->customer?->phone))
          {{ $sale->customer->phone }}<br>
        @endif

        @if(!empty($sale->customer?->email))
          {{ $sale->customer->email }}
        @endif
      </td>

      <td width="50%" class="text-right">
        <strong>{{__('translation.payment_method')}}</strong><br><br>

        @if(!empty($sale->payments))
          @foreach($sale->payments as $payment)
            <span class="badge">
              {{ ucfirst($payment->method) }}
              {{ __('translation.currency') }} {{ number_format($payment->amount, 2) }}
            </span>
          @endforeach
        @else
          {{ $sale->payment_method ?? 'N/A' }}
        @endif
      </td>
    </tr>
  </table>

  <!-- ITEMS -->
  <table class="items" style="margin-top:10px;">
    <thead>
      <tr>
        <th align="left">{{ __('translation.product') }}</th>
        <th align="center">{{ __('translation.quantity') }}</th>
        <th align="right">{{ __('translation.price') }}</th>
        <th align="right">{{ __('translation.total') }}</th>
      </tr>
    </thead>

    <tbody>
      @foreach($sale->items ?? [] as $item)
        <tr>
          <td>{{ $item->product->name ?? 'Product' }}</td>
          <td align="center">{{ $item->quantity }}</td>
          <td align="right">{{ __('translation.currency') }} {{ number_format($item->price, 2) }}</td>
          <td align="right">{{ __('translation.currency') }} {{ number_format($item->total, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <!-- TOTALS -->
  <table style="margin-top:15px;">
    <tr>
      <td width="55%"></td>

      <td width="45%">
        <div class="totals-box">
          <table>

            <tr>
              <td>{{ __('translation.subtotal') }}</td>
              <td class="text-right">{{ __('translation.currency') }} {{ number_format($sale->subtotal, 2) }}</td>
            </tr>

            <tr>
              <td>{{ __('translation.tax') }}</td>
              <td class="text-right">{{ __('translation.currency') }} {{ number_format($sale->tax, 2) }}</td>
            </tr>

            @if($sale->discount > 0)
              <tr>
                <td>{{ __('translation.discount') }}</td>
                <td class="text-right">- {{ __('translation.currency') }} {{ number_format($sale->discount, 2) }}</td>
              </tr>
            @endif

            <tr>
              <td style="border-top:1px solid #ddd; font-weight:bold;">
                {{ __('translation.total') }}
              </td>
              <td class="text-right" style="border-top:1px solid #ddd; font-weight:bold; font-size:14px;">
                {{ __('translation.currency') }} {{ number_format($sale->total, 2) }}
              </td>
            </tr>

          </table>
        </div>
      </td>
    </tr>
  </table>

  <!-- PAYMENT INFO -->
  <div style="margin-top:10px; font-size:11px;">
    <strong>{{ __('translation.payment_type') }}:</strong>
    {{ ($sale->payment_method == null) ? 'Partial Payment' : 'Full Payment' }}
  </div>

  <hr style="margin:20px 0;">

  <!-- FOOTER -->
  <div style="text-align:center; font-size:11px; color:#6b7280;">
    <strong>{{ __('translation.thanks_for_your_visit') }}</strong><br>
    {{ __('translation.we_value_your_patronage') }}<br><br>

    {{ __('translation.we_offer_special_discounts_and_promotions_to_our_loyal_customers') }}<br>
    {{ __('translation.to_enjoy_these_benefits_please_visit_the_link_below_to_register') }}<br><br>

    <a href="{{ __('translation.havana_worlds_registration_link') }}" style="color:#4f46e5;">
      {{ __('translation.havana_worlds') }}
    </a>
  </div>

</body>

</html>