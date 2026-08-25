<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\PayrollRecord;
use App\Models\User;
use App\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAndPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_can_view_employees_and_create_employee(): void
    {
        $response = $this->actingAs($this->user)->post('/employees', [
            'name'                 => 'Mukesh Chauhan',
            'phone'                => '+91 98765 43210',
            'designation'          => 'Injection Machine Operator',
            'department'           => 'Production',
            'shift'                => 'Day Shift (8 AM - 8 PM)',
            'salary_type'          => 'Monthly',
            'base_salary'          => 25000.00,
            'overtime_hourly_rate' => 120.00,
            'status'               => 'Active',
        ]);

        $response->assertRedirect('/employees');
        $this->assertDatabaseHas('employees', [
            'name'        => 'Mukesh Chauhan',
            'designation' => 'Injection Machine Operator',
            'salary_type' => 'Monthly',
        ]);

        $indexRes = $this->actingAs($this->user)->get('/employees');
        $indexRes->assertStatus(200);
        $indexRes->assertSee('Mukesh Chauhan');
    }

    public function test_can_mark_daily_attendance_and_all_present(): void
    {
        $emp = Employee::create([
            'emp_code'    => 'EMP-TEST-01',
            'name'        => 'Test Worker',
            'designation' => 'Helper',
            'department'  => 'Production',
            'shift'       => 'Day Shift (8 AM - 8 PM)',
            'salary_type' => 'Daily Wage',
            'base_salary' => 600.00,
            'status'      => 'Active',
        ]);

        $today = Carbon::today()->toDateString();

        // 1-Click All Present
        $responseAll = $this->actingAs($this->user)->post('/attendance/mark-all-present', [
            'date' => $today,
        ]);
        $responseAll->assertRedirect();

        $att = Attendance::where('employee_id', $emp->id)->whereDate('date', $today)->first();
        $this->assertNotNull($att);
        $this->assertEquals('Present', $att->status);

        // Update single worker to Half Day with OT
        $responseUpdate = $this->actingAs($this->user)->post('/attendance/daily', [
            'date'       => $today,
            'attendance' => [
                $emp->id => [
                    'status'         => 'Half Day',
                    'overtime_hours' => 2.0,
                    'remarks'        => 'Left early for doctor visit',
                ],
            ],
        ]);
        $responseUpdate->assertRedirect();

        $att->refresh();
        $this->assertEquals('Half Day', $att->status);
        $this->assertEquals('2.00', $att->overtime_hours);
    }

    public function test_can_record_employee_advance_and_deduct_in_payroll(): void
    {
        $emp = Employee::create([
            'emp_code'             => 'EMP-PAY-01',
            'name'                 => 'Kishan Dave',
            'designation'          => 'Operator',
            'department'           => 'Production',
            'shift'                => 'Day Shift (8 AM - 8 PM)',
            'salary_type'          => 'Monthly',
            'base_salary'          => 30000.00,
            'overtime_hourly_rate' => 125.00,
            'status'               => 'Active',
        ]);

        // Issue Advance
        $responseAdv = $this->actingAs($this->user)->post('/employee-advances', [
            'employee_id'  => $emp->id,
            'date'         => '2026-08-05',
            'amount'       => 5000.00,
            'payment_mode' => 'Cash',
            'salary_month' => '2026-08',
            'reason'       => 'Urgent family help',
        ]);
        $responseAdv->assertRedirect('/employee-advances');
        $this->assertDatabaseHas('employee_advances', [
            'employee_id' => $emp->id,
            'amount'      => 5000.00,
            'is_deducted' => false,
        ]);

        // Calculate payroll via service
        $service = app(PayrollCalculationService::class);
        $payroll = $service->generatePayrollRecord($emp, '2026-08');

        $this->assertEquals(30000.00, $payroll->gross_salary);
        $this->assertEquals(5000.00, $payroll->advance_deductions);
        $this->assertEquals(25000.00, $payroll->net_salary);

        // Verify advance marked as deducted
        $this->assertDatabaseHas('employee_advances', [
            'employee_id' => $emp->id,
            'is_deducted' => true,
        ]);
    }

    public function test_can_mark_payroll_payout_and_view_payslip(): void
    {
        $emp = Employee::create([
            'emp_code'    => 'EMP-SLIP-01',
            'name'        => 'Anand Suthar',
            'designation' => 'Tool Maker',
            'department'  => 'Tool Room',
            'shift'       => 'General Shift (9 AM - 6 PM)',
            'salary_type' => 'Monthly',
            'base_salary' => 28000.00,
            'status'      => 'Active',
        ]);

        $service = app(PayrollCalculationService::class);
        $payroll = $service->generatePayrollRecord($emp, '2026-08');

        // Mark as paid
        $responsePay = $this->actingAs($this->user)->post("/payroll/{$payroll->id}/pay", [
            'payment_date'          => '2026-08-31',
            'payment_mode'          => 'Bank Transfer',
            'paid_amount'           => $payroll->net_salary,
            'transaction_reference' => 'UTR99887766',
        ]);

        $responsePay->assertRedirect();
        $payroll->refresh();
        $this->assertEquals('Paid', $payroll->payment_status);

        // View printable payslip
        $slipRes = $this->actingAs($this->user)->get("/payroll/{$payroll->id}/payslip");
        $slipRes->assertStatus(200);
        $slipRes->assertSee('Anand Suthar');
        $slipRes->assertSee('SALARY PAYSLIP');
    }

    public function test_staff_api_endpoints(): void
    {
        $emp = Employee::create([
            'emp_code'    => 'EMP-API-01',
            'name'        => 'API Test Staff',
            'designation' => 'Technician',
            'department'  => 'Maintenance',
            'shift'       => 'Day Shift (8 AM - 8 PM)',
            'salary_type' => 'Monthly',
            'base_salary' => 20000.00,
            'status'      => 'Active',
        ]);

        $token = $this->user->createToken('test-token')->plainTextToken;

        $responseList = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/staff/employees');
        $responseList->assertStatus(200);
        $responseList->assertJsonFragment(['name' => 'API Test Staff']);

        $responseAtt = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/staff/attendance');
        $responseAtt->assertStatus(200);
        $responseAtt->assertJsonStructure(['date', 'total', 'present', 'absent', 'half_day']);
    }
}
