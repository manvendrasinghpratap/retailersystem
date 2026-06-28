<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ $storeDetails->name ?? Config::get('constants.shop_name') }} || {{ __('translation.invoice') }} </title>
  @include('backend.pdf.layouts.pdfcss')
</head>

<body>
  <div class="watermark">{{ $storeDetails->name ?? Config::get('constants.shop_name') }}</div>
  <!-- HEADER -->
  <table class="header" width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <!-- Company Details -->
      <td width="60%" style="vertical-align:top;">
        <div style="font-size:22px; font-weight:bold; color:#2563eb; margin-bottom:5px;">
          {{ $storeDetails->name ?? Config::get('constants.shop_name') }}
        </div>
        <div style="font-size:11px; color:#555; line-height:13px;">
          <strong>{{ __('translation.address') }}:</strong>
          {{ $storeDetails->address ?? '' }}
          <br>

          <strong>{{ __('translation.phone') }}:</strong>
          {{ $storeDetails->phone ?? '' }}
          @if(!empty($storeDetails->alternate_phone))
            || {{ $storeDetails->alternate_phone }}
          @endif
          <br>

          @if(!empty($storeDetails->email))
            <strong>{{ __('translation.email') }}:</strong>
            {{ $storeDetails->email }}
            <br>
          @endif

          @if(!empty($storeDetails->website))
            <strong>{{ __('translation.website') }}:</strong>
            {{ $storeDetails->website }}
          @endif
        </div>
      </td>

      <!-- Invoice Details -->
      <td width="40%" align="right" style="vertical-align:top;">
        <div style="font-size:28px; font-weight:bold; color:#111827; margin-bottom:10px;">
          {{ __('translation.invoice') }}
        </div>

        <table style="font-size:11px; margin-left:auto;">
          <tr>
            <td style="padding:2px 8px;"><strong>{{ __('translation.invoice_no') }}</strong></td>
            <td style="padding:2px 0;">: {{ $sale->invoice_no }}</td>
          </tr>

          <tr>
            <td style="padding:2px 8px;"><strong>{{ __('translation.date') }}</strong></td>
            <td style="padding:2px 0;">
              : {{ \Carbon\Carbon::parse($sale->created_at ?? now())->format('d M Y') }}
            </td>
          </tr>

          <tr>
            <td style="padding:2px 8px;"><strong>{{ __('translation.time') }}</strong></td>
            <td style="padding:2px 0;">
              : {{ \Carbon\Carbon::parse($sale->created_at ?? now())->format('h:i A') }}
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

        <div style="font-size:13px;font-weight:bold;color:#2563eb;margin-bottom:8px;">
          {{ __('translation.customer_information') }}
        </div>

        <table width="100%" style="font-size:11px; line-height:3px;">

          <tr>
            <td width="90"><strong>{{ __('translation.customer') }}</strong></td>
            <td>
              : {{ $sale->customer->name ?? __('translation.walk_in_customer') }}
            </td>
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
              <td style="vertical-align:top;">
                <strong>{{ __('translation.address') }}</strong>
              </td>
              <td>
                : <div style="display:inline-block;max-width:220px;">
                  {{ $sale->customer->address }}
                </div>
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
            <td width="45%">
              <strong>{{ __('translation.payment_type') }}</strong>
            </td>
            <td align="right">
              {{ $sale->customerPaymentType->name ?? ucfirst($sale->payment_type) }}
            </td>
          </tr>

          <tr>
            <td>
              <strong>{{ __('translation.payment_status') }}</strong>
            </td>
            <td align="right">

              @if($sale->payment_status == 'paid')
                <span style="color:#16a34a;font-weight:bold;">
                  {{ ucfirst($sale->payment_status) }}
                </span>

              @elseif($sale->payment_status == 'partial')
                <span style="color:#d97706;font-weight:bold;">
                  {{ ucfirst($sale->payment_status) }}
                </span>

              @else
                <span style="color:#dc2626;font-weight:bold;">
                  {{ ucfirst($sale->payment_status) }}
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
        <th align="left">#</th>
        <th align="left">{{ __('translation.product') }}</th>
        <th align="center">{{ __('translation.quantity') }}</th>
        <th align="right">{{ __('translation.price') }}</th>
        <th align="right">{{ __('translation.total') }}</th>
      </tr>
    </thead>

    <tbody>
      @foreach($sale->items ?? [] as $index => $item)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $item->product->name ?? 'Product' }}</td>
          <td align="center">{{ $item->quantity }}</td>
          <td align="right">{{ __('translation.currency') }} {{ number_format($item->price, 2) }}</td>
          <td align="right">{{ __('translation.currency') }} {{ number_format($item->total, 2) }}</td>
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
                    <td class="text-right">
                      {{ $sale->creditDuration->name ?? '-' }}
                    </td>
                  </tr>

                  <tr>
                    <td>{{ __('translation.due_date') }}</td>
                    <td class="text-right">
                      {{ $sale->due_date
          ? \App\Helpers\Settings::getFormattedDate($sale->due_date)
          : '-' }}
                    </td>
                  </tr>

                  @if($sale->interest_rate > 0)
                    <tr>
                      <td>{{ __('translation.interest_rate') }}</td>
                      <td class="text-right">
                        {{ number_format($sale->interest_rate, 2) }}%
                      </td>
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
              <td class="text-right">
                {{ __('translation.currency') }}
                {{ number_format($sale->subtotal, 2) }}
              </td>
            </tr>

            {{-- Tax --}}
            @if($sale->tax > 0)
              <tr>
                <td>
                  {{ __('translation.tax') }}
                  @if(account_setting('general.tax'))
                    ({{ account_setting('general.tax') }}%)
                  @endif
                </td>
                <td class="text-right">
                  {{ __('translation.currency') }}
                  {{ number_format($sale->tax, 2) }}
                </td>
              </tr>
            @endif

            {{-- Discount --}}
            @if($sale->discount > 0)
              <tr>
                <td>{{ __('translation.discount') }}</td>
                <td class="text-right">
                  - {{ __('translation.currency') }}
                  {{ number_format($sale->discount, 2) }}
                </td>
              </tr>
            @endif

            {{-- Interest --}}
            @if($sale->interest_amount > 0)
              <tr>
                <td>{{ __('translation.interest_amount') }}</td>
                <td class="text-right">
                  {{ __('translation.currency') }}
                  {{ number_format($sale->interest_amount, 2) }}
                </td>
              </tr>
            @endif

            {{-- Grand Total --}}
            <tr>
              <td style="border-top:1px solid #ddd;font-weight:bold;">
                {{ __('translation.grand_total') }}
              </td>
              <td class="text-right" style="border-top:1px solid #ddd;font-weight:bold;">
                {{ __('translation.currency') }}
                {{ number_format($sale->payable_amount > 0 ? $sale->payable_amount : $sale->total, 2) }}
              </td>
            </tr>

            {{-- Credit Payment Details --}}
            @if($sale->payment_type == 'credit')

              <tr>
                <td>{{ __('translation.paid_amount') }}</td>
                <td class="text-right">
                  {{ __('translation.currency') }}
                  {{ number_format($sale->paid_amount, 2) }}
                </td>
              </tr>

              <tr>
                <td>
                  <strong>{{ __('translation.balance_amount') }}</strong>
                </td>
                <td class="text-right">
                  <strong>
                    {{ __('translation.currency') }}
                    {{ number_format($sale->balance_amount, 2) }}
                  </strong>
                </td>
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
          <td style="vertical-align:top;">
            <strong>{{ __('translation.payment_method') }}</strong>
          </td>

          <td colspan="3">

            @foreach($sale->payments as $payment)

              {{ $payment->paymentMethod->name ?? ucfirst($payment->method) }}
              ({{ __('translation.currency') }}
              {{ number_format($payment->amount, 2) }})

              @if(!$loop->last)
                ,
              @endif

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

    @if(!empty($storeDetails->phone))
      <br>
      <strong>{{ __('translation.contact') }}:</strong>
      {{ $storeDetails->phone }}
    @endif

    @if(!empty($storeDetails->email))
      |
      {{ $storeDetails->email }}
    @endif

    @if(!empty($storeDetails->website))
      <br>
      <a href="{{ $storeDetails->website }}" style="color:#2563eb;text-decoration:none;">
        {{ $storeDetails->website }}
      </a>
    @endif

  </div>
</body>

</html>