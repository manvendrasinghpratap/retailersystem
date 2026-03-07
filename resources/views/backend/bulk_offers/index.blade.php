@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title',$breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection

@section('css')
@endsection

@section('content')
@include('backend.components.breadcrumb')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>  
                    <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloadbulkofferspdf',    
                        'pdfRoute' => route('bulk-offers.bulkofferspdf'),
                        'pdfClass' => 'downloadbulkofferspdf',
                        'csvId' =>'downloadbulkofferscsv',    
                        'csvRoute' => route('bulk-offers.bulkofferscsv'),
                        'csvClass' => 'downloadbulkofferscsv',
                        ])                 
                    </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : '') }}">
                    <div class="row">
                        <div class="col-xl-3 col-md-3">
                            <label for="menus_id">@lang('translation.product')</label>
                            <select name="menus_id" class="form-control products">
                                <option value="">@lang('translation.select_menu')</option>
                                @foreach($menus as $menu)
                                    <option value="{{ $menu->id }}" {{ request('menus_id') == $menu->id ? 'selected' : '' }}>
                                        {{ $menu->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-3 col-md-3">
                            <label for="is_active">@lang('translation.status')</label>
                            <select name="is_active" class="form-control accountstatus">
                                <option value="">@lang('translation.all')</option>
                                <option value="1" {{ request('is_active') === "1" ? 'selected' : '' }}>@lang('translation.active')</option>
                                <option value="0" {{ request('is_active') === "0" ? 'selected' : '' }}>@lang('translation.inactive')</option>
                            </select>
                        </div>
                        <div class="col-xl-3 col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">@lang('translation.search')</button>
                            <a href="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : '') }}" class="btn btn-secondary">@lang('translation.reset')</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-container">
                    <table id="datatable-buttons-" class="table dt-responsive table-bordered table-hover table-striped table-nowrap w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('translation.product')</th>
                                <th>@lang('translation.buy_quantity')</th>
                                <th>@lang('translation.free_menu')</th>
                                <th>@lang('translation.free_quantity')</th>
                                <th>@lang('translation.offer_start_date')</th>
                                <th>@lang('translation.offer_end_date')</th>
                                <th>@lang('translation.status')</th>
                                <th>@lang('translation.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bulkOffers as $offer)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $offer->menu->title ?? '-' }}</td>
                                    <td>{{ $offer->buy_quantity }}</td>
                                    <td>{{ $offer->freeMenu->title ?? '-' }}</td>
                                    <td>{{ $offer->free_quantity }}</td>
                                    <td>{{ $offer->start_date ?? '-' }}</td>
                                    <td>{{ $offer->end_date ?? '-' }}</td>
                                    <td>
                                        @if($offer->is_active)
                                            <span>@lang('translation.active')</span>
                                        @else
                                            <span>@lang('translation.inactive')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-href-input name="edit" label="Edit"  required href="{{ route('bulk-offers.edit',['id' => \App\Helpers\Settings::getEncodeCode($offer->id)]) }}" />
                                    <x-deletehref-input 
                                    name="DeleteButton" 
                                    :label="__('translation.delete')" 
                                    required 
                                    href="javascript:void(0)" 
                                    class="deleteData"  
                                    data-deleteid="{{ $offer->id }}"  
                                    data-routeurl="{{ route('bulk-offers.destroy') }}"
                                    />

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">@lang('translation.no_data_found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $bulkOffers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloadbulkofferspdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloadbulkofferscsv', 'data-downloadroutepdf');
    });
</script>
@endsection
