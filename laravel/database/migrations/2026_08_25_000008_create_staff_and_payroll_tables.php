<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Employees Master Table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_code')->unique(); // e.g. EMP-001
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('designation')->default('Machine Operator'); // Machine Operator, Helper, Tool Room Tech, Quality Inspector, Supervisor, Accountant, Driver
            $table->string('department')->default('Production'); // Production, Tool Room, Quality, Maintenance, Dispatch & Logistics, Accounts & Admin
            $table->string('shift')->default('Day Shift (8 AM - 8 PM)'); // Day Shift, Night Shift, General Shift
            $table->date('joining_date')->nullable();
            $table->string('salary_type')->default('Monthly'); // Monthly, Daily Wage
            $table->decimal('base_salary', 12, 2)->default(0.00); // Monthly salary or daily wage
            $table->decimal('overtime_hourly_rate', 8, 2)->default(0.00);
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('aadhar_number', 20)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->string('status')->default('Active'); // Active, Inactive, On Leave, Resigned, Terminated
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->text('emergency_contact')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Daily Attendance Table
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('Present'); // Present, Absent, Half Day, Paid Leave, Holiday, Overtime Only
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->decimal('working_hours', 5, 2)->default(8.00);
            $table->decimal('overtime_hours', 5, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });

        // 3. Employee Advances & Loans Table (ઉપાડ)
        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->string('payment_mode')->default('Cash'); // Cash, Bank Transfer, UPI
            $table->string('salary_month', 7)->nullable(); // e.g. 2026-08 (month in which to deduct)
            $table->string('reason')->nullable();
            $table->boolean('is_deducted')->default(false);
            $table->unsignedBigInteger('payroll_record_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Monthly Payroll Records (પગાર પત્રક)
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_number')->unique(); // e.g. PAY-202608-001
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('month_year', 7); // e.g. 2026-08
            $table->integer('total_month_days')->default(30);
            $table->decimal('present_days', 5, 2)->default(0.00);
            $table->decimal('half_days', 5, 2)->default(0.00);
            $table->decimal('absent_days', 5, 2)->default(0.00);
            $table->decimal('paid_holidays', 5, 2)->default(0.00);
            $table->decimal('payable_days', 5, 2)->default(0.00);
            $table->decimal('base_rate', 10, 2)->default(0.00); // monthly base or daily rate
            $table->decimal('gross_salary', 10, 2)->default(0.00);
            $table->decimal('total_ot_hours', 6, 2)->default(0.00);
            $table->decimal('overtime_amount', 10, 2)->default(0.00);
            $table->decimal('bonus_allowances', 10, 2)->default(0.00);
            $table->decimal('advance_deductions', 10, 2)->default(0.00);
            $table->decimal('other_deductions', 10, 2)->default(0.00);
            $table->decimal('net_salary', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->string('payment_status')->default('Unpaid'); // Unpaid, Paid, Partial
            $table->date('payment_date')->nullable();
            $table->string('payment_mode')->nullable(); // Bank Transfer, Cash, UPI, Cheque
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('employee_advances');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');
    }
};
