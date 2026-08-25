<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRecord extends Model
{
    protected $table = 'payroll_records';

    protected $fillable = [
        'payroll_number',
        'employee_id',
        'month_year',
        'total_month_days',
        'present_days',
        'half_days',
        'absent_days',
        'paid_holidays',
        'payable_days',
        'base_rate',
        'gross_salary',
        'total_ot_hours',
        'overtime_amount',
        'bonus_allowances',
        'advance_deductions',
        'other_deductions',
        'net_salary',
        'paid_amount',
        'payment_status',
        'payment_date',
        'payment_mode',
        'transaction_reference',
        'notes',
    ];

    protected $casts = [
        'total_month_days'   => 'integer',
        'present_days'       => 'decimal:2',
        'half_days'          => 'decimal:2',
        'absent_days'        => 'decimal:2',
        'paid_holidays'      => 'decimal:2',
        'payable_days'       => 'decimal:2',
        'base_rate'          => 'decimal:2',
        'gross_salary'       => 'decimal:2',
        'total_ot_hours'     => 'decimal:2',
        'overtime_amount'    => 'decimal:2',
        'bonus_allowances'   => 'decimal:2',
        'advance_deductions' => 'decimal:2',
        'other_deductions'   => 'decimal:2',
        'net_salary'         => 'decimal:2',
        'paid_amount'        => 'decimal:2',
        'payment_date'       => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class, 'payroll_record_id');
    }

    public static function generatePayrollNumber(string $monthYear): string
    {
        $clean = str_replace('-', '', $monthYear);
        $prefix = "PAY-{$clean}-";
        $count = self::where('month_year', $monthYear)->count() + 1;
        $num = sprintf('%s%03d', $prefix, $count);
        while (self::where('payroll_number', $num)->exists()) {
            $count++;
            $num = sprintf('%s%03d', $prefix, $count);
        }
        return $num;
    }
}
