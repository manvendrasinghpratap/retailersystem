@extends('backend.pdf.layouts.master')
@section('title', array_key_exists('heading', $pdfHeaderdata) ? $pdfHeaderdata['heading'] : '')
@section('content')
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

    <div class="mt-3">
        <span class="badge bg-success">P = Present</span>
        <span class="badge bg-danger">A = Absent</span>
        <span class="badge bg-warning text-dark">H = Half Day</span>
        <span class="badge bg-info">L = Leave</span>
    </div>
@endsection