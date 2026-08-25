@extends('layouts.app')
@section('title', $employee->name . ' — Employee Profile & History')
@section('page-title', 'Employee Profile: ' . $employee->name)

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom:8px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fa fa-arrow-left"></i> Back to Staff Directory
        </a>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-dark, #0f172a); margin:0; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-user-tie text-primary"></i> {{ $employee->name }}
        </h2>
        <div style="display:flex; align-items:center; gap:8px; margin-top:6px;">
            <code style="background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:4px; font-weight:700; font-size:12px;">{{ $employee->emp_code }}</code>
            <span class="badge badge-purple">{{ $employee->designation }}</span>
            <span class="badge badge-indigo">{{ $employee->department }}</span>
            <span class="badge {{ $employee->status === 'Active' ? 'badge-success' : 'badge-danger' }}">{{ $employee->status }}</span>
        </div>
    </div>
    <div style="display:flex; gap:10px;">
        <button class="btn btn-primary" onclick="openModal('quickAdvanceModal')">
            <i class="fa-solid fa-hand-holding-dollar"></i> Give Advance (ઉપાડ)
        </button>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 2fr; gap:24px; margin-bottom:24px;">
    {{-- Left Card: Employee Profile Specs --}}
    <div class="card">
        <div class="card-header">
            <h3 style="margin:0; font-size:15px; font-weight:700;"><i class="fa-solid fa-id-card-clip text-primary"></i> Personal &amp; Wage Details</h3>
        </div>
        <div class="card-body">
            @if($employee->photo)
            <div style="margin-bottom:16px; border-radius:12px; overflow:hidden; border:1px solid #e2e8f0; max-height:200px; display:flex; align-items:center; justify-content:center; background:#f8fafc;">
                <img src="{{ $employee->photo_url }}" alt="{{ $employee->name }}" style="max-height:190px; width:100%; object-fit:contain;">
            </div>
            @endif

            <table style="width:100%; font-size:13px;">
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Shift Roster:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $employee->shift }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Salary Basis:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $employee->salary_type }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Wage Rate:</td>
                    <td style="padding:8px 0; font-weight:700; font-family:monospace; text-align:right; color:var(--text-dark);">{{ $employee->formatted_salary }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Overtime Rate:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">₹{{ number_format((float)$employee->overtime_hourly_rate, 2) }} / hr</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Joining Date:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $employee->joining_date ? $employee->joining_date->format('d M Y') : '—' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Mobile:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $employee->phone ?: '—' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">UPI ID:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right; color:var(--primary);">{{ $employee->upi_id ?: '—' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Bank A/C:</td>
                    <td style="padding:8px 0; font-weight:700; font-family:monospace; text-align:right;">{{ $employee->account_number ? substr($employee->account_number, -4) . ' (' . $employee->bank_name . ')' : '—' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Total Upad (Advances):</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right; color:var(--danger, #ef4444);">₹{{ number_format((float)$totalAdvancesTaken, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:var(--text-muted);">Total Salary Received:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right; color:var(--success, #10b981);">₹{{ number_format((float)$totalSalaryPaid, 2) }}</td>
                </tr>
            </table>

            @if($employee->address)
            <div style="margin-top:16px; padding:12px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; font-size:12.5px; color:#475569;">
                <strong style="display:block; margin-bottom:4px; color:var(--text-dark);">Address &amp; Emergency:</strong>
                {{ $employee->address }}
            </div>
            @endif
        </div>
    </div>

    {{-- Right Card: Recent Attendance & Advances --}}
    <div style="display:flex; flex-direction:column; gap:24px;">
        <!-- Recent Attendance Log -->
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:15px; font-weight:700;"><i class="fa-solid fa-clipboard-check text-success"></i> Recent Attendance (Last 30 Days)</h3>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Working Hours</th>
                                <th>Overtime (OT)</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->attendances->take(15) as $att)
                            <tr>
                                <td style="font-weight:600;">{{ $att->date->format('d M Y (D)') }}</td>
                                <td>
                                    @if($att->status === 'Present')
                                        <span class="badge badge-success"><i class="fa fa-check"></i> Present</span>
                                    @elseif($att->status === 'Half Day')
                                        <span class="badge badge-warning">Half Day</span>
                                    @elseif($att->status === 'Absent')
                                        <span class="badge badge-danger">Absent</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $att->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $att->working_hours }} hrs</td>
                                <td>
                                    @if($att->overtime_hours > 0)
                                        <span class="badge badge-purple">+{{ $att->overtime_hours }} hrs OT</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="font-size:12px; color:var(--text-muted);">{{ $att->remarks ?: '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:24px; color:var(--text-muted);">No attendance records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Advances Log (ઉપાડ) -->
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:15px; font-weight:700;"><i class="fa-solid fa-hand-holding-dollar text-warning"></i> Advances &amp; Upad Records</h3>
                <button class="btn btn-primary btn-sm" onclick="openModal('quickAdvanceModal')"><i class="fa fa-plus"></i> New Advance</button>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date &amp; Month</th>
                                <th>Amount</th>
                                <th>Mode</th>
                                <th>Reason</th>
                                <th>Deduction Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->advances as $adv)
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $adv->date->format('d M Y') }}</div>
                                    <div style="font-size:11px; color:var(--text-muted);">Month: {{ $adv->salary_month }}</div>
                                </td>
                                <td style="font-weight:700; color:var(--danger);">₹{{ number_format((float)$adv->amount, 2) }}</td>
                                <td><span class="badge badge-info">{{ $adv->payment_mode }}</span></td>
                                <td style="font-size:12px;">{{ $adv->reason ?: 'General Advance' }}</td>
                                <td>
                                    @if($adv->is_deducted)
                                        <span class="badge badge-success"><i class="fa fa-check"></i> Deducted in Salary</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fa fa-clock"></i> Pending Deduction</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:20px; color:var(--text-muted);">No advance records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Advance Modal -->
<div class="modal-overlay" id="quickAdvanceModal">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-hand-holding-dollar text-warning"></i> Issue Advance to {{ $employee->name }}</h3>
            <button class="modal-close" onclick="closeModal('quickAdvanceModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('employee-advances.store') }}">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <div class="modal-body">
                <div style="margin-bottom:14px;">
                    <label class="form-label required">Advance Date</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="form-control">
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label required">Advance Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" required placeholder="e.g. 2000" class="form-control">
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
                        <label class="form-label">Deduct in Salary Month</label>
                        <input type="month" name="salary_month" value="{{ date('Y-m') }}" class="form-control">
                    </div>
                </div>
                <div>
                    <label class="form-label">Reason / Notes</label>
                    <input type="text" name="reason" placeholder="e.g. Festival advance, medical..." class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('quickAdvanceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Advance</button>
            </div>
        </form>
    </div>
</div>
@endsection
