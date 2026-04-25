<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Print</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
        }

        /* Page Setup */
        @page {
            size: A4 portrait;
            margin: 5mm;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 4mm;
            font-size: 14px;
            font-weight: bold;
        }

        /* Label Container */
        .page {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, 63mm);
            grid-auto-rows: 15mm;
            gap: 10mm;
            justify-content: center;
        }

        /* Label */
        .label-a4 {
            width: 69mm;
            height: 22mm;
            border: 1px solid #ccc;
            padding: 1.5mm;
            text-align: center;
            overflow: hidden;
        }

        /* Thermal */
        .label-thermal {
            width: 70mm;
            height: 20mm;
            margin: auto;
            border: 1px solid #ccc;
            padding: 2mm;
            text-align: center;
            overflow: hidden;
        }

        /* Product Name */
        .name {
            font-size: 10px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 1mm;
        }

        /* Barcode */
        .barcode svg {
            width: 100% !important;
            height: 8mm !important;
        }

        /* Code */
        .code {
            font-size: 9px;
            margin-top: 1mm;
            letter-spacing: 1px;
        }

        /* Print */
        @media print {

            body {
                margin: 0;
            }

            .page {
                gap: 2mm;
            }

            .label-a4,
            .label-thermal {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header">
        Product : {!! $product->name !!}
    </div>

    @php
        $labelClass = ($size == 'thermal') ? 'label-thermal' : 'label-a4';
    @endphp

    <div class="page">

        @for($i = 1; $i <= $qty; $i++)

            <div class="{{ $labelClass }}">

                <div class="name">
                    {!! $product->name !!}
                </div>

                <div class="barcode">
                    {!! DNS1D::getBarcodeHTML($product->barcode, 'C128') !!}
                </div>

                <div class="code">
                    {{ $product->barcode }}
                </div>

            </div>

        @endfor

    </div>

</body>

</html>