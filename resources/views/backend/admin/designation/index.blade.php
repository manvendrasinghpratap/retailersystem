@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection

@section('content')
    @include('backend.components.breadcrumb')

    {{-- FILTER SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.filter') }}</h4>
                     <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloaddesignationpdf',
                        'pdfRoute' => route('admin.designations.export.pdf'),
                        'pdfClass' => 'downloaddesignationpdf',
                        'csvId' =>'downloaddesignationcsv',
                        'csvRoute' => route('admin.designations.export.csv'),
                        'csvClass' => 'downloaddesignationcsv',
                        ])             
                    </div>      
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="name" label="{{ __('translation.designation') }}" value="{{ request()->get('name') }}" mainrows="3" />
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="request()->get('status')" mainrows="2" class="accountstatus" />
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" />
                                    <x-filter-href-button name="reset" href="{!! !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' !!}" label="Reset" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- LISTING SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
                        {{ __('translation.listing') }}
                    </h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('translation.designation') }}</th>
                                    <th>{{ __('translation.status') }}</th>
                                    <th>{{ __('translation.createdat') }}</th>
                                    <th>{{ __('translation.actions') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @if(!empty($designations) && $designations->count() > 0)
                                    @foreach($designations as $designation)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $designation->name }}</td>
                                            <td>
                                                @if($designation->status == '1')
                                                    <span class="badge bg-info">{{ __('translation.active') }}</span>
                                                @else
                                                    <span class="badge bg-primary">{{ __('translation.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $designation->created_date }}</td>
                                            <td>
                                                <x-href-input name="edit" label="Edit" required href="{{ route('admin.designations.edit', ['id' => \App\Helpers\Settings::getEncodeCode($designation->id)]) }}" />
                                                <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($designation->id) }}" data-routeurl="{{ route('admin.designations.softdelete', $designation->id) }}" />
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="10" class="text-center">{{ __('translation.no_designations_available') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if(!empty($designations) && $designations->count() > 0)
                        <div class="right user-navigation">
                            {!! $designations->appends(request()->input())->links() !!}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloaddesignationpdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloaddesignationcsv', 'data-downloadroutepdf');
    });
</script>
@endsection