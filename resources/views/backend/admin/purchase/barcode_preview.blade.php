@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
    <div class="row">

        <div class="col-lg-12">

            <div class="card">

                <div class="card-header">

                    <h4 class="card-title">

                        Purchase :
                        {{ $purchase->purchase_no }}

                    </h4>

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

                                            <div class="company">

                                                {{ config('constants.shop_name') }}

                                            </div>

                                            <div class="product">

                                                {{ $item->masterItem->name }}

                                            </div>

                                            <div class="barcode-image">

                                                {!! DNS1D::getBarcodeHTML($tracking->barcode, 'C128') !!}

                                            </div>

                                            <div class="barcode-number">

                                                {{ $tracking->barcode }}

                                            </div>

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

@endsection