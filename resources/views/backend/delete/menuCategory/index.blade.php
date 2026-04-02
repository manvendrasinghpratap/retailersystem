@extends('backend.layouts.master-horizontal')
@section('title')
    {{array_key_exists('title',$breadcrumb)?$breadcrumb['title']:''}}
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
                        'pdfId' =>'categorypdf',    
                        'pdfRoute' => route('menu.category.pdf'),
                        'pdfClass' => 'categorypdf',
                        'csvId' =>'categorycsv',    
                        'csvRoute' => route('menu.category.csv'),
                        'csvClass' => 'categorycsv',
                        ])                 
                    </div>
                </div>
                <div class="card-body">
                    <form name="cartlistingform" id="cartlistingform" method="GET">
                        <div class="row">
                            {{-- <x-select-dropdown name="subscription_id" label="Subscription Plan" :options="$subscriptionPlan" :selected="request()->get('subscription_id') ?? ''" class="subscription_id"  mainrows='2'/> --}}
                            <x-text-input name="categoryname" label="Category" value="{{ request()->get('categoryname') ?? '' }}" class="" mainrows='3'/>
                            <x-select-dropdown name="is_active" label="Status" :options="$status" :selected="request()->get('is_active') ?? ''" class="is_active accountstatus"  mainrows='2'/>
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                	<label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="Filter" value="Filter" class=""/>
                                     <x-filter-href-button name="reset" href="{{ route(array_key_exists('route',$breadcrumb)?$breadcrumb['route']:'') }}" label="Reset" class=""/>
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
                {{-- <div class="card-header">
                    <h4 class="card-title">{{array_key_exists('routeTitle',$breadcrumb)?$breadcrumb['routeTitle']:''}}</h4>
                </div> --}}
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table  class="table table-striped align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{__('translation.category')}}</th>
                                <th>{{__('translation.image')}}</th>
                                <th>{{__('translation.description')}}</th>
                                <th>{{__('translation.status')}}</th>
                                <th>{{__('translation.createdat')}}</th>
                                <th>{{__('translation.action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($menuTypeList as $menuType)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $menuType->type }}</td>
                                <td><img src="{{ asset('uploads/menu_type/small/'.$menuType->image) }}" alt="Image"></td>
                                <td>{{ substr($menuType->description,0,50) }}</td>
                                <td>
                                    <input type="checkbox" id="switch3{{$menuType->id}}" class="changestatus" data-id="{{ $menuType->id }}" data-url="{{ route('menu.category.statusUpdate') }}" switch="bool"  @if($menuType->status==1) checked @endif/>
                                    <label for="switch3{{$menuType->id}}" data-on-label="Yes" data-off-label="No"></label>
                                </td>
                                <td>{{ App\Helpers\Settings::getFormattedDatetime($menuType->created_at)}}</td>
                                <td>
                                    <x-href-input name="edit" label="Edit"  required href="{{ route('menu.category.edit',['id' => \App\Helpers\Settings::getEncodeCode($menuType->id)]) }}" />
                                    <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData"  data-deleteid="{{ $menuType->id }}"  data-routeurl="{{ route('menu.category.destroy') }}"/> 
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                     <div class="right user-navigation right">{!! $menuTypeList->appends(request()->input())->links() !!}</div>
                </div>
            </div>
            <!-- end cardaa -->
        </div> <!-- end col -->
    </div> <!-- end row -->
@endsection
@section('script')
     <script src="{{ URL::asset('assets/js/menucategory.js?id='.Config::get('app.css_refresh')) }}"></script>
     <!------------- js wtritten in ajax.blade file frontend/component-->
    <script>
        $(document).ready(function() {
        setupPdfDownload('.categorypdf', 'data-downloadroutepdf');
        setupPdfDownload('.categorycsv', 'data-downloadroutepdf');
        });
    </script>
@endsection
