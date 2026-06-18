<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ __('translation.webname') }} | {{ __('translation.receipt') }} | {{ Config::get('constants.shop_name')}}</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/icons/favicon-16x16.png')}}">
    @include('backend.admin.print.css')
</head>

<body>

    <div class="ticket">

        <div class="centered">
            <p><strong>{{ $storeDetails->name ?? Config::get('constants.shop_name') }} </strong></p>
            {{ $storeDetails->address ?? __('translation.webaddress') }}<br>
            <!-- <p>{{ Config::get('constants.mainwebsite') }}<br> -->
            {{ __('translation.phone') }} : {{ $storeDetails->phone ?? '' }} {{ $storeDetails->alternate_phone ? ' | ' . $storeDetails->alternate_phone : '' }}
            <p><strong>{{ __('translation.cashier') }}:</strong> {{ $sale->user->name ?? '-' }}</p>
        </div>

        <p>
            {{ __('translation.invoice') }} #: {{ $sale->invoice_no }} <br>
            Date: {{ App\Helpers\Settings::getFormattedDatetime($sale->created_at)}}
        </p>

        <table>
            <thead>
                <tr>
                    <th>{{ __('translation.s_no') }}</th>
                    <th>{{ __('translation.product_name') }}</th>
                    <th>{{ __('translation.quantity') }}</th>
                    <th>{{ __('translation.b_ngn') . ' ' . __('translation.price') }}</th>
                    <th>{{ __('translation.b_ngn') . ' ' . __('translation.total') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($sale->items as $item)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ __('translation.b_ngn') . ' ' . number_format($item->price, 2) }}</td>
                        <td>{{ __('translation.b_ngn') . ' ' . number_format($item->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('translation.no_sales_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">{{ __('translation.subtotal') }}</th>
                    <th>{{ __('translation.b_ngn') . ' ' . number_format($sale->subtotal, 2) }}</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">{{ __('translation.tax') }}</th>
                    <th>{{ __('translation.b_ngn') . ' ' . number_format($sale->tax, 2) }}</th>
                </tr>
                @if($sale->discount > 0)
                    <tr>
                        <th colspan="4" class="text-right">{{ __('translation.discount') }}</th>
                        <th>{{ __('translation.b_ngn') . ' ' . number_format($sale->discount, 2) }}</th>
                    </tr>
                @endif
                <tr>
                    <th colspan="4" class="text-right">{{ __('translation.total_payment') }}</th>
                    <th>{{ __('translation.b_ngn') . ' ' . number_format($sale->total, 2) }}</th>
                </tr>
            </tfoot>
        </table>
        <div>
            <p><strong>{{ __('translation.payment_type') }}:</strong> {{ ($sale->payment_method == null) ? 'Partial Payment' : 'Full Payment' }}</p>
            <p><strong>{{ __('translation.payment_method') }}:</strong> {{ $sale->payment_methods ?? '-' }}</p>
        </div>
        <div class="centered">
            --------------------------------<br>
            {{ __('translation.thanks_for_your_visit')}} {{ $storeDetails->name ?? Config::get('constants.shop_name') }}<br />
            {{ __('translation.we_value_your_patronage') }}<br />
        </div>

    </div>

    <!-- Print Button -->
    <button class="hidden-print" onclick="window.print()">Print</button>

    <script>
        // 🔥 Auto print when opened
        window.onload = function () {
            window.print();
        };
    </script>

</body>

</html>