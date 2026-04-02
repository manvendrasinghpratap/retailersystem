@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}}  @lang('translation.listing')
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
                        'pdfId' =>'downloadproductpdf',    
                        'pdfRoute' => route('menu.pdf'),
                        'pdfClass' => 'downloadproductpdf',
                        'csvId' =>'downloadproductcsv',    
                        'csvRoute' => route('menu.csv'),
                        'csvClass' => 'downloadproductcsv',
                        ])                 
                    </div>
                </div>
                <div class="card-body">
                    <form name="productmenuitemfilterform" id="productmenuitemfilterform" method="GET">
                        <div class="row">
                            <x-select-dropdown name="menu_type_id" label="Category" :options="$menuCategories?? []" :selected="request()->get('menu_type_id') ?? ''" class="menu_type_id menucategory"  mainrows='3'/>
                            <x-text-input name="productname" label="Product Name" value="{{ request()->get('productname') ?? '' }}" class="" mainrows='3'/>
                            <x-select-dropdown name="is_active" label="Status" :options="$status" :selected="request()->get('is_active') ?? ''" class="is_active accountstatus"  mainrows='2'/>
                            <x-button submitText="Filter" resetText="Reset" url="{{ route($breadcrumb['route']??'') }}" isbutton="1" iscancel="1" mainrows="2"/> 
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                {{-- <div class="card-header">
                    <h4 class="card-title">{{array_key_exists('routeTitle',$breadcrumb)?$breadcrumb['routeTitle']:''}}</h4>
                </div> --}}
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table id="datatable-buttons-" class="table table-striped align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{__('translation.category')}}</th>
                                <th>{{__('translation.product')}}</th>
                                <th>{{__('translation.image')}}</th>
                                <th>{{__('translation.ngn')}}  {{__('translation.regular').' '. __('translation.price')}}</th>
                                <th>{{__('translation.status')}}</th>
                                <th>{{__('translation.createdat')}}</th>
                                <th>{{__('translation.action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($menuList as $menu)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ @$menu->menuType->type }}</td>
                                <td>{{ $menu->title }}</td>
                                <td><img src="{{ asset('uploads/menu/small/'.$menu->image) }}" alt="Image"></td>
                                <td><a data-id="{{ $menu->id }}" data-fieldtobeupdate = "price" data-oldprice="{{ $menu->price }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top" href="javascript:void(0);"
                                                class="updateprice">{{__('translation.ngn')}}  {{ \App\Helpers\Settings::getcustomnumberformat($menu->price) }}</a>
                                </td>
                                <td>
                                    <input type="checkbox" id="switch3{{$menu->id}}" class="changestatus" data-id="{{ $menu->id }}" data-url="{{ route('menu.statusUpdate') }}" switch="bool"  @if($menu->status==1) checked @endif/>
                                    <label for="switch3{{$menu->id}}" data-on-label="Yes" data-off-label="No"></label>
                                </td>
                                <td>{{ App\Helpers\Settings::getFormattedDatetime($menu->created_at)}}</td>
                                <td>
                                    <x-href-input name="edit" label="Edit"  required href="{{ route('menu.edit',['id' => \App\Helpers\Settings::getEncodeCode($menu->id)]) }}" />
                                    <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData"  data-deleteid="{{ $menu->id }}"  data-routeurl="{{ route('menu.destroy') }}"/> 
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.productspdf', 'data-downloadroutepdf');
    });
</script>
@endsection
