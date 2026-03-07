@extends('backend.layouts.master-horizontal') {{-- matches your file --}}
@section('title', 'Inventory')

@section('content')
@include('backend.components.breadcrumb')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title d-inline-block">Filter</h4>
                {{-- <div class="d-inline-block">
                    <x-href-input target='_blank' title="Download as PDF" id="downloadsalespdf" name="downloadpdf" href="{{ route('sales.report') }}" action="pdf" class="downloadsalespdf" :data-downloadroutepdf="route('sales.report')" />
                </div> --}}
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : 'sales.index') }}" class="mb-3"> 
                    <div class="row">
                        <x-date-input name="date" :label="__('translation.date')" value="{{ request()->get('date')?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="form-control flatdatepickr" mainrows="4"/>
                        <x-button submitText="Filter" resetText="Reset" url="{{ route($breadcrumb['route']??'sales.index') }}" isbutton="1" iscancel="1" mainrows="2"/>                       
                    </div>
                </form>
                {{-- Filter Form End --}}
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm rounded-2xl">
        {{-- <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Inventory - {{ $date }}</h5>
        <div>
            <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Add Stock
            </a>
            <a href="{{ route('admin.inventory.report', ['date' => $date]) }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-file"></i> Daily Report
            </a>
        </div>
        </div> --}}
            <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Opening</th>
                            <th>Stock In</th>
                            <th>Stock Out</th>
                            <th>Closing</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Build a map of existing inventory by menu_id for quick lookup
                            $invMap = [];
                            foreach($inventories as $inv) {
                                $invMap[$inv->menu_id] = $inv;
                            }
                        @endphp

                        @foreach($menus as $k => $menu)
                            @php
                                // $inv = $invMap[$menu->id] ?? null;
                                // If no record present, show opening from last closing
                                if (!$inv) {
                                    // best-effort: fetch previous day's closing (this makes DB call; you can optimize)
                                    $prev = \App\Models\Inventory::where('menu_id', $menu->id)
                                        ->where('record_date', '<', $date)
                                        ->orderBy('record_date','desc')
                                        ->first();
                                    $opening = $prev ? $prev->closing_stock : 0;
                                    $stock_in = 0;
                                    $stock_out = 0;
                                    $closing = $opening;
                                    $record_date = $date;
                                } else {
                                    $opening = $inv->opening_stock;
                                    $stock_in = $inv->stock_in;
                                    $stock_out = $inv->stock_out;
                                    $closing = $inv->closing_stock;
                                    $record_date = $inv->record_date->toDateString();
                                }
                            @endphp
                            <tr>
                                <td>{{ $k + 1 }}</td>
                                <td>{{ $menu->title }}</td>
                                <td>{{ number_format($opening, 2) }}</td>
                                <td>{{ number_format($stock_in, 2) }}</td>
                                <td>{{ number_format($stock_out, 2) }}</td>
                                <td>{{ number_format($closing, 2) }}</td>
                                <td>{{ $record_date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#inventory-table').DataTable({
            "pageLength": 25,
            "order": []
        });
    });
</script>
@endsection
