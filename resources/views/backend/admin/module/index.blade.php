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
                            'pdfId' => 'downloadmodulepdf',
                            'pdfRoute' => route('admin.modules.exportPdf'),
                            'pdfClass' => 'downloadmodulepdf',
                            'csvId' => 'downloadmodulecsv',
                            'csvRoute' => route('admin.modules.exportCsv'),
    'csvClass' => 'downloadmodulecsv',
                        ])                
                    </div>      
                </div>
                <div class="card-body">
                   <form method="GET">
                        <div class="row">
                            <x-text-input name="name" label="{{ __('translation.module') }}" value="{{ request('name') }}" mainrows="3" />
                            <x-text-input name="slug" label="{{ __('translation.slug') }}" value="{{ request('slug') }}" mainrows="3" />
                            <x-select-dropdown name="status" label="{{ __('translation.status') }}" :options="config('constants.accountstatus')" :selected="request('status')" mainrows="2" class="accountstatus" />
                            <div class="col-xl-2 col-md-2">
                                <div class="form-group mb-3">
                                    <label class="d-inline-block w-100">&nbsp;</label>
                                    <x-filter-submit-button name="submit" label="{{ __('translation.filter') }}" />
                                    <x-filter-href-button name="reset" href="{{ !empty($breadcrumb['route2']) ? route($breadcrumb['route2']) : '' }}" label="{{ __('translation.reset') }}" />
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
                                    <th>{{ __('translation.module') }}</th>
                                    <th>{{ __('translation.slug') }}</th>
                                    <th>{{ __('translation.status') }}</th>
                                    <th>{{ __('translation.createdat') }}</th>
                                    <th>{{ __('translation.actions') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @if(!empty($modules) && $modules->count() > 0)
                                    @foreach($modules as $module)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $module->name }}</td>
                                            <td>{{ $module->slug }}</td>
                                            <td>
                                                @if($module->status == 1)
                                                    <span class="badge bg-success">{{ __('translation.active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('translation.inactive') }}</span>   
                                                @endif
                                            </td>
                                            <td>{{ \App\Helpers\Settings::getFormattedDatetime($module->created_at) }}</td>
                                            <td>
                                                <x-href-input name="edit" label="Edit" required href="{{ route('admin.modules.edit', ['id' => \App\Helpers\Settings::getEncodeCode($module->id)]) }}" />
                                                <x-deletehref-input name="DeleteButton" label="Delete" required href="javascript:void(0)" class="deleteData" data-deleteid="{{ \App\Helpers\Settings::getEncodeCode($module->id) }}" data-routeurl="{{ route('admin.modules.softdelete', $module->id) }}" />
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center">{{ __('translation.no_data_found') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if(!empty($modules) && $modules->count() > 0)
                        <div class="right user-navigation">
                            {!! $modules->appends(request()->input())->links() !!}
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
       setupPdfDownload('.downloadmodulepdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloadmodulecsv', 'data-downloadroutepdf');
    });
</script>
@endsection