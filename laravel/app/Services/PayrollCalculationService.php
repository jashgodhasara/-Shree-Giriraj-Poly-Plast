<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\PayrollRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollCalculationService
{
    /**
     * Compute salary details for a single employee in a given month (YYYY-MM).
     */
    public function calculateForEmployee(Employee $employee, string $monthYear): array
    {
        $startDate = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
        $endDate   = Carbon::createFromFormat('Y-m', $monthYear)->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        // Fetch attendance logs for the month
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $presentDays   = (float) $attendances->where('status', 'Present')->count();
        $halfDays      = (float) $attendances->where('status', 'Half Day')->count();
        $absentDays    = (float) $attendances->where('status', 'Absent')->count();
        $paidHolidays  = (float) $attendances->whereIn('status', ['Paid Leave', 'Holiday'])->count();
        $totalOtHours  = (float) $attendances->sum('overtime_hours');

        // Payable days
        $payableDays = $presentDays + ($halfDays * 0.5) + $paidHolidays;

        // If no attendance records at all (default full month assumption or 0)
        if ($attendances->isEmpty()) {
            $payableDays = $daysInMonth;
            $presentDays = $daysInMonth;
        }

        // Daily rate & Gross salary calculation
        if ($employee->salary_type === 'Daily Wage') {
            $dailyRate   = (float) $employee->base_salary;
            $grossSalary = round($payableDays * $dailyRate, 2);
        } else {
            // Monthly Fixed Salary
            $dailyRate   = round((float) $employee->base_salary / $daysInMonth, 2);
            $grossSalary = round(($payableDays / $daysInMonth) * (float) $employee->base_salary, 2);
        }

        // Overtime calculation
        $otRate = (float) $employee->overtime_hourly_rate;
        if ($otRate <= 0) {
            $otRate = round($dailyRate / 8, 2); // Default hourly OT rate based on 8-hour shift
        }
        $overtimeAmount = round($totalOtHours * $otRate, 2);

        // Advances pending for this month
        $pendingAdvances = EmployeeAdvance::where('employee_id', $employee->id)
            ->where('is_deducted', false)
            ->where(function ($q) use ($monthYear) {
                $q->where('salary_month', $monthYear)
                  ->orWhereNull('salary_month');
            })
            ->sum('amount');

        $advanceDeduction = (float) $pendingAdvances;
        $bonus = 0.00;
        $otherDeduction = 0.00;

        $netSalary = max(0, $grossSalary + $overtimeAmount + $bonus - $advanceDeduction - $otherDeduction);

        return [
            'employee_id'        => $employee->id,
            'month_year'         => $monthYear,
            'total_month_days'   => $daysInMonth,
            'present_days'       => $presentDays,
            'half_days'          => $halfDays,
            'absent_days'        => $absentDays,
            'paid_holidays'      => $paidHolidays,
            'payable_days'       => $payableDays,
            'base_rate'          => (float) $employee->base_salary,
            'gross_salary'       => $grossSalary,
            'total_ot_hours'     => $totalOtHours,
            'overtime_amount'    => $overtimeAmount,
            'bonus_allowances'   => $bonus,
            'advance_deductions' => $advanceDeduction,
            'other_deductions'   => $otherDeduction,
            'net_salary'         => $netSalary,
        ];
    }

    /**
     * Generate or recalculate payroll record for an employee.
     */
    public function generatePayrollRecord(Employee $employee, string $monthYear, array $overrides = []): PayrollRecord
    {
        return DB::transaction(function () use ($employee, $monthYear, $overrides) {
            $calc = $this->calculateForEmployee($employee, $monthYear);
            $data = array_merge($calc, $overrides);

            // Re-evaluate net salary with overrides if present
            $gross = (float) ($data['gross_salary'] ?? $calc['gross_salary']);
            $ot    = (float) ($data['overtime_amount'] ?? $calc['overtime_amount']);
            $bonus = (float) ($data['bonus_allowances'] ?? 0.00);
            $adv   = (float) ($data['advance_deductions'] ?? $calc['advance_deductions']);
            $other = (float) ($data['other_deductions'] ?? 0.00);
            $data['net_salary'] = max(0, $gross + $ot + $bonus - $adv - $other);

            $payroll = PayrollRecord::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'month_year'  => $monthYear,
                ],
                array_merge($data, [
                    'payroll_number' => PayrollRecord::where('employee_id', $employee->id)
                        ->where('month_year', $monthYear)
                        ->value('payroll_number') ?? PayrollRecord::generatePayrollNumber($monthYear),
                ])
            );

            // Link advances and mark as deducted if payroll has advance deduction
            if ($data['advance_deductions'] > 0) {
                EmployeeAdvance::where('employee_id', $employee->id)
                    ->where('is_deducted', false)
                    ->where(function ($q) use ($monthYear) {
                        $q->where('salary_month', $monthYear)
                          ->orWhereNull('salary_month');
                    })
                    ->update([
                        'is_deducted'       => true,
                        'payroll_record_id' => $payroll->id,
                    ]);
            }

            return $payroll;
        });
    }

    /**
     * Generate payroll for all active employees for a month.
     */
    public function generateMonthlyPayrollForAll(string $monthYear): int
    {
        $employees = Employee::where('status', 'Active')->get();
        $count = 0;

        foreach ($employees as $employee) {
            $this->generatePayrollRecord($employee, $monthYear);
            $count++;
        }

        return $count;
    }
}
