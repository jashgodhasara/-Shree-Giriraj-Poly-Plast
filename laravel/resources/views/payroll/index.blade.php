@extends('layouts.app')
@section('title', 'Staff Salary & Payroll Processing — Shree Giriraj Poly Plast')
@section('page-title', 'Staff Salary & Payroll (પગાર પત્રક)')

@section('content')
<!-- Top Action Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-dark, #0f172a); margin:0; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-file-invoice-dollar" style="color:var(--primary, #6366f1);"></i> Monthly Salary &amp; Payroll (પગાર પત્રક)
        </h2>
        <p style="font-size:13px; color:var(--text-muted, #64748b); margin:4px 0 0 0;">Automatic wage calculation, attendance multipliers, OT bonuses, advance deductions &amp; payslips</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('attendance.index') }}" class="btn btn-outline" style="background:#fff;">
            <i class="fa-solid fa-clipboard-user"></i> Daily Attendance
        </a>
        <a href="{{ route('employee-advances.index') }}" class="btn btn-outline" style="background:#fff;">
            <i class="fa-solid fa-hand-holding-dollar"></i> Upad Register
        </a>
        <form method="POST" action="{{ route('payroll.generate-all') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="month_year" value="{{ $monthYear }}">
            <button type="submit" class="btn btn-primary" onclick="return confirm('Generate or recalculate monthly payroll for all active staff for {{ $monthYear }}?');">
                <i class="fa-solid fa-calculator"></i> 1-Click Calculate Payroll
            </button>
        </form>
    </div>
</div>

<!-- KPI Metric Cards Grid for Month -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
            <span class="badge badge-purple">{{ date('F Y', strtotime($monthYear . '-01')) }}</span>
        </div>
        <div class="stat-label">Gross Earned Wages</div>
        <div class="stat-value">₹{{ number_format((float)$totalGrossSalary, 2) }}</div>
    </div>
    <div class="stat-card s-blue">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-business-time"></i></div>
            <span class="badge badge-info">OT Pay</span>
        </div>
        <div class="stat-label">Total Overtime (OT) Pay</div>
        <div class="stat-value">₹{{ number_format((float)$totalOvertimePay, 2) }}</div>
    </div>
    <div class="stat-card s-amber">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <span class="badge badge-warning">Upad Deducted</span>
        </div>
        <div class="stat-label">Advances Deducted</div>
        <div class="stat-value">₹{{ number_format((float)$totalAdvancesDeducted, 2) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-indian-rupee-sign"></i></div>
            <span class="badge badge-success">Net Payroll</span>
        </div>
        <div class="stat-label">Net Payable Salary</div>
        <div class="stat-value">₹{{ number_format((float)$totalNetPayroll, 2) }}</div>
    </div>
    <div class="stat-card s-rose">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <span class="badge badge-indigo">Disbursement</span>
        </div>
        <div class="stat-label">Total Paid / Disbursed</div>
        <div class="stat-value">₹{{ number_format((float)$totalPaidAmount, 2) }}</div>
    </div>
</div>

<!-- Month & Status Filter Bar -->
<div class="card" style="margin-bottom:20px; padding:16px;">
    <form method="GET" action="{{ route('payroll.index') }}" style="display:grid; grid-template-columns: 1fr 1fr 120px; gap:12px; align-items:center;">
        <div style="display:flex; align-items:center; gap:8px;">
            <label style="font-weight:700; font-size:14px; color:var(--text-dark); white-space:nowrap;"><i class="fa fa-calendar-alt text-primary"></i> Select Salary Month:</label>
            <input type="month" name="month_year" value="{{ $monthYear }}" class="form-control" onchange="this.form.submit()" style="font-weight:700;">
        </div>
        <div>
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">All Payment Statuses</option>
                <option value="Paid" {{ $status === 'Paid' ? 'selected' : '' }}>Paid (ચૂકવાઈ ગયેલ)</option>
                <option value="Unpaid" {{ $status === 'Unpaid' ? 'selected' : '' }}>Unpaid (બાકી)</option>
                <option value="Partial" {{ $status === 'Partial' ? 'selected' : '' }}>Partial</option>
            </select>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fa fa-filter"></i> Filter</button>
            <a href="{{ route('payroll.index') }}" class="btn btn-secondary" title="Reset Filters"><i class="fa fa-redo"></i></a>
        </div>
    </form>
</div>

<!-- Payroll Table -->
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0; font-size:16px; font-weight:700;">
            <i class="fa-solid fa-file-invoice-dollar text-primary"></i> Payroll Statements for {{ date('F Y', strtotime($monthYear . '-01')) }} ({{ $payrolls->total() }} Employees)
        </h3>
    </div>
    <div class="card-body" style="padding:0;">
        @if($payrolls->isEmpty())
        <div class="empty-state" style="padding:48px 20px; text-align:center;">
            <div class="empty-icon" style="font-size:40px; color:#cbd5e1; margin-bottom:12px;"><i class="fa-solid fa-calculator"></i></div>
            <p style="color:var(--text-muted); font-size:15px; margin-bottom:16px;">No payroll statements generated yet for {{ $monthYear }}.</p>
            <form method="POST" action="{{ route('payroll.generate-all') }}">
                @csrf
                <input type="hidden" name="month_year" value="{{ $monthYear }}">
                <button type="submit" class="btn btn-primary">Calculate Payroll for {{ date('F Y', strtotime($monthYear . '-01')) }}</button>
            </form>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Wage Basis</th>
                        <th>Payable Days / OT</th>
                        <th>Gross Wages</th>
                        <th>Upad Deductions</th>
                        <th>Net Payable</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions &amp; Payslip</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payrolls as $p)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:36px; height:36px; border-radius:50%; background:#eef2ff; color:#4f46e5; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; flex-shrink:0;">
                                    {{ strtoupper(substr($p->employee->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div style="font-weight:700; color:var(--text-dark); font-size:14px;">{{ $p->employee->name }}</div>
                                    <code style="font-size:11px; background:#f1f5f9; color:#475569; padding:1px 5px; border-radius:4px;">{{ $p->employee->emp_code }}</code>
                                    <span style="font-size:11px; color:var(--text-muted);">{{ $p->employee->designation }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $p->employee->salary_type === 'Daily Wage' ? 'badge-warning' : 'badge-purple' }}">
                                {{ $p->employee->salary_type }}
                            </span>
                            <div style="font-size:12px; font-weight:700; font-family:monospace; margin-top:2px;">{{ $p->employee->formatted_salary }}</div>
                        </td>
                        <td>
                            <div style="font-weight:700; color:var(--text-dark);">{{ $p->payable_days }} / {{ $p->total_month_days }} Days</div>
                            @if($p->total_ot_hours > 0)
                                <span class="badge badge-info" style="font-size:11px; margin-top:2px;">+{{ $p->total_ot_hours }} hrs OT (₹{{ number_format((float)$p->overtime_amount, 2) }})</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight:700; color:var(--text-dark); font-family:monospace;">₹{{ number_format((float)$p->gross_salary, 2) }}</span>
                        </td>
                        <td>
                            @if($p->advance_deductions > 0)
                                <span style="font-weight:700; color:var(--danger, #ef4444); font-family:monospace;">-₹{{ number_format((float)$p->advance_deductions, 2) }}</span>
                            @else
                                <span style="color:var(--text-muted); font-size:12px;">₹0.00</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight:800; color:var(--primary); font-size:15px; font-family:monospace;">
                                ₹{{ number_format((float)$p->net_salary, 2) }}
                            </span>
                        </td>
                        <td>
                            @if($p->payment_status === 'Paid')
                                <span class="badge badge-success"><i class="fa fa-circle-check"></i> Paid</span>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">{{ $p->payment_date ? $p->payment_date->format('d M') : '' }} ({{ $p->payment_mode }})</div>
                            @elseif($p->payment_status === 'Partial')
                                <span class="badge badge-warning">Partial (₹{{ number_format((float)$p->paid_amount, 0) }})</span>
                            @else
                                <span class="badge badge-danger">Unpaid</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; gap:6px; justify-content:flex-end;">
                                <a href="{{ route('payroll.payslip', $p) }}" class="btn btn-outline btn-sm btn-icon" title="View &amp; Print Payslip" target="_blank">
                                    <i class="fa fa-print"></i>
                                </a>
                                @if($p->payment_status !== 'Paid')
                                <button class="btn btn-primary btn-sm btn-icon" title="Mark Salary Paid" onclick='openPayoutModal(@json($p))'>
                                    <i class="fa fa-indian-rupee-sign"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px 20px; border-top:1px solid #f1f5f9;">
            {{ $payrolls->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Mark Payout Modal -->
<div class="modal-overlay" id="payoutModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-indian-rupee-sign text-success"></i> Record Salary Payout</h3>
            <button class="modal-close" onclick="closeModal('payoutModal')">✕</button>
        </div>
        <form id="payoutForm" method="POST" action="">
            @csrf
            <div class="modal-body">
                <div style="margin-bottom:14px; padding:12px; background:#eef2ff; border-radius:8px; border:1px solid #c7d2fe;">
                    <div style="font-size:13px; color:#475569;">Employee: <strong id="payout_emp_name" style="color:#1e1b4b;"></strong></div>
                    <div style="font-size:15px; font-weight:800; color:var(--primary); margin-top:4px;">Net Payable: ₹<span id="payout_net_amount"></span></div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Payout Date</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Paid Amount (₹)</label>
                        <input type="number" step="0.01" name="paid_amount" id="payout_paid_amount" required class="form-control" style="font-weight:700;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Payment Mode</label>
                        <select name="payment_mode" required class="form-control">
                            <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="UPI">UPI (Direct Pay)</option>
                            <option value="Cash">Cash (રોકડા)</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Transaction / UTR Ref</label>
                        <input type="text" name="transaction_reference" placeholder="UTR or Ref No" class="form-control">
                    </div>
                </div>

                <div>
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" placeholder="e.g. Cleared via Kotak Bank..." class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('payoutModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Confirm Payment</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openPayoutModal(payroll) {
    document.getElementById('payoutForm').action = "/payroll/" + payroll.id + "/pay";
    document.getElementById('payout_emp_name').innerText = payroll.employee.name + " (" + payroll.employee.emp_code + ")";
    document.getElementById('payout_net_amount').innerText = parseFloat(payroll.net_salary).toFixed(2);
    document.getElementById('payout_paid_amount').value = parseFloat(payroll.net_salary).toFixed(2);
    openModal('payoutModal');
}
</script>
@endpush
@endsection
