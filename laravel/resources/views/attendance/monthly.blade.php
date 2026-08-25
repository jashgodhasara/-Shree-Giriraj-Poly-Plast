@extends('layouts.app')
@section('title', 'Monthly Attendance Matrix — Shree Giriraj Poly Plast')
@section('page-title', 'Monthly Attendance Matrix')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-dark, #0f172a); margin:0; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-calendar-days text-primary"></i> Monthly Attendance Matrix (31-Day Grid)
        </h2>
        <p style="font-size:13px; color:var(--text-muted, #64748b); margin:4px 0 0 0;">Day-by-day attendance grid for all factory personnel</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-clipboard-user"></i> Daily Entry View
        </a>
        <a href="{{ route('payroll.index', ['month_year' => $monthYear]) }}" class="btn btn-primary">
            <i class="fa-solid fa-file-invoice-dollar"></i> Process Monthly Payroll
        </a>
    </div>
</div>

<!-- Month Selector Bar -->
<div class="card" style="margin-bottom:20px; padding:16px;">
    <form method="GET" action="{{ route('attendance.monthly') }}" style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:8px;">
            <label style="font-weight:700; font-size:14px; color:var(--text-dark);"><i class="fa fa-calendar-alt text-primary"></i> Select Month:</label>
            <input type="month" name="month_year" value="{{ $monthYear }}" class="form-control" onchange="this.form.submit()" style="font-weight:700;">
        </div>
        <div>
            <select name="department" class="form-control" onchange="this.form.submit()">
                <option value="">All Departments</option>
                <option value="Production" {{ $department === 'Production' ? 'selected' : '' }}>Production</option>
                <option value="Tool Room" {{ $department === 'Tool Room' ? 'selected' : '' }}>Tool Room</option>
                <option value="Quality" {{ $department === 'Quality' ? 'selected' : '' }}>Quality</option>
                <option value="Maintenance" {{ $department === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="Accounts & Admin" {{ $department === 'Accounts & Admin' ? 'selected' : '' }}>Accounts &amp; Admin</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-secondary"><i class="fa fa-sync"></i> Refresh</button>
        </div>
    </form>
</div>

<!-- 31-Day Attendance Matrix Table -->
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0; font-size:16px; font-weight:700;">
            <i class="fa-solid fa-table-cells text-primary"></i> Attendance Sheet for {{ $startOfMonth->format('F Y') }} ({{ $employees->count() }} Employees)
        </h3>
        <div style="display:flex; gap:12px; font-size:12px; font-weight:600;">
            <span style="color:#16a34a;"><strong style="background:#dcfce7; padding:2px 6px; border-radius:4px;">P</strong> Present</span>
            <span style="color:#dc2626;"><strong style="background:#fee2e2; padding:2px 6px; border-radius:4px;">A</strong> Absent</span>
            <span style="color:#d97706;"><strong style="background:#fef3c7; padding:2px 6px; border-radius:4px;">HD</strong> Half Day</span>
            <span style="color:#6b7280;"><strong style="background:#f3f4f6; padding:2px 6px; border-radius:4px;">WO</strong> Weekly Off</span>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap" style="overflow-x:auto;">
            <table style="font-size:12px; min-width:1200px;">
                <thead>
                    <tr>
                        <th style="position:sticky; left:0; background:#f8fafc; z-index:2; width:180px;">Employee</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $currDate = sprintf('%s-%02d', $monthYear, $d);
                            $isSun = \Carbon\Carbon::parse($currDate)->isSunday();
                        @endphp
                        <th style="text-align:center; padding:6px 2px; width:30px; {{ $isSun ? 'background:#fef2f2; color:#ef4444;' : '' }}">
                            <div>{{ $d }}</div>
                            <div style="font-size:9px; font-weight:normal;">{{ substr(\Carbon\Carbon::parse($currDate)->format('D'), 0, 1) }}</div>
                        </th>
                        @endfor
                        <th style="text-align:center; background:#f1f5f9;">P</th>
                        <th style="text-align:center; background:#f1f5f9;">HD</th>
                        <th style="text-align:center; background:#f1f5f9;">A</th>
                        <th style="text-align:center; background:#f1f5f9; font-weight:700;">Pay Days</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                    @php
                        $empAtts = $allAttendances->get($emp->id, collect())->keyBy(function($item) {
                            return $item->date->format('j');
                        });
                        $pCount = 0;
                        $hdCount = 0;
                        $aCount = 0;
                        $woCount = 0;
                    @endphp
                    <tr>
                        <td style="position:sticky; left:0; background:#fff; z-index:1; font-weight:700; white-space:nowrap; border-right:1px solid #e2e8f0;">
                            <div>{{ $emp->name }}</div>
                            <div style="font-size:10px; color:var(--text-muted);">{{ $emp->emp_code }}</div>
                        </td>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $currDate = sprintf('%s-%02d', $monthYear, $d);
                            $isSun = \Carbon\Carbon::parse($currDate)->isSunday();
                            $att = $empAtts->get($d);
                            $st = $att ? $att->status : ($isSun ? 'Holiday' : '—');

                            if ($st === 'Present') $pCount++;
                            elseif ($st === 'Half Day') $hdCount++;
                            elseif ($st === 'Absent') $aCount++;
                            elseif ($st === 'Holiday' || $st === 'Paid Leave') $woCount++;
                        @endphp
                        <td style="text-align:center; padding:4px 1px; {{ $isSun ? 'background:#fafafa;' : '' }}">
                            @if($st === 'Present')
                                <span style="display:inline-block; width:22px; height:22px; line-height:22px; border-radius:4px; background:#dcfce7; color:#166534; font-weight:700; font-size:10px;">P</span>
                            @elseif($st === 'Half Day')
                                <span style="display:inline-block; width:22px; height:22px; line-height:22px; border-radius:4px; background:#fef3c7; color:#92400e; font-weight:700; font-size:9px;">HD</span>
                            @elseif($st === 'Absent')
                                <span style="display:inline-block; width:22px; height:22px; line-height:22px; border-radius:4px; background:#fee2e2; color:#991b1b; font-weight:700; font-size:10px;">A</span>
                            @elseif($st === 'Holiday' || $st === 'Paid Leave')
                                <span style="display:inline-block; width:22px; height:22px; line-height:22px; border-radius:4px; background:#f1f5f9; color:#64748b; font-weight:600; font-size:9px;">WO</span>
                            @else
                                <span style="color:#cbd5e1;">-</span>
                            @endif
                        </td>
                        @endfor
                        <td style="text-align:center; font-weight:700; color:#16a34a; background:#f8fafc;">{{ $pCount }}</td>
                        <td style="text-align:center; font-weight:700; color:#d97706; background:#f8fafc;">{{ $hdCount }}</td>
                        <td style="text-align:center; font-weight:700; color:#dc2626; background:#f8fafc;">{{ $aCount }}</td>
                        <td style="text-align:center; font-weight:800; color:var(--primary); background:#eef2ff;">
                            {{ $pCount + ($hdCount * 0.5) + $woCount }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
