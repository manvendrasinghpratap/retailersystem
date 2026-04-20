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
                        'pdfId' =>'downloadcategorypdf',    
                        'pdfRoute' => route('admin.category.exportPdf'),
                        'pdfClass' => 'downloadcategorypdf',
                        'csvId' =>'downloadcategorycsv',    
                        'csvRoute' => route('admin.category.exportCsv'),
                        'csvClass' => 'downloadcategorycsv',
                        ])                 
                    </div>  
                </div>
                <div class="card-body">
                    <form name="cartlistingform" id="cartlistingform" method="GET">
                        <div class="row">
                            <x-text-input name="categoryname" label="{{ __('translation.category_name') }}" value="{{ request()->get('categoryname') ?? '' }}" class="" mainrows='3' />
                            <x-select-dropdown name="is_active" label="{{ __('translation.status') }}" :options="$status ?? []" :selected="request()->get('is_active') ?? ''" class="is_active accountstatus" mainrows="2" />
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
                                    <th>{{__('translation.category_name')}}</th>
                                    <th>{{__('translation.description')}}</th>
                                    <th>{{__('translation.slug')}}</th>
                                    <th>{{__('translation.image')}}</th>
                                    <th>{{__('translation.status')}}</th>
                                    <th>{{__('translation.createdat')}}</th>
                                    <th>{{__('translation.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($categories))
                                    @foreach($categories as $categoriesType)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $categoriesType->name }}</td>
                                            <td>{{ substr($categoriesType->description, 0, 50) }}</td>
                                            <td>{{ $categoriesType->slug }}</td>
                                            <td>
                                                <img src="{{ (!empty($categoriesType->image) && file_exists(public_path('uploads/categories/small/' . $categoriesType->image))) ? asset('uploads/categories/small/' . $categoriesType->image) : asset('assets/images/no-image.png') }}" width="80" height="60" alt="Category Image">
                                            </td>
                                            <td>
                                                @if($categoriesType->status == 1)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ App\Helpers\Settings::getFormattedDatetime($categoriesType->created_at)}}</td>
                                            <td>
                                                <x-href-input name="edit" label="Edit" required href="{{ route('admin.categories.edit', ['id' => \App\Helpers\Settings::getEncodeCode($categoriesType->id)]) }}" />
                                                <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($categoriesType->id) }}" data-routeurl="{{ route('admin.categories.softdelete', $categoriesType->id) }}" />
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center">No categories available</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if(!empty($categories) && $categories->count() > 0)
                        <div class="right user-navigation right">{!! $categories->appends(request()->input())->links() !!}</div>
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