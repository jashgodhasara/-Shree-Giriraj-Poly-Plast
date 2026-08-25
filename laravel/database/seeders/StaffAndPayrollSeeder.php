<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\PayrollRecord;
use App\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StaffAndPayrollSeeder extends Seeder
{
    public function run(): void
    {
        $staffData = [
            [
                'emp_code'             => 'EMP-001',
                'name'                 => 'Ramesh Patel',
                'phone'                => '+91 98250 11223',
                'email'                => 'ramesh.patel@shreegiriraj.com',
                'designation'          => 'Senior Injection Moulding Operator',
                'department'           => 'Production',
                'shift'                => 'Day Shift (8 AM - 8 PM)',
                'joining_date'         => '2023-01-15',
                'salary_type'          => 'Monthly',
                'base_salary'          => 26000.00,
                'overtime_hourly_rate' => 120.00,
                'bank_name'            => 'State Bank of India',
                'account_number'       => '38910023456',
                'ifsc_code'            => 'SBIN0001234',
                'upi_id'               => 'rameshpatel@okhdfcbank',
                'aadhar_number'        => '4589 1234 9876',
                'status'               => 'Active',
                'address'              => 'Plot 12, GIDC Phase 2, Vatva, Ahmedabad',
            ],
            [
                'emp_code'             => 'EMP-002',
                'name'                 => 'Mahesh Parmar',
                'phone'                => '+91 98980 44556',
                'email'                => 'mahesh.parmar@shreegiriraj.com',
                'designation'          => 'Automatic Blow Moulding Master',
                'department'           => 'Production',
                'shift'                => 'Night Shift (8 PM - 8 AM)',
                'joining_date'         => '2023-05-10',
                'salary_type'          => 'Monthly',
                'base_salary'          => 24000.00,
                'overtime_hourly_rate' => 110.00,
                'bank_name'            => 'HDFC Bank',
                'account_number'       => '501002938475',
                'ifsc_code'            => 'HDFC0000456',
                'upi_id'               => 'maheshp@apl',
                'aadhar_number'        => '7823 4561 2345',
                'status'               => 'Active',
                'address'              => 'Ramol, Ahmedabad, Gujarat',
            ],
            [
                'emp_code'             => 'EMP-003',
                'name'                 => 'Vikram Solanki',
                'phone'                => '+91 97230 77889',
                'email'                => 'vikram.solanki@shreegiriraj.com',
                'designation'          => 'Tool Room & Dye Maintenance Specialist',
                'department'           => 'Tool Room',
                'shift'                => 'General Shift (9 AM - 6 PM)',
                'joining_date'         => '2022-08-01',
                'salary_type'          => 'Monthly',
                'base_salary'          => 32000.00,
                'overtime_hourly_rate' => 150.00,
                'bank_name'            => 'Bank of Baroda',
                'account_number'       => '023401009876',
                'ifsc_code'            => 'BARB0VADVIX',
                'upi_id'               => 'vikramsolanki@barodapay',
                'aadhar_number'        => '9812 6543 2198',
                'status'               => 'Active',
                'address'              => 'Odhav GIDC, Ahmedabad',
            ],
            [
                'emp_code'             => 'EMP-004',
                'name'                 => 'Suresh Rathod',
                'phone'                => '+91 94280 33221',
                'email'                => 'suresh.rathod@shreegiriraj.com',
                'designation'          => 'Production Helper & Material Loader',
                'department'           => 'Production',
                'shift'                => 'Day Shift (8 AM - 8 PM)',
                'joining_date'         => '2024-02-01',
                'salary_type'          => 'Daily Wage',
                'base_salary'          => 550.00, // ₹550 / day
                'overtime_hourly_rate' => 70.00,
                'bank_name'            => 'State Bank of India',
                'account_number'       => '39021128374',
                'ifsc_code'            => 'SBIN0004567',
                'upi_id'               => 'sureshrathod@sbi',
                'status'               => 'Active',
                'address'              => 'Narol, Ahmedabad',
            ],
            [
                'emp_code'             => 'EMP-005',
                'name'                 => 'Jitendra Makwana',
                'phone'                => '+91 96380 99887',
                'email'                => 'jitendra.m@shreegiriraj.com',
                'designation'          => 'Quality Inspector & Dispatch Head',
                'department'           => 'Quality',
                'shift'                => 'General Shift (9 AM - 6 PM)',
                'joining_date'         => '2023-11-20',
                'salary_type'          => 'Monthly',
                'base_salary'          => 22000.00,
                'overtime_hourly_rate' => 100.00,
                'bank_name'            => 'Kotak Mahindra Bank',
                'account_number'       => '4812349081',
                'ifsc_code'            => 'KKBK0000889',
                'upi_id'               => 'jitumakwana@kotak',
                'status'               => 'Active',
                'address'              => 'Maninagar, Ahmedabad',
            ],
            [
                'emp_code'             => 'EMP-006',
                'name'                 => 'Bhupatbhai Chauhan',
                'phone'                => '+91 95120 44332',
                'email'                => 'bhupat.c@shreegiriraj.com',
                'designation'          => 'Plant Maintenance Electrician',
                'department'           => 'Maintenance',
                'shift'                => 'General Shift (9 AM - 6 PM)',
                'joining_date'         => '2023-04-12',
                'salary_type'          => 'Monthly',
                'base_salary'          => 25000.00,
                'overtime_hourly_rate' => 120.00,
                'bank_name'            => 'Axis Bank',
                'account_number'       => '920010034567890',
                'ifsc_code'            => 'UTIB0001020',
                'status'               => 'Active',
                'address'              => 'C.T.M., Ahmedabad',
            ],
        ];

        foreach ($staffData as $item) {
            $emp = Employee::updateOrCreate(['emp_code' => $item['emp_code']], $item);

            // Seed some attendance records for August 2026
            $curMonth = '2026-08';
            for ($d = 1; $d <= 25; $d++) {
                $dateStr = sprintf('%s-%02d', $curMonth, $d);
                $isSunday = Carbon::parse($dateStr)->isSunday();

                Attendance::updateOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'date'        => $dateStr,
                    ],
                    [
                        'status'         => $isSunday ? 'Holiday' : ($d % 14 === 0 ? 'Half Day' : ($d % 22 === 0 ? 'Absent' : 'Present')),
                        'working_hours'  => $isSunday ? 0.00 : ($d % 14 === 0 ? 4.00 : 8.00),
                        'overtime_hours' => (!$isSunday && $d % 5 === 0) ? 2.00 : 0.00,
                    ]
                );
            }
        }

        // Seed some advances
        $emp1 = Employee::where('emp_code', 'EMP-001')->first();
        if ($emp1) {
            EmployeeAdvance::updateOrCreate(
                [
                    'employee_id'  => $emp1->id,
                    'date'         => '2026-08-10',
                ],
                [
                    'amount'       => 3000.00,
                    'payment_mode' => 'Cash',
                    'salary_month' => '2026-08',
                    'reason'       => 'Family medical expense advance',
                    'is_deducted'  => false,
                ]
            );
        }

        $emp4 = Employee::where('emp_code', 'EMP-004')->first();
        if ($emp4) {
            EmployeeAdvance::updateOrCreate(
                [
                    'employee_id'  => $emp4->id,
                    'date'         => '2026-08-12',
                ],
                [
                    'amount'       => 1500.00,
                    'payment_mode' => 'UPI',
                    'salary_month' => '2026-08',
                    'reason'       => 'Emergency village travel',
                    'is_deducted'  => false,
                ]
            );
        }

        // Generate payroll for August 2026
        $service = app(PayrollCalculationService::class);
        $service->generateMonthlyPayrollForAll('2026-08');
    }
}
