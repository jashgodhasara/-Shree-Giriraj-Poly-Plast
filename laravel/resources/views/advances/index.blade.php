@extends('layouts.app')
@section('title', 'Staff Advances & Upad Register — Shree Giriraj Poly Plast')
@section('page-title', 'Staff Advances (ઉપાડ હિસાબ)')

@section('content')
<!-- Top Action Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-dark, #0f172a); margin:0; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-hand-holding-dollar" style="color:var(--primary, #6366f1);"></i> Staff Advances &amp; Upad Register (ઉપાડ હિસાબ)
        </h2>
        <p style="font-size:13px; color:var(--text-muted, #64748b); margin:4px 0 0 0;">Track mid-month cash/UPI advances and automatic deductions from salary slips</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('payroll.index') }}" class="btn btn-outline" style="background:#fff;">
            <i class="fa-solid fa-file-invoice-dollar"></i> View Monthly Payroll
        </a>
        <button class="btn btn-primary" onclick="openCreateAdvanceModal()">
            <i class="fa fa-plus"></i> Give New Advance (ઉપાડ)
        </button>
    </div>
</div>

<!-- KPI Metric Cards Grid -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-receipt"></i></div>
            <span class="badge badge-purple">Total Upad</span>
        </div>
        <div class="stat-label">Total Advances Issued</div>
        <div class="stat-value">₹{{ number_format((float)$totalAdvancesAmount, 2) }}</div>
    </div>
    <div class="stat-card s-amber">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
            <span class="badge badge-warning">Pending</span>
        </div>
        <div class="stat-label">Pending Deduction</div>
        <div class="stat-value">₹{{ number_format((float)$pendingDeduction, 2) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <span class="badge badge-success">Deducted</span>
        </div>
        <div class="stat-label">Deducted in Salaries</div>
        <div class="stat-value">₹{{ number_format((float)$deductedAmount, 2) }}</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom:20px; padding:16px;">
    <form method="GET" action="{{ route('employee-advances.index') }}" style="display:grid; grid-template-columns: 1fr 1fr 120px; gap:12px; align-items:center;">
        <div>
            <select name="employee_id" class="form-control" onchange="this.form.submit()">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->emp_code }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <input type="month" name="month" value="{{ $month }}" class="form-control" onchange="this.form.submit()">
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fa fa-filter"></i> Filter</button>
            <a href="{{ route('employee-advances.index') }}" class="btn btn-secondary" title="Reset Filters"><i class="fa fa-redo"></i></a>
        </div>
    </form>
</div>

<!-- Advances Table -->
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-hand-holding-dollar text-primary"></i> Advances Log ({{ $advances->total() }})</h3>
    </div>
    <div class="card-body" style="padding:0;">
        @if($advances->isEmpty())
        <div class="empty-state" style="padding:48px 20px; text-align:center;">
            <div class="empty-icon" style="font-size:40px; color:#cbd5e1; margin-bottom:12px;"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <p style="color:var(--text-muted); font-size:15px; margin-bottom:16px;">No advance / upad records found matching criteria.</p>
            <button class="btn btn-primary btn-sm" onclick="openCreateAdvanceModal()">Give First Advance</button>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employee Details</th>
                        <th>Advance Date</th>
                        <th>Amount</th>
                        <th>Payment Mode</th>
                        <th>Salary Month</th>
                        <th>Reason / Purpose</th>
                        <th>Deduction Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($advances as $adv)
                    <tr>
                        <td>
                            <div style="font-weight:700; color:var(--text-dark);">{{ $adv->employee?->name }}</div>
                            <code style="font-size:11px; background:#f1f5f9; color:#475569; padding:1px 5px; border-radius:4px;">{{ $adv->employee?->emp_code }}</code>
                        </td>
                        <td style="font-weight:600;">{{ $adv->date->format('d M Y') }}</td>
                        <td>
                            <span style="font-weight:800; font-family:monospace; color:var(--danger, #ef4444); font-size:14px;">
                                ₹{{ number_format((float)$adv->amount, 2) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $adv->payment_mode }}</span>
                        </td>
                        <td>
                            <span style="font-weight:600;">{{ $adv->salary_month }}</span>
                        </td>
                        <td style="font-size:12.5px; color:var(--text-dark);">
                            {{ $adv->reason ?: 'General Advance' }}
                        </td>
                        <td>
                            @if($adv->is_deducted)
                                <span class="badge badge-success"><i class="fa fa-check"></i> Deducted in Salary</span>
                            @else
                                <span class="badge badge-warning"><i class="fa fa-clock"></i> Pending Deduction</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            @if(!$adv->is_deducted)
                            <form action="{{ route('employee-advances.destroy', $adv) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this advance?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <span style="font-size:11px; color:var(--text-muted);">Locked</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px 20px; border-top:1px solid #f1f5f9;">
            {{ $advances->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create Advance Modal -->
<div class="modal-overlay" id="advanceModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-hand-holding-dollar text-primary"></i> Issue Staff Advance (ઉપાડ)</h3>
            <button class="modal-close" onclick="closeModal('advanceModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('employee-advances.store') }}">
            @csrf
            <div class="modal-body">
                <div style="margin-bottom:14px;">
                    <label class="form-label required">Select Employee</label>
                    <select name="employee_id" required class="form-control">
                        <option value="">-- Choose Employee --</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->emp_code }}) - {{ $e->designation }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Advance Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Advance Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" required placeholder="e.g. 2000" class="form-control">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Payment Mode</label>
                        <select name="payment_mode" required class="form-control">
                            <option value="Cash">Cash (રોકડા)</option>
                            <option value="UPI">UPI (Google Pay / Paytm)</option>
                            <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Deduct in Month</label>
                        <input type="month" name="salary_month" value="{{ date('Y-m') }}" class="form-control">
                    </div>
                </div>
                <div>
                    <label class="form-label">Reason / Notes</label>
                    <textarea name="reason" rows="2" placeholder="e.g. Festival advance, hospital, personal..." class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('advanceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Advance</button>
            </div>
        </form>
    </div>
</div>

<script>
window.openCreateAdvanceModal = function() {
    openModal('advanceModal');
};
</script>
@endsection
