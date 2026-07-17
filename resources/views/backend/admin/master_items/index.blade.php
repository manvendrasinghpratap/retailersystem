@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}}
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
                            'showPdf'=>false,
                            'showCsv'=>false,
                            'pdfId' =>'downloadmasteritempdf',    
                            'pdfRoute' => route('admin.master_items.exportPdf'),
                            'pdfClass' => 'downloadmasteritempdf',
                            'csvId' =>'downloadmasteritemcsv',    
                            'csvRoute' => route('admin.master_items.exportCsv'),
                            'csvClass' => 'downloadmasteritemcsv',
                        ])                 
                    </div>
                </div>
                <div class="card-body">
                    <form name="cartlistingform" id="cartlistingform" method="GET">
                        <div class="row">
                            <x-text-input name="item_name" label="Item Name / Code" value="{{ request('item_name') }}" mainrows="3" />
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="\Config::get('constants.accountstatus')" :selected="request('status')" class="accountstatus" mainrows="2" />
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" value="Filter" class="" />
                                    <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" class="" />
                                </div>
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
                <div class="card-header">
                    <h4 class="card-title">{{array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : ''}} {{__('translation.listing')}}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                <th>#</th>
                                <th>{{ __('translation.image') }}</th>
                                <th>{{ __('translation.name') }}</th>
                                <th>{{ __('translation.code') }}</th>
                                <th>{{ __('translation.description') }}</th>
                                <th>{{ __('translation.status') }}</th>
                                <th>{{ __('translation.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <img src="{{ (!empty($item->image) && file_exists(public_path('uploads/master_item/small/' . $item->image))) ? asset('uploads/master_item/small/' . $item->image) : asset('assets/images/no-image.png') }}" width="80" height="60" alt="Master Item Image">
                                </td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->description }}</td>
                                <td>
                                    <span class="badge {{ $item->status ? 'bg-success':'bg-danger' }}">
                                        {{ $item->status ? __('translation.active'):__('translation.inactive') }}
                                    </span>
                                </td>
                                <td>
                                    <x-href-input name="edit" label="Edit" required href="{{ route('admin.master_items.edit', ['id' => \App\Helpers\Settings::getEncodeCode($item->id)]) }}" />
                                    <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($item->id) }}" data-routeurl="{{ route('admin.master_items.delete', $item->id) }}" />
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(!empty($items) && $items->count() > 0)
                        <div class="right user-navigation right">{!! $items->appends(request()->input())->links() !!}</div>
                    @endif
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            setupPdfDownload('.downloadcategorypdf', 'data-downloadroutepdf');
            setupPdfDownload('.downloadcategorycsv', 'data-downloadroutepdf');
        });
    </script>
@endsection


