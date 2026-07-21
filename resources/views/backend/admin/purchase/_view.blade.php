<div class="p-2">

    <!-- ===== Purchase Info ===== -->
    <div class="mb-3">

        <div class="row">
            <div class="col-6">
                <small class="text-muted">{{ __('translation.purchase_no') }}</small>
                <div><strong>{{ $purchase->purchase_no }}</strong></div>
            </div>

            <div class="col-6 text-end">
                <small class="text-muted">{{ __('translation.date') }}</small>
                <div>
                    {{ \App\Helpers\Settings::getFormattedDatetime($purchase->created_at) }}
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <small class="text-muted">{{ __('translation.vendor') }}</small>
                <div>{{ $purchase->vendor->company_name ?? 'N/A' }}</div>
                <div>{{ $purchase->vendor->name ?? 'N/A' }}</div>
                <div>{{ $purchase->vendor->address ?? 'N/A' }}</div>
                <div>{{ __('translation.phone') }}:{{ $purchase->vendor->phone ?? 'N/A' }}</div>
            </div>

            <div class="col-6 text-end">
                <small class="text-muted">{{ __('translation.warehouse') }}</small>
                @if($purchase->warehouse->staff)
                    <div>{{ $purchase->warehouse->staff->name ?? '--' }}</div>
                @endif
                <div>{{ $purchase->warehouse->name ?? 'N/A' }}</div>
                <div>{{ $purchase->warehouse->address ?? 'N/A' }}</div>
                <div>{{ $purchase->warehouse->phone ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
    <hr class="my-2">
    <!-- ===== Items Table ===== -->
    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
        <table class="table table-sm table-bordered- mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('translation.product') }}</th>
                    <th width="80" class="text-center">{{ __('translation.quantity') }}</th>
                    <th width="120" class="text-end">{{ __('translation.price') }}</th>
                    <th width="20%" class="text-end">{{ __('translation.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchase->items as $item)
                    <tr>
                        <td>{{ $item->masterItem->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ __('translation.currency') }}{{ \App\Helpers\Settings::getcustomnumberformat($item->cost_price) }}</td>
                        <td class="text-end fw-bold">{{ __('translation.currency') }}{{ \App\Helpers\Settings::getcustomnumberformat($item->total) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">{{__('translation.no_items_found')}}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <hr class="my-2">

    <!-- ===== Total Section ===== -->
    <div class="d-flex justify-content-end">
        <div class="text-end">
            <div class="fs-6 text-muted">
                {{ __('translation.total') }}
            </div>
            <div class="fs-5 fw-bold text-primary">
                {{ __('translation.currency') }}
                {{ \App\Helpers\Settings::getcustomnumberformat($purchase->total) }}
            </div>
        </div>
    </div>
</div>