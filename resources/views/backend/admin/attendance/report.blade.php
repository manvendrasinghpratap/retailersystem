@extends('backend.layouts.master-horizontal')

@section('title')
    {{ array_key_exists('title', $breadcrumb) ? $breadcrumb['title'] : 'Monthly Attendance Report' }}
@endsection

@section('content')

    @include('backend.components.breadcrumb')

    {{-- ================= FILTER SECTION ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title d-inline-block">{{ __('translation.staff_month_wise_attendance') }}</h4>
                     <div class="d-inline-block">
                        @include('backend.components.exportpdfcsv', [
                        'pdfId' =>'downloadattendancepdf',    
                        'pdfRoute' => route('attendance.exportPdf'),
                        'pdfClass' => 'downloadattendancepdf',
                        'csvId' =>'downloadattendancecsv',    
                        'csvRoute' => route('attendance.exportCsv'),
                        'csvClass' => 'downloadattendancecsv',
                        ])                 
                    </div>      
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-3">
                                <label>{{ __('translation.month') }}</label>
                                <input type="month" name="month" value="{{ request('month', $month ?? date('Y-m')) }}" class="form-control">
                            </div>
                            @if(auth()->user()->isStaff())
                                <x-select-dropdown name="staff_id" label="{{ __('translation.staff_name') }}" :options="$staffdropdown ?? []" :selected="request('staff_id') ?? ''" class="staff" mainrows="2" />
                            @endif
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

    {{-- ================= MONTH WISE TABLE ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{ __('translation.attendance_calendar_view') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive overflowx">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('translation.staff_name') }}</th>
                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        <th class="text-center">{{ $d }}</th>
                                    @endfor
                                    <th>P</th>
                                    <th>A</th>
                                    <th>H</th>
                                    <th>L</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffs as $staff)
                                    <tr>
                                        <td class="fw-bold">
                                            {{ $staff->name }}
                                        </td>
                                        @php
                                            $present = 0;
                                            $absent = 0;
                                            $half = 0;
                                            $leave = 0;
                                        @endphp
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            @php
                                                $date = $year . '-' . sprintf('%02d', $monthNo) . '-' . sprintf('%02d', $d);
                                                $row = $attendance[$staff->id][$date] ?? null;
                                                $status = $row->status ?? '';
                                                $text = '';
                                                $class = '';
                                                if ($status == 'Present') {
                                                    $text = 'P';
                                                    $class = 'bg-success text-white';
                                                    $present++;
                                                } elseif ($status == 'Absent') {
                                                    $text = 'A';
                                                    $class = 'bg-danger text-white';
                                                    $absent++;
                                                } elseif ($status == 'Half Day') {
                                                    $text = 'H';
                                                    $class = 'bg-warning';
                                                    $half++;
                                                } elseif ($status == 'Leave') {
                                                    $text = 'L';
                                                    $class = 'bg-info text-white';
                                                    $leave++;
                                                }
                                            @endphp
                                            <td class="text-center {{ $class }}">
                                                {{ $text }}
                                            </td>
                                        @endfor
                                        <td class="fw-bold text-success">
                                            {{ $present }}
                                        </td>
                                        <td class="fw-bold text-danger">
                                            {{ $absent }}
                                        </td>
                                        <td class="fw-bold text-warning">
                                            {{ $half }}
                                        </td>
                                        <td class="fw-bold text-info">
                                            {{ $leave }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success">P = Present</span>
                        <span class="badge bg-danger">A = Absent</span>
                        <span class="badge bg-warning text-dark">H = Half Day</span>
                        <span class="badge bg-info">L = Leave</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
       setupPdfDownload('.downloadattendancepdf', 'data-downloadroutepdf');
       setupPdfDownload('.downloadattendancecsv', 'data-downloadroutepdf');
    });
</script>
@endsection