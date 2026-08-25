<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAdvance extends Model
{
    protected $table = 'employee_advances';

    protected $fillable = [
        'employee_id',
        'date',
        'amount',
        'payment_mode',
        'salary_month',
        'reason',
        'is_deducted',
        'payroll_record_id',
        'notes',
    ];

    protected $casts = [
        'date'        => 'date',
        'amount'      => 'decimal:2',
        'is_deducted' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function payrollRecord(): BelongsTo
    {
        return $this->belongsTo(PayrollRecord::class, 'payroll_record_id');
    }
}
