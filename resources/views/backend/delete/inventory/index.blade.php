@extends('backend.layouts.master-horizontal') {{-- matches your file --}}
@section('title', 'Stock Management')

@section('content')
@include('backend.components.breadcrumb')

<div class="row">
    <div class="col-12">
        <div class="card">
             <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>  
                <div class="d-inline-block">
                    @include('backend.components.exportpdfcsv', [
                    'pdfId' =>'downloaddailyStockPdf',    
                    'pdfRoute' => route('stockPdf'),
                    'pdfClass' => 'downloaddailyStockPdf',
                    'csvId' =>'downloaddailyStockCsv',    
                    'csvRoute' => route('stockCsv'),
                    'csvClass' => 'downloaddailyStockCsv',
                    ])                 
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : 'sales.index') }}" class="mb-3"> 
                    <div class="row">
                         <x-select-dropdown name="product_id" label="{{ __('translation.product') }}" :options="$products" :selected="request('product_id')" class="products" mainrows="4"/>
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
            <div class="card-body">
            <div class="table-responsive overflowx">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('translation.product')}}</th>
                            <th>{{ __('translation.quantity')}}</th>
                            <th>{{ __('translation.status')}}</th>
                            <th>{{ __('translation.updated_at')}}</th>
                            {{-- <th>{{ __('translation.action')}}</th> --}}
                        </tr>
                    </thead>
                    <tbody> 
                        @forelse($stocks as $stock)
                          <tr>
                              <td>{{ $loop->iteration }}</td>
                              <td>{{ $stock->product->title }}</td>
                              <td>{{ $stock->quantity }}</td>
                              <td>{{ ($stock->status && array_key_exists($stock->status,\Config::get('constants.accountstatus'))) ? \Config::get('constants.accountstatus')[$stock->status]:'' }}</td>
                              <td>{{ App\Helpers\Settings::getFormattedDate($stock->updated_at) }}</td>
                              {{-- <td>
                                <x-href-input name="edit" label="Edit"  required href="{{ route('stock.edit',['id' => \App\Helpers\Settings::getEncodeCode($stock->id)]) }}" />
                              </td> --}}
                          </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">@lang('translation.no_data_found')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="right user-navigation" style="float:right">{!! $stocks->appends(request()->input())->links() !!}</div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloaddailyStockPdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloaddailyStockCsv', 'data-downloadroutepdf');
    });
</script>
@endsection
