<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'emp_code',
        'name',
        'phone',
        'email',
        'designation',
        'department',
        'shift',
        'joining_date',
        'salary_type',
        'base_salary',
        'overtime_hourly_rate',
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi_id',
        'aadhar_number',
        'pan_number',
        'status',
        'photo',
        'address',
        'emergency_contact',
        'notes',
    ];

    protected $casts = [
        'joining_date'         => 'date',
        'base_salary'          => 'decimal:2',
        'overtime_hourly_rate' => 'decimal:2',
    ];

    protected $appends = ['photo_url', 'daily_rate', 'formatted_salary'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset($this->photo) : null;
    }

    public function getDailyRateAttribute(): float
    {
        if ($this->salary_type === 'Daily Wage') {
            return (float) $this->base_salary;
        }
        return round((float) $this->base_salary / 30, 2);
    }

    public function getFormattedSalaryAttribute(): string
    {
        if ($this->salary_type === 'Daily Wage') {
            return '₹' . number_format((float)$this->base_salary, 2) . ' / Day';
        }
        return '₹' . number_format((float)$this->base_salary, 2) . ' / Month';
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class, 'employee_id')->latest('date');
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(PayrollRecord::class, 'employee_id')->latest('month_year');
    }

    public static function generateCode(string $prefix = 'EMP'): string
    {
        $count = self::count() + 1;
        $code = sprintf('%s-%03d', strtoupper($prefix), $count);
        while (self::where('emp_code', $code)->exists()) {
            $count++;
            $code = sprintf('%s-%03d', strtoupper($prefix), $count);
        }
        return $code;
    }
}
