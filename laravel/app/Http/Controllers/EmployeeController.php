<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search     = $request->get('search');
        $department = $request->get('department');
        $status     = $request->get('status');

        $query = Employee::latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('emp_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($department) {
            $query->where('department', $department);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $employees = $query->paginate(25)->withQueryString();

        // KPI statistics
        $totalCount    = Employee::count();
        $activeCount   = Employee::where('status', 'Active')->count();
        $monthlyStaff  = Employee::where('salary_type', 'Monthly')->count();
        $dailyStaff    = Employee::where('salary_type', 'Daily Wage')->count();
        $totalWageBill = Employee::where('status', 'Active')->where('salary_type', 'Monthly')->sum('base_salary');

        return view('employees.index', compact(
            'employees',
            'totalCount',
            'activeCount',
            'monthlyStaff',
            'dailyStaff',
            'totalWageBill',
            'search',
            'department',
            'status'
        ));
    }

    public function show(Employee $employee)
    {
        $employee->load(['attendances' => function ($q) {
            $q->latest('date')->take(31);
        }, 'advances', 'payrolls']);

        $totalAdvancesTaken = $employee->advances->sum('amount');
        $totalSalaryPaid    = $employee->payrolls->where('payment_status', 'Paid')->sum('net_salary');

        return view('employees.show', compact('employee', 'totalAdvancesTaken', 'totalSalaryPaid'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'emp_code'             => 'nullable|string|max:50|unique:employees,emp_code',
            'name'                 => 'required|string|max:255',
            'phone'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:255',
            'designation'          => 'required|string|max:100',
            'department'           => 'required|string|max:100',
            'shift'                => 'required|string|max:100',
            'joining_date'         => 'nullable|date',
            'salary_type'          => 'required|in:Monthly,Daily Wage',
            'base_salary'          => 'required|numeric|min:0',
            'overtime_hourly_rate' => 'nullable|numeric|min:0',
            'bank_name'            => 'nullable|string|max:100',
            'account_number'       => 'nullable|string|max:50',
            'ifsc_code'            => 'nullable|string|max:20',
            'upi_id'               => 'nullable|string|max:100',
            'aadhar_number'        => 'nullable|string|max:20',
            'pan_number'           => 'nullable|string|max:20',
            'status'               => 'required|string|max:50',
            'address'              => 'nullable|string',
            'notes'                => 'nullable|string',
            'photo'                => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        if (empty($validated['emp_code'])) {
            $validated['emp_code'] = Employee::generateCode('EMP');
        }

        if ($request->hasFile('photo')) {
            $dest = public_path('uploads/employees');
            if (!file_exists($dest)) {
                @mkdir($dest, 0777, true);
            }
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($dest, $filename);
            $validated['photo'] = 'uploads/employees/' . $filename;
        }

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee profile registered successfully.');
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'emp_code'             => 'required|string|max:50|unique:employees,emp_code,' . $employee->id,
            'name'                 => 'required|string|max:255',
            'phone'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:255',
            'designation'          => 'required|string|max:100',
            'department'           => 'required|string|max:100',
            'shift'                => 'required|string|max:100',
            'joining_date'         => 'nullable|date',
            'salary_type'          => 'required|in:Monthly,Daily Wage',
            'base_salary'          => 'required|numeric|min:0',
            'overtime_hourly_rate' => 'nullable|numeric|min:0',
            'bank_name'            => 'nullable|string|max:100',
            'account_number'       => 'nullable|string|max:50',
            'ifsc_code'            => 'nullable|string|max:20',
            'upi_id'               => 'nullable|string|max:100',
            'aadhar_number'        => 'nullable|string|max:20',
            'pan_number'           => 'nullable|string|max:20',
            'status'               => 'required|string|max:50',
            'address'              => 'nullable|string',
            'notes'                => 'nullable|string',
            'photo'                => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        if ($request->hasFile('photo')) {
            $dest = public_path('uploads/employees');
            if (!file_exists($dest)) {
                @mkdir($dest, 0777, true);
            }
            if ($employee->photo && file_exists(public_path($employee->photo))) {
                @unlink(public_path($employee->photo));
            }
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($dest, $filename);
            $validated['photo'] = 'uploads/employees/' . $filename;
        }

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee profile updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->photo && file_exists(public_path($employee->photo))) {
            @unlink(public_path($employee->photo));
        }
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee record deleted.');
    }
}
