@extends('backend.layouts.master-horizontal')
@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection
@section('content')
    @include('backend.components.breadcrumb')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ __('translation.account_settings') }}</h4>
                    @can('account-settings.create')
                        <a href="{{ route('admin.account-settings.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> {{ __('translation.add_new') }}
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>{{ __('translation.module') }}</th>
                                    <th>{{ __('translation.total_settings') }}</th>
                                    <th>{{ __('translation.updated_at') }}</th>
                                    <th width="12%" class="text-center">{{ __('translation.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountSettings as $index => $setting)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $setting->module)) }}</td>
                                        <td>{{ count($setting->settings ?? []) }}<br>
                                            {{ implode(', ', array_keys($setting->settings ?? [])) }}
                                        </td>
                                        <td>{{ App\Helpers\Settings::getFormattedDatetime($setting->updated_at)}}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.account-settings.edit', $setting->id) }}" class="btn btn-sm btn-primary" title="{{ __('translation.edit') }}">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            @can('account-settings.edit')
                                                <a href="{{ route('admin.account-settings.edit', $setting->id) }}" class="btn btn-sm btn-primary" title="{{ __('translation.edit') }}">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4"> {{ __('translation.no_records_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection