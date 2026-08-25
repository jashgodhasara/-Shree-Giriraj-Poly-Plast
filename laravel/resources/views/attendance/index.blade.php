@extends('layouts.app')
@section('title', 'Daily Attendance Register — Shree Giriraj Poly Plast')
@section('page-title', 'Daily Attendance Register')

@section('content')
<!-- Top Action Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-dark, #0f172a); margin:0; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-clipboard-user" style="color:var(--primary, #6366f1);"></i> Daily Attendance Register (હાજરી પત્રક)
        </h2>
        <p style="font-size:13px; color:var(--text-muted, #64748b); margin:4px 0 0 0;">Mark daily plant presence, half-days, leaves &amp; calculate overtime (OT) hours</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('attendance.monthly') }}" class="btn btn-outline" style="background:#fff;">
            <i class="fa-solid fa-calendar-days"></i> 31-Day Monthly View
        </a>
        <form method="POST" action="{{ route('attendance.mark-all-present') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <button type="submit" class="btn btn-primary" onclick="return confirm('Mark all active workers present for today?');">
                <i class="fa-solid fa-check-double"></i> 1-Click All Present
            </button>
        </form>
    </div>
</div>

<!-- KPI Metric Cards Grid for Date -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
            <span class="badge badge-purple">{{ date('d M Y', strtotime($date)) }}</span>
        </div>
        <div class="stat-label">Total Active Staff</div>
        <div class="stat-value">{{ number_format($totalActive) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <span class="badge badge-success">Present</span>
        </div>
        <div class="stat-label">Present on Plant Floor</div>
        <div class="stat-value">{{ number_format($presentCount) }}</div>
    </div>
    <div class="stat-card s-red">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
            <span class="badge badge-danger">Absent</span>
        </div>
        <div class="stat-label">Absent Staff</div>
        <div class="stat-value">{{ number_format($absentCount) }}</div>
    </div>
    <div class="stat-card s-amber">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <span class="badge badge-warning">Half Day</span>
        </div>
        <div class="stat-label">Half Day Working</div>
        <div class="stat-value">{{ number_format($halfDayCount) }}</div>
    </div>
    <div class="stat-card s-purple">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-business-time"></i></div>
            <span class="badge badge-purple">Overtime</span>
        </div>
        <div class="stat-label">Total OT Hours Today</div>
        <div class="stat-value">{{ number_format($totalOtHours, 1) }} hrs</div>
    </div>
</div>

<!-- Date Selector Bar -->
<div class="card" style="margin-bottom:20px; padding:16px;">
    <form method="GET" action="{{ route('attendance.index') }}" style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:8px;">
            <label style="font-weight:700; font-size:14px; color:var(--text-dark); white-space:nowrap;"><i class="fa fa-calendar text-primary"></i> Select Date:</label>
            <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()" style="font-weight:700;">
        </div>
        <div>
            <select name="department" class="form-control" onchange="this.form.submit()">
                <option value="">All Departments</option>
                <option value="Production" {{ $department === 'Production' ? 'selected' : '' }}>Production (પ્લાન્ટ ઓપરેટર)</option>
                <option value="Tool Room" {{ $department === 'Tool Room' ? 'selected' : '' }}>Tool Room (ડાઈ)</option>
                <option value="Quality" {{ $department === 'Quality' ? 'selected' : '' }}>Quality</option>
                <option value="Maintenance" {{ $department === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="Accounts & Admin" {{ $department === 'Accounts & Admin' ? 'selected' : '' }}>Accounts</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-secondary"><i class="fa fa-sync"></i> Refresh</button>
        </div>
    </form>
</div>

<!-- Attendance Form Sheet -->
<form method="POST" action="{{ route('attendance.store-daily') }}">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">
    
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:16px; font-weight:700;">
                <i class="fa-solid fa-list-check text-primary"></i> Attendance Sheet for {{ date('l, d F Y', strtotime($date)) }}
            </h3>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Save Attendance Changes
            </button>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Employee Code &amp; Name</th>
                            <th>Designation &amp; Shift</th>
                            <th>Attendance Status</th>
                            <th>Overtime (OT Hours)</th>
                            <th>Remarks / Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $emp)
                        @php
                            $att = $attendances->get($emp->id);
                            $currentStatus = $att ? $att->status : 'Present';
                            $otHours = $att ? $att->overtime_hours : 0;
                        @endphp
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:36px; height:36px; border-radius:50%; background:#eef2ff; color:#4f46e5; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; flex-shrink:0;">
                                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:700; color:var(--text-dark); font-size:14px;">{{ $emp->name }}</div>
                                        <code style="font-size:11px; background:#f1f5f9; color:#475569; padding:1px 5px; border-radius:4px;">{{ $emp->emp_code }}</code>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:13px;">{{ $emp->designation }}</div>
                                <span style="font-size:11px; color:var(--text-muted);"><i class="fa fa-clock"></i> {{ $emp->shift }}</span>
                            </td>
                            <td>
                                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                    <label style="display:flex; align-items:center; gap:4px; font-size:13px; font-weight:600; cursor:pointer; padding:4px 8px; border-radius:6px; background:#f0fdf4; border:1px solid #bbf7d0;">
                                        <input type="radio" name="attendance[{{ $emp->id }}][status]" value="Present" {{ $currentStatus === 'Present' ? 'checked' : '' }}>
                                        <span style="color:#166534;">Present (હાજર)</span>
                                    </label>
                                    <label style="display:flex; align-items:center; gap:4px; font-size:13px; font-weight:600; cursor:pointer; padding:4px 8px; border-radius:6px; background:#fef2f2; border:1px solid #fecaca;">
                                        <input type="radio" name="attendance[{{ $emp->id }}][status]" value="Absent" {{ $currentStatus === 'Absent' ? 'checked' : '' }}>
                                        <span style="color:#991b1b;">Absent (ગેરહાજર)</span>
                                    </label>
                                    <label style="display:flex; align-items:center; gap:4px; font-size:13px; font-weight:600; cursor:pointer; padding:4px 8px; border-radius:6px; background:#fffbeb; border:1px solid #fde68a;">
                                        <input type="radio" name="attendance[{{ $emp->id }}][status]" value="Half Day" {{ $currentStatus === 'Half Day' ? 'checked' : '' }}>
                                        <span style="color:#92400e;">Half Day</span>
                                    </label>
                                    <label style="display:flex; align-items:center; gap:4px; font-size:13px; font-weight:600; cursor:pointer; padding:4px 8px; border-radius:6px; background:#f8fafc; border:1px solid #e2e8f0;">
                                        <input type="radio" name="attendance[{{ $emp->id }}][status]" value="Paid Leave" {{ $currentStatus === 'Paid Leave' ? 'checked' : '' }}>
                                        <span style="color:#475569;">Leave / Holiday</span>
                                    </label>
                                </div>
                            </td>
                            <td style="width:140px;">
                                <div style="display:flex; align-items:center; gap:4px;">
                                    <input type="number" step="0.5" min="0" max="12" name="attendance[{{ $emp->id }}][overtime_hours]" value="{{ $otHours }}" class="form-control" style="width:75px; font-weight:700; text-align:center;">
                                    <span style="font-size:12px; color:var(--text-muted);">hrs</span>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="attendance[{{ $emp->id }}][remarks]" value="{{ $att?->remarks }}" placeholder="Optional notes..." class="form-control" style="font-size:12.5px;">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 20px; display:flex; justify-content:flex-end; border-top:1px solid #f1f5f9;">
                <button type="submit" class="btn btn-primary" style="padding:10px 24px;">
                    <i class="fa-solid fa-floppy-disk"></i> Save &amp; Submit Attendance
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
