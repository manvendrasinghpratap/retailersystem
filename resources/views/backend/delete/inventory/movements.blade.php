@extends('backend.layouts.master-horizontal') {{-- matches your file --}}
@section('title', array_key_exists('title',$breadcrumb)?$breadcrumb['title']:'')

@section('content')
@include('backend.components.breadcrumb')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>               
                <div class="d-inline-block">
                    @include('backend.components.exportpdfcsv', [
                    'pdfId' =>'downloadmovementpdf',    
                    'pdfRoute' => route($pdfRoute),
                    'pdfClass' => 'downloadmovementpdf',
                    'csvId' =>'downloadmovementcsv',    
                    'csvRoute' => route($csvRoute),
                    'csvClass' => 'downloadmovementcsv',
                    ])                 
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route(array_key_exists('route',$breadcrumb) ? $breadcrumb['route'] : 'sales.index') }}" class="mb-3"> 
                    <div class="row">
                        <x-select-dropdown name="product_id" label="{{ __('translation.product') }}" :options="$products" :selected="request('product_id')" class="products" mainrows="2"/>
                        @if(!($adjustment ?? false))
                        <x-select-dropdown name="type" label="{{ __('translation.type') }}" :options="Config::get('constants.transactiontype')" :selected="request('type')" class="types" mainrows="1"/>
                        @endif
                        <x-date-input name="from_date" :label="__('translation.from_date')" value="{{ request()->get('from_date')?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickr" mainrows="2" data-mindate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-1 year'))) }}" data-maxdate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}"/>
                        <x-date-input name="to_date" :label="__('translation.to_date')" value="{{ request()->get('to_date')?? \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" class="flatdatepickrto " mainrows="2" data-mindate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d', strtotime('-1 year'))) }}" data-maxdate="{{ \App\Helpers\Settings::getFormattedDate(date('Y-m-d')) }}" />    
                        <x-select-dropdown name="user_id" label="{{ __('translation.staff') }}" :options="$users" :selected="request('user_id')" class="staff" mainrows="2"/>                 
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
                            <th>{{ __('translation.type')}}</th>
                            <th>{{ __('translation.quantity')}}</th>
                            <th>{{ __('translation.old')}} {{ __('translation.quantity')}}</th>
                            <th>{{ __('translation.new')}} {{ __('translation.quantity')}}</th>
                            <th>{{ __('translation.staff')}}</th>
                            <th>{{ __('translation.transaction')}} {{ __('translation.date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $m)
                           <tr>
                              <td>{{ $loop->iteration }}</td>
                              <td>{{ $m->product->title ?? 'N/A' }}</td>
                              <td title="{{$m->remarks}}" class="{{$m->remarks ? 'error': ''}}">{{ $m->type }}</td>
                              <td>{{ $m->quantity }}</td>
                              <td>{{ $m->old_quantity }}</td>
                              <td>{{ $m->new_quantity }}</td>
                              <td>{{ @$m->staff->staff_name }}</td>
                              <td>{{ App\Helpers\Settings::getFormattedDate($m->date)}}</td>
                              {{-- <td>{{ App\Helpers\Settings::getFormattedDatetime($m->created_at)}}</td> --}}
                           </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">@lang('translation.no_data_found')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="right user-navigation" style="float:right"> {{ $movements->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloadmovementpdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloadmovementcsv', 'data-downloadroutepdf');
    });
</script>
@endsection
