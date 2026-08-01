@extends('backend.layouts.master-horizontal')

@section('title')
    {{ $breadcrumb['title'] ?? 'Print Barcode' }}
@endsection

@section('content')

    @include('backend.components.breadcrumb')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title">
                        {{ __('translation.purchase') }} # {{ $purchase->purchase_no }}
                    </h4>
                </div>

                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>{{ __('translation.vendor') }}</strong><br>
                            {{ $purchase->vendor->company_name ?? '-' }}
                        </div>

                        <div class="col-md-4">
                            <strong>{{ __('translation.warehouse') }}</strong><br>
                            {{ $purchase->warehouse->name ?? '-' }}
                        </div>

                        <div class="col-md-4">
                            <strong>{{ __('translation.date') }}</strong><br>
                            {{ \App\Helpers\Settings::formatDate($purchase->created_at) }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <form method="GET" target="_blank" action="{{ route('admin.purchases.barcodePreview', \App\Helpers\Settings::getEncodeCode($purchase->id)) }}">

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-end">
                        <h4 class="card-title mb-0">{{ __('translation.purchase') }} {{ __('translation.product') }}</h4>
                        <div class="d-flex align-items-end gap-2">
                            <div style="width:120px;">
                                <label>{{ __('translation.copies') }}</label>
                                <input type="number" class="form-control" name="copies" min="1" value="1">
                            </div>
                            <button class="btn btn-primary"><i class="mdi mdi-printer"></i> {{ __('translation.print_preview') }}</button>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary"><i class="mdi mdi-arrow-left"></i> {{ __('translation.back') }}</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th width="40"><input type="checkbox" id="checkAll" checked></th>
                                        <th>{{ __('translation.product') }}</th>
                                        <th>{{ __('translation.barcode') }}</th>
                                        <th width="80">{{ __('translation.quantity') }}</th>
                                        <th width="120">{{ __('translation.tracking') }}</th>
                                        <th width="130">{{ __('translation.print_qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->items as $item)
                                        <tr>
                                            <td><input type="checkbox" class="itemCheck" name="items[]" value="{{ $item->id }}" checked></td>
                                            <td>{{ $item->masterItem->name }}</td>
                                            <td>
                                                @if($item->trackings->isNotEmpty())
                                                    {!! DNS1D::getBarcodeSVG($item->trackings->first()->barcode, 'C128') !!}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td> {{ $item->trackings->count() }}</td>
                                            <td><span class="badge bg-info"> {{ \App\Helpers\Settings::getDataTitle($item->tracking_type) }} </span></td>
                                            <td><input type="number" class="form-control" name="print_qty[{{ $item->id }}]" min="1" max="{{ $item->trackings->count() }}" value="{{ $item->trackings->count() }}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection


@section('script')
    <script>
        $('#checkAll').on('change', function () {
            $('.itemCheck').prop('checked', $(this).is(':checked'));
        });
        $('.itemCheck').on('change', function () {
            $('#checkAll').prop(
                'checked',
                $('.itemCheck:checked').length === $('.itemCheck').length
            );
        });
    </script>
@endsection