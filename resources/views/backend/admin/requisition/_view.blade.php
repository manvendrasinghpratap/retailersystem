<div class="row mb-3">
    <div class="col-md-4">
        <strong>{{ __('translation.requisition_no') }}:</strong>
        {{ $requisition->requisition_no }}
    </div>

    <div class="col-md-4">
        <strong>{{ __('translation.from_warehouse') }}:</strong>
        {{ $requisition->fromWarehouse->name ?? '-' }}
    </div>

    <div class="col-md-4">
        <strong>{{ __('translation.for_store') }}:</strong>
        {{ $requisition->store->name ?? '-' }}
    </div>
    <div class="col-md-4 mt-3">
        <strong>{{ __('translation.requester') }}:</strong>
        {{ $requisition->creator->name ?? '-' }}
    </div>
    <div class="col-md-4 mt-3">
        <strong>{{ __('translation.date') }}:</strong>
        {{ \App\Helpers\Settings::getFormattedDatetime($requisition->created_at) }}
    </div>

</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('translation.product') }}</th>
            <th>{{ __('translation.quantity') }}</th>
            <th>{{ __('translation.accepted_by') }}</th>
            <th>{{ __('translation.action') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($requisition->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->masterItem->name ?? '-' }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->acceptedBy->name ?? 'NO' }}</td>
                <td>
                    @if($item->accepted_by == null && $requisition->status == 1)
                        <x-href-input action="no-barcode" name="no-barcode" label="" class="no-barcode" data-id="{{ \App\Helpers\Settings::getEncodeCode($item->id) }}" href="javascript:void(0);" />
                        <x-href-input action="barcode" name="barcode" label="" class="barcode" data-id="{{ \App\Helpers\Settings::getEncodeCode($item->id) }}" href="javascript:void(0);" />
                    @elseif($item->accepted_by == null && $requisition->status == 0)
                        <span class="badge bg-danger">{{ __('translation.cancelled') }}</span>
                    @elseif($item->accepted_by != null && $requisition->status == 1)
                        <span class="badge bg-success">{{ __('translation.accepted') }}</span>
                    @else
                        <span class="badge bg-success">{{ __('translation.moved_to_store') }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">
                    {{ __('translation.no_data_found') }}
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-3">
    <h5 class="text-end pt-3" style="padding: 5px;">
        <strong>{{ __('translation.total_qty') }} : {{ number_format($requisition->total_qty, 2) }}</strong>
    </h5>
</div>

<script>

    // ===============================
    // NO BARCODE
    // ===============================
    $(document).on('click', '.no-barcode', function () {

        let id = $(this).data('id');

        Swal.fire({
            title: 'Brand Without Barcode?',
            text: 'This item will use auto generated barcode.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Redirecting...',
                    text: 'Opening in new tab',
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                });

                // redirect to another URL in new tab
                window.open(
                    "{{ route('admin.no-barcode') }}?requisition_item_id=" + id,
                    '_blank'
                );
            }
        });
    });


    // ===============================
    // WITH BARCODE
    // ===============================
    $(document).on('click', '.barcode', function () {

        let id = $(this).data('id');

        Swal.fire({
            title: 'Brand With Barcode?',
            text: 'Barcode labels will be printed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Redirecting...',
                    text: 'Opening in new tab',
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                });

                // redirect to another URL in new tab
                window.open(
                    "{{ route('admin.barcode') }}?requisition_item_id=" + id,
                    '_blank'
                );
            }
        });
    });

</script>