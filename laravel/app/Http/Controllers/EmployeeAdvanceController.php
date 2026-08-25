<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $month      = $request->get('month', Carbon::today()->format('Y-m'));

        $query = EmployeeAdvance::with('employee')->latest('date');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($month) {
            $query->where(function ($q) use ($month) {
                $q->where('salary_month', $month)
                  ->orWhere('date', 'like', "{$month}%");
            });
        }

        $advances = $query->paginate(25)->withQueryString();

        $totalAdvancesAmount = EmployeeAdvance::sum('amount');
        $pendingDeduction    = EmployeeAdvance::where('is_deducted', false)->sum('amount');
        $deductedAmount      = EmployeeAdvance::where('is_deducted', true)->sum('amount');

        $employees = Employee::where('status', 'Active')->orderBy('name')->get();

        return view('advances.index', compact(
            'advances',
            'totalAdvancesAmount',
            'pendingDeduction',
            'deductedAmount',
            'employees',
            'employeeId',
            'month'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'date'         => 'required|date',
            'amount'       => 'required|numeric|min:1',
            'payment_mode' => 'required|in:Cash,Bank Transfer,UPI',
            'salary_month' => 'nullable|string|max:7',
            'reason'       => 'nullable|string|max:255',
            'notes'        => 'nullable|string',
        ]);

        if (empty($validated['salary_month'])) {
            $validated['salary_month'] = Carbon::parse($validated['date'])->format('Y-m');
        }

        EmployeeAdvance::create($validated);

        return redirect()->route('employee-advances.index')->with('success', 'Staff advance (ઉપાડ) recorded successfully.');
    }

    public function destroy(EmployeeAdvance $employeeAdvance)
    {
        if ($employeeAdvance->is_deducted) {
            return back()->with('error', 'Cannot delete advance because it is already deducted in a payroll slip.');
        }

        $employeeAdvance->delete();

        return redirect()->route('employee-advances.index')->with('success', 'Advance record deleted.');
    }
}
