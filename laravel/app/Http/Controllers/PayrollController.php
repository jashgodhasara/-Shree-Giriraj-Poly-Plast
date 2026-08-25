<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    protected PayrollCalculationService $payrollService;

    public function __construct(PayrollCalculationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index(Request $request)
    {
        $monthYear = $request->get('month_year', Carbon::today()->format('Y-m'));
        $status    = $request->get('status');

        $query = PayrollRecord::with(['employee', 'advances'])->where('month_year', $monthYear);

        if ($status) {
            $query->where('payment_status', $status);
        }

        $payrolls = $query->paginate(25)->withQueryString();

        // Monthly payroll summary KPIs
        $totalGrossSalary   = PayrollRecord::where('month_year', $monthYear)->sum('gross_salary');
        $totalOvertimePay   = PayrollRecord::where('month_year', $monthYear)->sum('overtime_amount');
        $totalAdvancesDeducted = PayrollRecord::where('month_year', $monthYear)->sum('advance_deductions');
        $totalNetPayroll    = PayrollRecord::where('month_year', $monthYear)->sum('net_salary');
        $totalPaidAmount    = PayrollRecord::where('month_year', $monthYear)->sum('paid_amount');
        $totalPendingAmount = max(0, $totalNetPayroll - $totalPaidAmount);

        $activeEmployeesCount = Employee::where('status', 'Active')->count();
        $processedCount       = PayrollRecord::where('month_year', $monthYear)->count();

        return view('payroll.index', compact(
            'monthYear',
            'status',
            'payrolls',
            'totalGrossSalary',
            'totalOvertimePay',
            'totalAdvancesDeducted',
            'totalNetPayroll',
            'totalPaidAmount',
            'totalPendingAmount',
            'activeEmployeesCount',
            'processedCount'
        ));
    }

    public function generateAll(Request $request)
    {
        $monthYear = $request->input('month_year', Carbon::today()->format('Y-m'));
        $count = $this->payrollService->generateMonthlyPayrollForAll($monthYear);

        return redirect()->route('payroll.index', ['month_year' => $monthYear])
            ->with('success', "Successfully generated payroll records for {$count} active employees for {$monthYear}.");
    }

    public function generateSingle(Request $request, Employee $employee)
    {
        $monthYear = $request->input('month_year', Carbon::today()->format('Y-m'));
        $this->payrollService->generatePayrollRecord($employee, $monthYear);

        return redirect()->route('payroll.index', ['month_year' => $monthYear])
            ->with('success', "Payroll calculated for {$employee->name} for {$monthYear}.");
    }

    public function markAsPaid(Request $request, PayrollRecord $payroll)
    {
        $validated = $request->validate([
            'payment_date'          => 'required|date',
            'payment_mode'          => 'required|in:Bank Transfer,Cash,UPI,Cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'paid_amount'           => 'required|numeric|min:1',
            'notes'                 => 'nullable|string',
        ]);

        $paid = (float) $validated['paid_amount'];
        $net  = (float) $payroll->net_salary;

        $payroll->paid_amount           = $paid;
        $payroll->payment_status        = $paid >= $net ? 'Paid' : 'Partial';
        $payroll->payment_date          = $validated['payment_date'];
        $payroll->payment_mode          = $validated['payment_mode'];
        $payroll->transaction_reference = $validated['transaction_reference'] ?? null;
        $payroll->notes                 = $validated['notes'] ?? null;
        $payroll->save();

        return back()->with('success', 'Salary payout recorded successfully.');
    }

    public function payslip(PayrollRecord $payroll)
    {
        $payroll->load(['employee', 'advances']);
        return view('payroll.payslip', compact('payroll'));
    }
}
