@extends('backend.layouts.master-horizontal')
@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')

    <!-- FILTER -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                     <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloadstockreceivinghistorypdf',    
                        'pdfRoute' => route('admin.requisitions.pending.posting.history.pdf'),
                        'pdfClass' => 'downloadstockreceivinghistorypdf',
                        'csvId' =>'downloadstockreceivinghistorycsv',    
                        'csvRoute' => route('admin.requisitions.pending.posting.history.csv'),
                        'csvClass' => 'downloadstockreceivinghistorycsv',
                        ])                 
                    </div>      
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="requisition_no" label="{{ __('translation.requisition_no') }}" :value="request('requisition_no')" mainrows="2" />
                            <x-select-dropdown name="from_warehouse_id" label="{{ __('translation.from_warehouse') }}" :options="$warehouses" :selected="request('from_warehouse_id')" mainrows="2" class="warehouse" />
                            <!-- <x-select-dropdown name="for_store_id" label="{{ __('translation.for_store') }}" :options="$stores" :selected="request('for_store_id')" mainrows="2" class="store" /> -->
                            <x-text-input name="from_date" label="{{ __('translation.from_date') }}" value="{{ request('from_date') ?? \App\Helpers\Settings::getFormattedDatetime($today) }}" mainrows="2" class="flatdatepickr" />
                            <x-text-input name="to_date" label="{{ __('translation.to_date') }}" value="{{ request('to_date') }}" mainrows="2" class="flatdatepickr" />
                        </div>
                        <!-- Buttons -->
                        <div class="row mt-2">
                            <div class="col-12 d-flex justify-content-end">
                                <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" value="Filter" class="me-2" />
                                <x-filter-href-button name="reset" href="{!! route('admin.requisitions.pending.posting.history') !!}" label="{{ __('translation.reset') }}" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- LIST -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ $breadcrumb['title'] ?? '' }}
                        {{ __('translation.listing') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.requisition_no') }}</th>
                                    <th>{{ __('translation.from_warehouse') }}</th>
                                    <!-- <th>{{ __('translation.for_store') }}</th> -->
                                    <th>{{ __('translation.product') }}</th>
                                    <th>{{ __('translation.quantity') }}</th>
                                    <th>{{ __('translation.status') }}</th>
                                    <th>{{ __('translation.received_by') }}</th>
                                    <th>{{ __('translation.received_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->requisition->requisition_no ?? '-' }}</td>
                                        <td>{{ $item->requisition->fromWarehouse->name ?? '-' }}</td>
                                        <!-- <td>{{ $item->requisition->store->name ?? '-' }}</td> -->
                                        <td>{{ $item->masterItem->name ?? '-' }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>
                                            @if($item->accepted_by)
                                                <span class="badge bg-success">{{ __('translation.posted') }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ __('translation.pending_posting') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!is_null($item->accepted_by))
                                                {{$item->acceptedBy->name}}
                                            @else
                                                {{"-"}}
                                            @endif
                                        </td>
                                        <td>{{ \App\Helpers\Settings::getFormattedDatetime($item->updated_at) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            {{ __('translation.no_data_found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- PAGINATION -->
                    <div class="right user-navigation" style="float:right">{!! $items->appends(request()->input())->links() !!}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloadstockreceivinghistorypdf', 'data-downloadstockreceivinghistorypdf');
       setupPdfDownload('.downloadstockreceivinghistorycsv', 'data-downloadstockreceivinghistorycsv');
    });
</script>
@endsection