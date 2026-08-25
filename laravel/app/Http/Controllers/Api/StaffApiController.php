<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\PayrollRecord;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffApiController extends Controller
{
    public function employees(Request $request): JsonResponse
    {
        $query = Employee::latest();
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->has('department')) {
            $query->where('department', $request->get('department'));
        }
        return response()->json($query->paginate(30));
    }

    public function employeeDetail(Employee $employee): JsonResponse
    {
        $employee->load(['advances' => fn($q) => $q->take(10), 'payrolls' => fn($q) => $q->take(12)]);
        return response()->json($employee);
    }

    public function attendanceSummary(Request $request): JsonResponse
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $attendances = Attendance::where('date', $date)->with('employee:id,emp_code,name,department')->get();
        return response()->json([
            'date'        => $date,
            'total'       => Employee::where('status', 'Active')->count(),
            'present'     => $attendances->where('status', 'Present')->count(),
            'absent'      => $attendances->where('status', 'Absent')->count(),
            'half_day'    => $attendances->where('status', 'Half Day')->count(),
            'attendances' => $attendances,
        ]);
    }

    public function payrollRecords(Request $request): JsonResponse
    {
        $monthYear = $request->get('month_year', Carbon::today()->format('Y-m'));
        $payrolls = PayrollRecord::with('employee:id,emp_code,name,designation,salary_type')
            ->where('month_year', $monthYear)
            ->get();

        return response()->json([
            'month_year' => $monthYear,
            'summary'    => [
                'total_gross' => $payrolls->sum('gross_salary'),
                'total_net'   => $payrolls->sum('net_salary'),
                'total_paid'  => $payrolls->sum('paid_amount'),
                'count'       => $payrolls->count(),
            ],
            'records'    => $payrolls,
        ]);
    }
}
