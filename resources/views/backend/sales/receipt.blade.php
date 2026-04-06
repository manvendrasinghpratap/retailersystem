<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ Config::get('constants.shop_name') . '|' . __('translation.receipt') }}</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/icons/favicon-16x16.png')}}">

    <style>
        * {
            font-size: 12px;
            font-family: monospace;
        }

        .ticket {
            width: 80mm;
            max-width: 80mm;
        }

        .centered {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border-top: 1px dashed #000;
            padding: 3px 0;
        }

        .hidden-print {
            margin-top: 10px;
        }

        @media print {
            .hidden-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="ticket">

        <div class="centered">
            <img src="{{ asset('assets/images/logo.png') }}" style="width:60px;"><br>
            <strong>{{ Config::get('constants.shop_name') }} </strong><br>
            {{ __('translation.phone') }} : {{ __('translation.webphone') }}<br>
            <p><strong>{{ __('translation.cashier') }}:</strong> {{ $sale->user->name ?? '-' }}</p>
            --------------------------------
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
                        <td colspan="7" class="text-center">No Sales Found</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">{{ __('translation.total') }}</th>
                    <th>{{ __('translation.b_ngn') . ' ' . number_format($sale->total, 2) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="centered">
            --------------------------------<br>
            Thank You!<br>
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