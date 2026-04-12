<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ __('translation.webname') }} | {{ __('translation.invoice') }} | {{ Config::get('constants.shop_name')}}</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f9; font-family:Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px;">
        <tr>
            <td align="center">

                <!-- Container -->
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#4e73df,#1cc88a); color:#fff; padding:20px;">
                            <h2 style="margin:0;">{{ Config::get('constants.shop_name') }}</h2>
                            <p style="margin:5px 0 0;">{{ __('translation.invoice') }} #{{ $invoice_no }}</p>
                        </td>
                    </tr>

                    <!-- Customer Info -->
                    <tr>
                        <td style="padding:20px;">
                            <p style="margin:0;"><strong>Hello {{ $customer->name ?? 'Customer' }},</strong></p>
                            <p style="margin:5px 0 15px;">{{ __('translation.thank_you_for_your_purchase') }} 🎉</p>

                            <table width="100%" style="font-size:14px;">
                                <tr>
                                    <td><strong>Date:</strong> {{ App\Helpers\Settings::getFormattedDatetime($sale->created_at)}}</td>
                                    <td align="right"><strong>Time:</strong> {{ App\Helpers\Settings::getFormattedDatetime($sale->created_at)}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Items Table -->
                    <tr>
                        <td style="padding:0 20px 20px;">
                            <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse; font-size:14px;">

                                <thead>
                                    <tr style="background:#f8f9fc;">
                                        <th align="left">{{ __('translation.product') }}</th>
                                        <th align="center">{{ __('translation.quantity') }}</th>
                                        <th align="right">{{ __('translation.price') }}</th>
                                        <th align="right">{{ __('translation.total') }}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($items as $item)
                                        <tr style="border-bottom:1px solid #eee;">
                                            <td>{{ $item->product->name ?? 'Product' }}</td>
                                            <td align="center">{{ $item->quantity }}</td>
                                            <td align="right">₦ {{ number_format($item->price, 2) }}</td>
                                            <td align="right">₦ {{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </td>
                    </tr>

                    <!-- Summary -->
                    <tr>
                        <td style="padding:0 20px 20px;">
                            <table width="100%" style="font-size:14px;">
                                <tr>
                                    <td align="right">Subtotal:</td>
                                    <td align="right" width="120">₦ {{ number_format($sale->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td align="right">Tax:</td>
                                    <td align="right">₦ {{ number_format($sale->tax, 2) }}</td>
                                </tr>
                                @if($sale->discount > 0)
                                    <tr>
                                        <td align="right">Discount:</td>
                                        <td align="right">₦ {{ number_format($sale->discount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td align="right"><strong>Total:</strong></td>
                                    <td align="right"><strong>₦ {{ number_format($sale->total, 2) }}</strong></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 20px 20px;">
                            <div style="font-size:14px;">
                                <p><strong>{{ __('translation.payment_type') }}:</strong> {{ ($sale->payment_method == null) ? 'Partial Payment' : 'Full Payment' }}</p>
                                <p><strong>{{ __('translation.payment_method') }}:</strong> {{ $sale->payment_methods ?? '-' }}</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <hr />
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 20px 20px; text-align:center;">
                            <div class="centered">
                                {{ __('translation.thanks_for_your_visit') }}<br />
                                {{ __('translation.we_value_your_patronage') }}<br />
                                {{ __('translation.also_visit_our_havana_kitchen_and_lounge') }}<br>
                                <p>
                                    {{ __('translation.we_offer_special_discounts_and_promotions_to_our_loyal_customers') }}<br>
                                    {{ __('translation.to_enjoy_these_benefits_please_visit_the_link_below_to_register') }}<br>
                                    <a href="{{ __('translation.havana_worlds_registration_link') }}" target="_blank">{{ __('translation.havana_worlds') }}</a>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <!-- <tr>
                        <td style="background:#f8f9fc; padding:20px; text-align:center; font-size:13px; color:#666;">
                            <p style="margin:0;">Thank you for shopping with us 🙏</p>
                            <p style="margin:5px 0 0;">If you have any questions, reply to this email.</p>
                        </td>
                    </tr> -->

                </table>

            </td>
        </tr>
    </table>

</body>

</html>