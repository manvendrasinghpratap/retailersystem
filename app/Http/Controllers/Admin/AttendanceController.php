<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Helpers\Settings;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceController extends Controller
{
    protected $breadcrumbAttendance;
    protected $breadcrumbAttendanceReport;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAttendance = [
            'title' => __('translation.attendance'),
            'route1' => "attendance.report",
            'route1Title' => __('translation.monthly_report'),

            'route2Title' => __('translation.attendance'),
            'route2' => 'attendance.index',

            'route3Title' => __('translation.report'),
            'route3' => 'attendance.report',

            'reset_route' => 'attendance.index',
            'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbAttendanceReport = [
            'title' => __('translation.attendance'),
            'route1' => "attendance.index",
            'route1Title' => __('translation.attendance'),

            'route2Title' => __('translation.attendance'),
            'route2' => 'attendance.report',

            'route3Title' => __('translation.report'),
            'route3' => 'attendance.report',

            'reset_route' => 'attendance.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    /**
     * Attendance Page
     */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbAttendance;

        $date = $request->date
            ? Settings::formatDate($request->date, 'Y-m-d')
            : date('Y-m-d');

        $staffs = Settings::userScope(
            User::where('status', 1),
            'id'
        )->orderBy('name')->get();

        $attendance = Settings::userScope(
            Attendance::whereDate('date', $date),
            'staff_id'
        )->get()->keyBy('staff_id');

        return view('backend.admin.attendance.index', compact(
            'breadcrumb',
            'date',
            'staffs',
            'attendance'
        ));
    }

    public function exportPdf(Request $request)
    {
        $request->merge(['pdf' => 1]);
        return $this->report($request);
    }
    public function exportCsv(Request $request)
    {
        $request->merge(['csv' => 1]);
        return $this->report($request);
    }

    /**
     * Save Manual Attendance
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required',
            'attendance' => 'required|array'
        ]);

        foreach ($request->attendance as $staffId => $status) {

            Attendance::updateOrCreate(
                [
                    'staff_id' => $staffId,
                    'date' => Settings::formatDate($request->date, 'Y-m-d')
                ],
                [
                    'status' => $status,
                    'remarks' => $request->remarks[$staffId] ?? null,
                    'created_by' => auth()->id()
                ]
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Attendance Saved Successfully');
    }

    /**
     * Punch In
     */
    public function punchIn(Request $request)
    {

        $staffId = auth()->user()->id;

        if (!$staffId) {
            return back()->with('error', 'Staff not linked with user.');
        }

        $record = Attendance::where('staff_id', $staffId)
            ->whereDate('date', date('Y-m-d'))
            ->first();

        if ($record && $record->check_in) {
            return back()->with('error', 'Already punched in.');
        }

        Attendance::updateOrCreate(
            [
                'staff_id' => $staffId,
                'date' => date('Y-m-d')
            ],
            [
                'status' => 'Present',
                'check_in' => now()->utc(),
                'created_by' => auth()->id()
            ]
        );

        return back()->with('success', 'Punch In Successful');
    }

    /**
     * Punch Out
     */

    public function punchOut(Request $request)
    {
        $staffId = auth()->user()->id;

        $attendance = Attendance::where('staff_id', $staffId)
            ->whereDate('date', now()->toDateString())
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Please Punch In First.');
        }

        // prevent accidental overwrite
        if ($attendance->check_out && !$request->confirm_update) {
            return back()->with('warning', 'already_punched_out');
        }

        $checkIn = Carbon::parse($attendance->check_in)->utc();
        $checkOut = now()->utc();

        $minutes = $checkIn->diffInMinutes($checkOut);
        $hours = round($minutes / 60, 2);

        $attendance->update([
            'check_out' => $checkOut,
            'work_hours' => $hours
        ]);

        return back()->with('success', 'Punch Out Updated Successfully');
    }
    /**
     * Attendance Report
     */

    public function report(Request $request)
    {
        $breadcrumb = $this->breadcrumbAttendanceReport;

        $month = $request->month ?? date('Y-m');
        $staffId = $request->staff_id;

        $carbon = Carbon::parse($month . '-01');

        $year = $carbon->year;
        $monthNo = $carbon->month;
        $daysInMonth = $carbon->daysInMonth;

        $user = auth()->user();

        // ===============================
        // STAFF LIST
        // Admin (designation_id = 2) = All
        // Others = Only Own Record
        // ===============================
        $staffs = User::where('status', 1)
            ->when($user->designation_id != 2, function ($q) use ($user) {
                $q->where('id', $user->id);
            })
            ->when($staffId, function ($q) use ($staffId, $user) {
                if ($user->designation_id == 2) {
                    $q->where('id', $staffId);
                }
            })
            ->orderBy('name')
            ->get();
        $staffdropdown = User::where('status', 1)
            ->visibleToUser()
            ->orderBy('name')
            ->pluck('name', 'id');

        // ===============================
        // ATTENDANCE RECORDS
        // ===============================
        $records = Attendance::whereYear('date', $year)
            ->whereMonth('date', $monthNo)

            ->when($user->designation_id != 2, function ($q) use ($user) {
                $q->where('staff_id', $user->id);
            })

            ->when($staffId, function ($q) use ($staffId, $user) {
                if ($user->designation_id == 2) {
                    $q->where('staff_id', $staffId);
                }
            })

            ->get();

        // ===============================
        // ARRAY FORMAT
        // ===============================
        $attendance = [];

        foreach ($records as $row) {
            $attendance[$row->staff_id][date('Y-m-d', strtotime($row->date))] = $row;
        }
        if ($request->has('pdf')) {
            $pdfHeaderdata = \Config::get('constants.attendanceListpdf');
            $pdf = PDF::loadView('backend.pdf.attendance.attendanceListpdf', compact('staffs', 'attendance', 'pdfHeaderdata', 'breadcrumb', 'year', 'monthNo', 'daysInMonth'));
            $pdf = Settings::downloadLandscapepdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        }
        if ($request->has('csv')) {

            $csvHeaderdata = \Config::get('constants.attendanceListpdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';

            $data = [];

            /*
            |--------------------------------------------------------------------------
            | Header Row
            |--------------------------------------------------------------------------
            */

            $header = $head = [];
            $monthName = date('F', mktime(0, 0, 0, $monthNo, 1, $year));

            $header = $head = [];

            /*
            |--------------------------------------------------------------------------
            | First Row
            |--------------------------------------------------------------------------
            */
            $head[] = '';
            $head[] = 'Staff';
            $head[] = $monthName . ' / Days';

            $data[] = $head;

            /*
            |--------------------------------------------------------------------------
            | Second Row
            |--------------------------------------------------------------------------
            */
            $header[] = '#';
            $header[] = __('translation.staff_name');

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $header[] = $d;
            }

            $header[] = 'P';
            $header[] = 'A';
            $header[] = 'H';
            $header[] = 'L';

            $data[] = $header;

            /*
            |--------------------------------------------------------------------------
            | Staff Rows
            |--------------------------------------------------------------------------
            */
            $sr = 1;

            foreach ($staffs as $staff) {

                $present = 0;
                $absent = 0;
                $half = 0;
                $leave = 0;

                $row = [];
                $row[] = $sr++;
                $row[] = $staff->name;

                for ($d = 1; $d <= $daysInMonth; $d++) {

                    $date = $year . '-' . sprintf('%02d', $monthNo) . '-' . sprintf('%02d', $d);

                    $attendanceRow = $attendance[$staff->id][$date] ?? null;
                    $status = $attendanceRow->status ?? '';

                    $text = '';

                    if ($status == 'Present') {
                        $text = 'P';
                        $present++;
                    } elseif ($status == 'Absent') {
                        $text = 'A';
                        $absent++;
                    } elseif ($status == 'Half Day') {
                        $text = 'H';
                        $half++;
                    } elseif ($status == 'Leave') {
                        $text = 'L';
                        $leave++;
                    }

                    $row[] = $text;
                }

                // Totals
                $row[] = $present;
                $row[] = $absent;
                $row[] = $half;
                $row[] = $leave;

                $data[] = $row;
            }

            return Settings::downloadcsvfile($data, $fileName);
        }

        return view('backend.admin.attendance.report', compact(
            'breadcrumb',
            'month',
            'staffs',
            'attendance',
            'year',
            'monthNo',
            'daysInMonth',
            'staffdropdown'
        ));
    }

    /**
     * Today's Attendance Summary
     */
    public function todaySummary()
    {
        $today = date('Y-m-d');

        $summary = [
            'present' => Attendance::whereDate('date', $today)
                ->where('status', 'Present')
                ->count(),

            'absent' => Attendance::whereDate('date', $today)
                ->where('status', 'Absent')
                ->count(),

            'leave' => Attendance::whereDate('date', $today)
                ->where('status', 'Leave')
                ->count(),

            'halfday' => Attendance::whereDate('date', $today)
                ->where('status', 'Half Day')
                ->count(),
        ];

        return response()->json($summary);
    }

    /**
     * Delete Attendance Record
     */
    public function destroy($id)
    {
        Attendance::findOrFail($id)->delete();

        return back()->with('success', 'Attendance Deleted Successfully');
    }
}