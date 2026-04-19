@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
@endsection

@section('content')
    @include('backend.components.breadcrumb')
    {{-- ================= FILTER SECTION ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">
                        {{ __('translation.filter') }}
                    </h4>
                </div>

                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <x-text-input name="date" label="{{ __('translation.date') }}" value="{{ \App\Helpers\Settings::formatDate($date ?? '', Config::get('constants.dateformat.slashdmyonly')) }}" class="flatdatepickr" mainrows="3" />
                            <div class="col-xl-3 col-md-3">
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

    {{-- ================= LISTING SECTION ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title">
                        {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : '' }}
                    </h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('attendance.store') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <div class="table-responsive overflowx">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('translation.staff_name') }}</th>
                                        <th>{{ __('translation.present') }}</th>
                                        <th>{{ __('translation.absent') }}</th>
                                        <th>{{ __('translation.half_day') }}</th>
                                        <th>{{ __('translation.leave') }}</th>
                                        <th>{{ __('translation.check_in') }}</th>
                                        <th>{{ __('translation.check_out') }}</th>
                                        <th>{{ __('translation.hours') }}</th>
                                        <th>{{ __('translation.remarks') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(!empty($staffs) && $staffs->count() > 0)
                                        @foreach($staffs as $staff)
                                            @php
                                                $row = $attendance[$staff->id] ?? null;
                                                $status = $row->status ?? 'Present';
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $staff->name }}</td>
                                                <td><input type="radio" name="attendance[{{ $staff->id }}]" value="Present" {{ $status == 'Present' ? 'checked' : '' }}></td>
                                                <td><input type="radio" name="attendance[{{ $staff->id }}]" value="Absent" {{ $status == 'Absent' ? 'checked' : '' }}></td>
                                                <td><input type="radio" name="attendance[{{ $staff->id }}]" value="Half Day" {{ $status == 'Half Day' ? 'checked' : '' }}></td>
                                                <td><input type="radio" name="attendance[{{ $staff->id }}]" value="Leave" {{ $status == 'Leave' ? 'checked' : '' }}></td>
                                                <td>{{ !empty($row->check_in) ? \App\Helpers\Settings::userdatetime($row->check_in) : '' }}</td>
                                                <td>{{ !empty($row->check_out) ? \App\Helpers\Settings::userdatetime($row->check_out) : '' }}</td>
                                                <td>{{ $row->work_hours ?? 0 }}</td>
                                                <td><input type="text" name="remarks[{{ $staff->id }}]" value="{{ $row->remarks ?? '' }}" class="form-control"></td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10" class="text-center">
                                                {{ __('translation.no_staff_available') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group">
                            <x-form-buttons submitText="{{ __('translation.update_save') }}" resetText="{{ isset($breadcrumb['reset_route_title']) ? $breadcrumb['reset_route_title'] : 'Cancel' }}" url="{{ isset($breadcrumb['reset_route']) ? route($breadcrumb['reset_route']) : '#' }}" class="btn-success" />
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection