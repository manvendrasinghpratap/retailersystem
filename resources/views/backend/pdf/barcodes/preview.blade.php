@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('translation.barcode_for_purchase_no') }}: {{ $purchase->purchase_no }}</h4>
                </div>
                <div class="card-body">
                    <div class="barcode-sheet">
                        @foreach($purchase->items as $item)
                            @php
                                $selectedItems = $selectedItems ?? [];
                                $printQty = $printQty ?? [];
                                $copies = $copies ?? 1;
                            @endphp
                            @if(empty($selectedItems) || in_array($item->id, $selectedItems))
                                @php
                                    $limit = $printQty[$item->id] ?? $item->trackings->count();
                                    $trackings = $item->trackings->take($limit);
                                @endphp
                                @foreach($trackings as $tracking)
                                    @for($c = 1; $c <= $copies; $c++)
                                        <div class="barcode-label">
                                            <div class="company">{{ auth()->user()->store->name ?? Config::get('constants.shop_name') }}</div>
                                            <div class="product">{{ $item->masterItem->name }}</div>
                                            <div class="barcode-image">{!! DNS1D::getBarcodeHTML($tracking->barcode, 'C128') !!}</div>
                                            <div class="barcode-number">{{ $tracking->barcode }}</div>
                                        </div>
                                    @endfor
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .barcode-sheet {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .barcode-label {
            width: 70mm;
            min-height: 20mm;

            border: 1px solid #000;
            border-radius: 4px;

            padding: 5px;
            padding-left: 10px;
            padding-right: 10px;

            text-align: center;
            page-break-inside: avoid;
            background: #fff;
        }

        .company {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .product {
            font-size: 10px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .barcode-image {
            margin: 2px 0;
        }

        .barcode-number {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        @media print {

            .barcode-label {
                border: 1px solid #000;
                margin: 4mm;
            }

        }
    </style>
@endsection