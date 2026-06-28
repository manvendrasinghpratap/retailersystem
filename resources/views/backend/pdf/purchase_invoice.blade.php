<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">

  <title>
    {{ $storeDetails->name ?? Config::get('constants.shop_name') }}
    ||
    {{ __('translation.purchase_invoice') }}
  </title>

  <style>
    @page {
      margin: 12mm;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
      font-size: 11px;
      color: #000;
      margin: 0;
      padding: 0;
      line-height: 1.4;
    }

    .invoice {
      width: 100%;
      position: relative;
    }

    .watermark {
      position: fixed;
      top: 40%;
      left: 15%;
      font-size: 70px;
      color: #eeeeee;
      transform: rotate(-35deg);
      z-index: -1;
      font-weight: bold;
      opacity: .30;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    .header td {
      vertical-align: top;
    }

    .logo {
      width: 90px;
    }

    .logo img {
      width: 80px;
      height: auto;
    }

    .company-details {
      font-size: 11px;
      line-height: 18px;
      padding-left: 10px;
    }

    .company-name {
      font-size: 22px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .invoice-title {
      font-size: 24px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 10px;
      letter-spacing: 1px;
    }

    .invoice-info {
      width: 100%;
      border: 1px solid #000;
    }

    .invoice-info td {
      border: 1px solid #000;
      padding: 6px;
      font-size: 11px;
    }

    .label {
      font-weight: bold;
      width: 42%;
      background: #f4f4f4;
    }

    .section-title {
      background: #f3f3f3;
      border: 1px solid #000;
      padding: 6px;
      font-size: 12px;
      font-weight: bold;
      margin-top: 15px;
    }

    .mt-15 {
      margin-top: 15px;
    }

    .text-right {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    .font-bold {
      font-weight: bold;
    }
  </style>

</head>

<body>

  <div class="watermark">
    {{ $storeDetails->name ?? Config::get('constants.shop_name') }}
  </div>

  <div class="invoice">

    {{-- ================= HEADER ================= --}}

    <table class="header">

      <tr>

        {{-- Logo --}}

        <td width="12%">

          @if(!empty($storeDetails->logo))

            <div class="logo">

              <img src="{{ public_path('storage/' . $storeDetails->logo) }}">

            </div>

          @endif

        </td>

        {{-- Company Details --}}

        <td width="53%">

          <div class="company-details">

            <div class="company-name">
              {{ $storeDetails->name ?? Config::get('constants.shop_name') }}
            </div>

            @if(!empty($storeDetails->address))
              {{ $storeDetails->address }}<br>
            @endif

            @if(!empty($storeDetails->phone))
              <strong>{{ __('translation.phone') }} :</strong>
              {{ $storeDetails->phone }}

              @if(!empty($storeDetails->alternate_phone))
                /
                {{ $storeDetails->alternate_phone }}
              @endif

              <br>
            @endif

            @if(!empty($storeDetails->email))
              <strong>{{ __('translation.email') }} :</strong>
              {{ $storeDetails->email }}
              <br>
            @endif

            @if(!empty($storeDetails->website))
              <strong>{{ __('translation.website') }} :</strong>
              {{ $storeDetails->website }}
            @endif

          </div>

        </td>

        {{-- Invoice Details --}}

        <td width="35%">

          <div class="invoice-title">

            {{ __('translation.purchase_invoice') }}

          </div>

          <table class="invoice-info">

            <tr>

              <td class="label">
                {{ __('translation.invoice_no') }}
              </td>

              <td>
                {{ $purchase->invoice_number ?? '-' }}
              </td>

            </tr>

            <tr>

              <td class="label">
                {{ __('translation.date') }}
              </td>

              <td>
                {{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}
              </td>

            </tr>

            <tr>

              <td class="label">
                {{ __('translation.purchase_no') }}
              </td>

              <td>
                {{ $purchase->purchase_no ?? '-' }}
              </td>

            </tr>

            <tr>

              <td class="label">
                {{ __('translation.reference') }}
              </td>

              <td>
                {{ $purchase->reference ?? '-' }}
              </td>

            </tr>

            <tr>

              <td class="label">
                {{ __('translation.created_by') }}
              </td>

              <td>
                {{ $purchase->user->name ?? '-' }}
              </td>

            </tr>

          </table>

        </td>

      </tr>

    </table>

    {{-- ================= END HEADER ================= --}}
    {{-- ================= SUPPLIER INFORMATION ================= --}}

    <div class="section-title">
      {{ __('translation.supplier_information') }}
    </div>

    <table style="border:1px solid #000; margin-top:-1px;">

      <tr>

        {{-- Supplier Details --}}
        <td width="60%" style="border:1px solid #000; padding:8px; vertical-align:top;">

          <div style="font-size:13px;font-weight:bold;margin-bottom:8px;">
            {{ __('translation.sold_to') }}
          </div>

          <table width="100%">

            <tr>
              <td width="28%">
                <strong>{{ __('translation.supplier') }}</strong>
              </td>
              <td>
                {{ $purchase->supplier->name ?? '-' }}
              </td>
            </tr>

            @if(!empty($purchase->supplier->address))
              <tr>
                <td>
                  <strong>{{ __('translation.address') }}</strong>
                </td>
                <td>
                  {{ $purchase->supplier->address }}
                </td>
              </tr>
            @endif

            @if(!empty($purchase->supplier->phone))
              <tr>
                <td>
                  <strong>{{ __('translation.phone') }}</strong>
                </td>
                <td>
                  {{ $purchase->supplier->phone }}
                </td>
              </tr>
            @endif

            @if(!empty($purchase->supplier->email))
              <tr>
                <td>
                  <strong>{{ __('translation.email') }}</strong>
                </td>
                <td>
                  {{ $purchase->supplier->email }}
                </td>
              </tr>
            @endif

            @if(!empty($purchase->supplier->contact_person))
              <tr>
                <td>
                  <strong>{{ __('translation.contact_person') }}</strong>
                </td>
                <td>
                  {{ $purchase->supplier->contact_person }}
                </td>
              </tr>
            @endif

          </table>

        </td>

        {{-- Supplier Tax Information --}}
        <td width="40%" style="border:1px solid #000;padding:8px;vertical-align:top;">

          <div style="font-size:13px;font-weight:bold;margin-bottom:8px;">
            {{ __('translation.tax_information') }}
          </div>

          <table width="100%">

            <tr>
              <td width="45%">
                <strong>VAT No</strong>
              </td>
              <td>
                {{ $purchase->supplier->vat ?? '-' }}
              </td>
            </tr>

            <tr>
              <td>
                <strong>TIN</strong>
              </td>
              <td>
                {{ $purchase->supplier->tin ?? '-' }}
              </td>
            </tr>

            <tr>
              <td>
                <strong>RC No</strong>
              </td>
              <td>
                {{ $purchase->supplier->rc ?? '-' }}
              </td>
            </tr>

            <tr>
              <td>
                <strong>{{ __('translation.reference') }}</strong>
              </td>
              <td>
                {{ $purchase->reference ?? '-' }}
              </td>
            </tr>

          </table>

        </td>

      </tr>

    </table>


    {{-- ================= PURCHASE DETAILS ================= --}}

    <div class="section-title">
      {{ __('translation.purchase_details') }}
    </div>

    <table style="border:1px solid #000;margin-top:-1px;">

      <tr>

        <td width="50%" style="border:1px solid #000;padding:8px;vertical-align:top;">

          <table width="100%">

            <tr>
              <td width="45%">
                <strong>{{ __('translation.purchase_date') }}</strong>
              </td>
              <td>
                {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}
              </td>
            </tr>

            <tr>
              <td>
                <strong>{{ __('translation.invoice_date') }}</strong>
              </td>
              <td>
                {{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}
              </td>
            </tr>

            <tr>
              <td>
                <strong>{{ __('translation.payment_type') }}</strong>
              </td>
              <td>
                {{ ucfirst($purchase->payment_type ?? '-') }}
              </td>
            </tr>

            <tr>
              <td>
                <strong>{{ __('translation.payment_status') }}</strong>
              </td>
              <td>
                {{ ucfirst($purchase->payment_status ?? '-') }}
              </td>
            </tr>

          </table>

        </td>

        <td width="50%" style="border:1px solid #000;padding:8px;vertical-align:top;">

          <table width="100%">

            <tr>
              <td width="45%">
                <strong>{{ __('translation.warehouse') }}</strong>
              </td>
              <td>
                {{ $purchase->warehouse->name ?? '-' }}
              </td>
            </tr>

            <tr>
              <td>
                <strong>{{ __('translation.received_by') }}</strong>
              </td>
              <td>
                {{ $purchase->receivedBy->name ?? '-' }}
              </td>
            </tr>

            <tr>
              <td>
                <strong>{{ __('translation.created_by') }}</strong>
              </td>
              <td>
                {{ $purchase->user->name ?? '-' }}
              </td>
            </tr>

            <tr>
              <td>
                <strong>{{ __('translation.status') }}</strong>
              </td>
              <td>
                {{ ucfirst($purchase->status ?? '-') }}
              </td>
            </tr>

          </table>

        </td>

      </tr>

    </table>

    {{-- ================= END SUPPLIER SECTION ================= --}}
    {{-- ================= PURCHASE ITEMS ================= --}}

    <div class="section-title">
      {{ __('translation.purchase_items') }}
    </div>

    <table class="items" style="margin-top:-1px;">

      <thead>

        <tr style="background:#efefef;">

          <th width="5%" align="center">
            #
          </th>

          <th width="15%">
            {{ __('translation.code') }}
          </th>

          <th width="28%">
            {{ __('translation.product') }}
          </th>

          <th width="18%">
            {{ __('translation.description') }}
          </th>

          <th width="8%" align="center">
            {{ __('translation.qty') }}
          </th>

          <th width="10%" align="right">
            {{ __('translation.cost_price') }}
          </th>

          <th width="8%" align="right">
            {{ __('translation.tax') }}
          </th>

          <th width="8%" align="right">
            {{ __('translation.discount') }}
          </th>

          <th width="12%" align="right">
            {{ __('translation.total') }}
          </th>

        </tr>

      </thead>

      <tbody>

        @php

          $subTotal = 0;

          $totalTax = 0;

          $totalDiscount = 0;

        @endphp

        @forelse($purchase->items as $index => $item)

          @php

            $lineTax = $item->tax ?? 0;

            $lineDiscount = $item->discount ?? 0;

            $lineTotal = $item->total;

            $subTotal += $lineTotal;

            $totalTax += $lineTax;

            $totalDiscount += $lineDiscount;

          @endphp

          <tr>

            <td align="center">

              {{ $index + 1 }}

            </td>

            <td>

              {{ $item->product->code ?? '-' }}

            </td>

            <td>

              {{ $item->product->name ?? '-' }}

            </td>

            <td>

              {{ $item->description ?? '-' }}

            </td>

            <td align="center">

              {{ number_format($item->quantity, 2) }}

            </td>

            <td align="right">

              {{ __('translation.currency') }}

              {{ number_format($item->cost_price, 2) }}

            </td>

            <td align="right">

              {{ __('translation.currency') }}

              {{ number_format($lineTax, 2) }}

            </td>

            <td align="right">

              {{ __('translation.currency') }}

              {{ number_format($lineDiscount, 2) }}

            </td>

            <td align="right">

              {{ __('translation.currency') }}

              {{ number_format($lineTotal, 2) }}

            </td>

          </tr>

        @empty

          <tr>

            <td colspan="9" align="center" style="padding:20px;">

              {{ __('translation.no_items_found') }}

            </td>

          </tr>

        @endforelse

        {{-- Empty rows to maintain invoice height --}}

        @php

          $minimumRows = 15;

          $blankRows = $minimumRows - ($purchase->items->count());

        @endphp

        @for($i = 0; $i < $blankRows; $i++)

          <tr>

            <td>&nbsp;</td>

            <td></td>

            <td></td>

            <td></td>

            <td></td>

            <td></td>

            <td></td>

            <td></td>

            <td></td>

          </tr>

        @endfor

      </tbody>

    </table>

    {{-- ================= NOTES & TOTALS ================= --}}

    <table width="100%" style="margin-top:15px;">

      <tr>

        {{-- Terms & Conditions --}}
        <td width="60%" style="vertical-align:top; padding-right:15px;">

          <div class="section-title">
            {{ __('translation.terms_conditions') }}
          </div>

          <div style="border:1px solid #000; border-top:none; padding:10px; min-height:170px;">

            @if(!empty($storeDetails->purchase_invoice_note))

              {!! nl2br(e($storeDetails->purchase_invoice_note)) !!}

            @else

              <ol style="margin:0;padding-left:18px;line-height:20px;">

                <li>{{ __('translation.goods_once_sold_are_not_returnable') }}</li>

                <li>{{ __('translation.check_items_before_signing_delivery_note') }}</li>

                <li>{{ __('translation.warranty_subject_to_company_policy') }}</li>

                <li>{{ __('translation.report_any_damage_within_24_hours') }}</li>

                <li>{{ __('translation.thank_you_for_your_business') }}</li>

              </ol>

            @endif

          </div>

          {{-- Amount in Words --}}

          <div class="section-title" style="margin-top:15px;">
            {{ __('translation.amount_in_words') }}
          </div>

          <div style="border:1px solid #000; border-top:none; padding:10px; min-height:60px;">

            {{ $purchase->amount_in_words ?? '-' }}
            <!-- \App\Helpers\NumberHelper::amountInWords($purchase->grand_total) -->

          </div>

        </td>

        {{-- Totals --}}
        <td width="40%" style="vertical-align:top;">

          <div class="section-title">

            {{ __('translation.summary') }}

          </div>

          <table style="border:1px solid #000; border-top:none;">

            {{-- Subtotal --}}
            <tr>

              <td style="padding:8px;">
                {{ __('translation.subtotal') }}
              </td>

              <td align="right" style="padding:8px;">

                {{ __('translation.currency') }}

                {{ number_format($purchase->subtotal ?? $subTotal, 2) }}

              </td>

            </tr>

            {{-- Tax --}}
            @if(($purchase->tax ?? $totalTax) > 0)

              <tr>

                <td style="padding:8px;">

                  {{ __('translation.tax') }}

                </td>

                <td align="right" style="padding:8px;">

                  {{ __('translation.currency') }}

                  {{ number_format($purchase->tax ?? $totalTax, 2) }}

                </td>

              </tr>

            @endif

            {{-- Discount --}}
            @if(($purchase->discount ?? $totalDiscount) > 0)

              <tr>

                <td style="padding:8px;">

                  {{ __('translation.discount') }}

                </td>

                <td align="right" style="padding:8px;">

                  - {{ __('translation.currency') }}

                  {{ number_format($purchase->discount ?? $totalDiscount, 2) }}

                </td>

              </tr>

            @endif

            {{-- Shipping --}}
            @if(($purchase->shipping_charge ?? 0) > 0)

              <tr>

                <td style="padding:8px;">

                  {{ __('translation.shipping_charge') }}

                </td>

                <td align="right" style="padding:8px;">

                  {{ __('translation.currency') }}

                  {{ number_format($purchase->shipping_charge, 2) }}

                </td>

              </tr>

            @endif

            {{-- Other Charges --}}
            @if(($purchase->other_charge ?? 0) > 0)

              <tr>

                <td style="padding:8px;">

                  {{ __('translation.other_charge') }}

                </td>

                <td align="right" style="padding:8px;">

                  {{ __('translation.currency') }}

                  {{ number_format($purchase->other_charge, 2) }}

                </td>

              </tr>

            @endif

            {{-- Grand Total --}}
            <tr>

              <td style="padding:10px;border-top:2px solid #000;font-weight:bold;font-size:12px;">

                {{ __('translation.grand_total') }}

              </td>

              <td align="right" style="padding:10px;border-top:2px solid #000;font-weight:bold;font-size:13px;">

                {{ __('translation.currency') }}

                {{ number_format($purchase->grand_total ?? $purchase->total, 2) }}

              </td>

            </tr>

            {{-- Paid --}}
            @if(isset($purchase->paid_amount))

              <tr>

                <td style="padding:8px;">

                  {{ __('translation.paid_amount') }}

                </td>

                <td align="right" style="padding:8px;">

                  {{ __('translation.currency') }}

                  {{ number_format($purchase->paid_amount, 2) }}

                </td>

              </tr>

            @endif

            {{-- Balance --}}
            @if(isset($purchase->balance_amount))

              <tr>

                <td style="padding:8px;font-weight:bold;">

                  {{ __('translation.balance_amount') }}

                </td>

                <td align="right" style="padding:8px;font-weight:bold;">

                  {{ __('translation.currency') }}

                  {{ number_format($purchase->balance_amount, 2) }}

                </td>

              </tr>

            @endif

          </table>

        </td>

      </tr>

    </table>

    {{-- ================= SIGNATURE SECTION ================= --}}

    <table width="100%" style="margin-top:40px;">

      <tr>

        <td width="33%" align="center">

          <div style="border-top:1px solid #000; width:180px; margin:0 auto;"></div>

          <div style="margin-top:6px; font-weight:bold;">
            {{ __('translation.prepared_by') }}
          </div>

          <div style="margin-top:4px;">
            {{ $purchase->user->name ?? '-' }}
          </div>

        </td>

        <td width="34%" align="center">

          <div style="border-top:1px solid #000; width:180px; margin:0 auto;"></div>

          <div style="margin-top:6px; font-weight:bold;">
            {{ __('translation.received_by') }}
          </div>

          <div style="margin-top:4px;">
            ________________________
          </div>

        </td>

        <td width="33%" align="center">

          <div style="border-top:1px solid #000; width:180px; margin:0 auto;"></div>

          <div style="margin-top:6px; font-weight:bold;">
            {{ __('translation.authorized_by') }}
          </div>

          <div style="margin-top:4px;">
            ________________________
          </div>

        </td>

      </tr>

    </table>


    {{-- ================= REMARKS ================= --}}

    @if(!empty($purchase->remarks))

      <div class="section-title" style="margin-top:25px;">

        {{ __('translation.remarks') }}

      </div>

      <div style="border:1px solid #000;border-top:none;padding:10px;min-height:60px;">

        {!! nl2br(e($purchase->remarks)) !!}

      </div>

    @endif


    {{-- ================= FOOTER ================= --}}

    <hr style="margin-top:35px;margin-bottom:10px;">

    <table width="100%">

      <tr>

        <td width="70%" style="font-size:10px;color:#555;line-height:18px;">

          <strong>

            {{ $storeDetails->name ?? Config::get('constants.shop_name') }}

          </strong>

          <br>

          @if(!empty($storeDetails->address))
            {{ $storeDetails->address }}<br>
          @endif

          @if(!empty($storeDetails->phone))

            {{ __('translation.phone') }} :

            {{ $storeDetails->phone }}

            @if(!empty($storeDetails->alternate_phone))
              /
              {{ $storeDetails->alternate_phone }}
            @endif

            <br>

          @endif

          @if(!empty($storeDetails->email))

            {{ __('translation.email') }} :

            {{ $storeDetails->email }}

            <br>

          @endif

          @if(!empty($storeDetails->website))

            {{ __('translation.website') }} :

            {{ $storeDetails->website }}

          @endif

        </td>

        <td width="30%" align="right" style="font-size:10px;color:#555;">

          {{ __('translation.generated_on') }}

          <br>

          {{ now()->format('d M Y h:i A') }}

          <br><br>

          {{ __('translation.printed_by') }}

          <br>

          {{ auth()->user()->name ?? '-' }}

        </td>

      </tr>

    </table>


    <div style="text-align:center;margin-top:15px;font-size:11px;color:#666;">

      <strong>

        {{ __('translation.thank_you_for_your_business') }}

      </strong>

      <br>

      {{ __('translation.this_is_a_system_generated_document') }}

    </div>


    {{-- ================= PAGE NUMBER ================= --}}

    <script type="text/php">
if (isset($pdf)) {

    $font = $fontMetrics->get_font("Helvetica", "normal");

    $pdf->page_text(
        520,
        810,
        "Page {PAGE_NUM} of {PAGE_COUNT}",
        $font,
        9,
        array(0,0,0)
    );

}
</script>

  </div>

</body>

</html>