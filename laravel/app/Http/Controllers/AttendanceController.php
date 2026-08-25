<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $department = $request->get('department');

        $empQuery = Employee::where('status', 'Active')->orderBy('name');
        if ($department) {
            $empQuery->where('department', $department);
        }
        $employees = $empQuery->get();

        // Existing attendance for the date
        $attendances = Attendance::where('date', $date)->get()->keyBy('employee_id');

        // Summary stats for the date
        $totalActive = $employees->count();
        $presentCount = $attendances->where('status', 'Present')->count();
        $absentCount = $attendances->where('status', 'Absent')->count();
        $halfDayCount = $attendances->where('status', 'Half Day')->count();
        $leaveCount = $attendances->whereIn('status', ['Paid Leave', 'Holiday'])->count();
        $totalOtHours = $attendances->sum('overtime_hours');

        return view('attendance.index', compact(
            'date',
            'department',
            'employees',
            'attendances',
            'totalActive',
            'presentCount',
            'absentCount',
            'halfDayCount',
            'leaveCount',
            'totalOtHours'
        ));
    }

    public function storeDaily(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $attendanceData = $request->input('attendance', []); // [emp_id => ['status' => ..., 'overtime_hours' => ...]]

        DB::transaction(function () use ($date, $attendanceData) {
            foreach ($attendanceData as $empId => $data) {
                if (empty($data['status'])) continue;

                Attendance::updateOrCreate(
                    [
                        'employee_id' => $empId,
                        'date'        => $date,
                    ],
                    [
                        'status'         => $data['status'],
                        'in_time'        => $data['in_time'] ?? null,
                        'out_time'       => $data['out_time'] ?? null,
                        'working_hours'  => $data['status'] === 'Half Day' ? 4.00 : ($data['status'] === 'Present' ? 8.00 : 0.00),
                        'overtime_hours' => (float)($data['overtime_hours'] ?? 0.00),
                        'remarks'        => $data['remarks'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('attendance.index', ['date' => $date])->with('success', 'Daily attendance saved successfully.');
    }

    public function markAllPresent(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $employees = Employee::where('status', 'Active')->get();

        DB::transaction(function () use ($date, $employees) {
            foreach ($employees as $emp) {
                Attendance::updateOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'date'        => $date,
                    ],
                    [
                        'status'         => 'Present',
                        'working_hours'  => 8.00,
                        'overtime_hours' => 0.00,
                    ]
                );
            }
        });

        return redirect()->route('attendance.index', ['date' => $date])->with('success', 'Marked all active employees as Present.');
    }

    public function monthly(Request $request)
    {
        $monthYear = $request->get('month_year', Carbon::today()->format('Y-m'));
        $department = $request->get('department');

        $startOfMonth = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
        $endOfMonth   = Carbon::createFromFormat('Y-m', $monthYear)->endOfMonth();
        $daysInMonth  = $startOfMonth->daysInMonth;

        $empQuery = Employee::where('status', 'Active')->orderBy('name');
        if ($department) {
            $empQuery->where('department', $department);
        }
        $employees = $empQuery->get();

        $allAttendances = Attendance::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy('employee_id');

        return view('attendance.monthly', compact(
            'monthYear',
            'department',
            'employees',
            'daysInMonth',
            'startOfMonth',
            'allAttendances'
        ));
    }
}
