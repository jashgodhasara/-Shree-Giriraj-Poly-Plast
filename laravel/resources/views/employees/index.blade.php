@extends('layouts.app')
@section('title', 'Staff & Employee Directory — Shree Giriraj Poly Plast')
@section('page-title', 'Staff Management')

@section('content')
<!-- Top Action Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-dark, #0f172a); margin:0; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-users-gear" style="color:var(--primary, #6366f1);"></i> Staff &amp; Employee Directory
        </h2>
        <p style="font-size:13px; color:var(--text-muted, #64748b); margin:4px 0 0 0;">Machine operators, helpers, supervisors, daily/monthly wages &amp; shift rosters</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('attendance.index') }}" class="btn btn-outline" style="background:#fff;">
            <i class="fa-solid fa-clipboard-user"></i> Daily Attendance
        </a>
        <a href="{{ route('payroll.index') }}" class="btn btn-outline" style="background:#fff;">
            <i class="fa-solid fa-file-invoice-dollar"></i> Salary Payroll
        </a>
        <button class="btn btn-primary" onclick="openCreateEmployeeModal()">
            <i class="fa fa-plus"></i> Register New Staff
        </button>
    </div>
</div>

<!-- KPI Metric Cards Grid -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-id-card"></i></div>
            <span class="badge badge-purple">Total Staff</span>
        </div>
        <div class="stat-label">Total Registered Staff</div>
        <div class="stat-value">{{ number_format($totalCount) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-user-check"></i></div>
            <span class="badge badge-success">Active</span>
        </div>
        <div class="stat-label">Active Working Employees</div>
        <div class="stat-value">{{ number_format($activeCount) }}</div>
    </div>
    <div class="stat-card s-blue">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
            <span class="badge badge-info">Monthly</span>
        </div>
        <div class="stat-label">Monthly Fixed Salary Staff</div>
        <div class="stat-value">{{ number_format($monthlyStaff) }}</div>
    </div>
    <div class="stat-card s-amber">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
            <span class="badge badge-warning">Daily Wage</span>
        </div>
        <div class="stat-label">Daily Wage / Rojamdar Staff</div>
        <div class="stat-value">{{ number_format($dailyStaff) }}</div>
    </div>
    <div class="stat-card s-purple">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-indian-rupee-sign"></i></div>
            <span class="badge badge-purple">Monthly Bill</span>
        </div>
        <div class="stat-label">Active Monthly Wage Base</div>
        <div class="stat-value">₹{{ number_format((float)$totalWageBill, 0) }}</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom:20px; padding:16px;">
    <form method="GET" action="{{ route('employees.index') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 120px; gap:12px; align-items:center;">
        <div>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search Name, EMP Code, Phone..." class="form-control" style="width:100%;">
        </div>
        <div>
            <select name="department" class="form-control" style="width:100%;">
                <option value="">All Departments</option>
                <option value="Production" {{ $department === 'Production' ? 'selected' : '' }}>Production (પ્લાન્ટ ઓપરેટર)</option>
                <option value="Tool Room" {{ $department === 'Tool Room' ? 'selected' : '' }}>Tool Room (ડાઈ મેન્ટેનન્સ)</option>
                <option value="Quality" {{ $department === 'Quality' ? 'selected' : '' }}>Quality &amp; Dispatch</option>
                <option value="Maintenance" {{ $department === 'Maintenance' ? 'selected' : '' }}>Maintenance (ઇલેક્ટ્રિશિયન)</option>
                <option value="Accounts & Admin" {{ $department === 'Accounts & Admin' ? 'selected' : '' }}>Accounts &amp; Admin</option>
            </select>
        </div>
        <div>
            <select name="status" class="form-control" style="width:100%;">
                <option value="">All Statuses</option>
                <option value="Active" {{ $status === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ $status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="On Leave" {{ $status === 'On Leave' ? 'selected' : '' }}>On Leave</option>
            </select>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fa fa-filter"></i> Filter</button>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary" title="Reset Filters"><i class="fa fa-redo"></i></a>
        </div>
    </form>
</div>

<!-- Employees Directory Table -->
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-users text-primary"></i> Employees Directory ({{ $employees->total() }})</h3>
    </div>
    <div class="card-body" style="padding:0;">
        @if($employees->isEmpty())
        <div class="empty-state" style="padding:48px 20px; text-align:center;">
            <div class="empty-icon" style="font-size:40px; color:#cbd5e1; margin-bottom:12px;"><i class="fa-solid fa-users-gear"></i></div>
            <p style="color:var(--text-muted); font-size:15px; margin-bottom:16px;">No staff records found matching the criteria.</p>
            <button class="btn btn-primary btn-sm" onclick="openCreateEmployeeModal()">Register First Staff</button>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employee Profile</th>
                        <th>Designation &amp; Dept</th>
                        <th>Shift &amp; Timings</th>
                        <th>Salary Wage Rate</th>
                        <th>Bank / UPI</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:42px; height:42px; border-radius:50%; background:#eef2ff; color:#4f46e5; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:700; flex-shrink:0; overflow:hidden; border:2px solid #e2e8f0;">
                                    @if($emp->photo)
                                        <img src="{{ $emp->photo_url }}" alt="{{ $emp->name }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('employees.show', $emp) }}" style="font-weight:700; color:var(--text-dark, #0f172a); text-decoration:none; font-size:14px;">{{ $emp->name }}</a>
                                    <div style="display:flex; align-items:center; gap:8px; margin-top:2px;">
                                        <code style="background:#f1f5f9; color:#475569; padding:1px 6px; border-radius:4px; font-size:11px; font-weight:700;">{{ $emp->emp_code }}</code>
                                        @if($emp->phone)
                                            <a href="tel:{{ $emp->phone }}" style="font-size:11px; color:var(--text-muted); text-decoration:none;"><i class="fa fa-phone"></i> {{ $emp->phone }}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:600; font-size:13px; color:var(--text-dark);">{{ $emp->designation }}</div>
                            <span class="badge badge-purple" style="font-size:11px; margin-top:2px;">{{ $emp->department }}</span>
                        </td>
                        <td>
                            <div style="font-size:12px; font-weight:600; color:var(--text-dark); display:flex; align-items:center; gap:4px;">
                                <i class="fa fa-clock text-muted"></i> {{ $emp->shift }}
                            </div>
                            @if($emp->joining_date)
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Joined: {{ $emp->joining_date->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:700; font-size:13px; color:var(--text-dark); font-family:monospace;">
                                {{ $emp->formatted_salary }}
                            </div>
                            @if($emp->overtime_hourly_rate > 0)
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">OT: ₹{{ number_format((float)$emp->overtime_hourly_rate, 2) }}/hr</div>
                            @endif
                        </td>
                        <td>
                            @if($emp->upi_id)
                                <div style="font-size:12px; font-weight:600; color:var(--primary);"><i class="fa-solid fa-mobile-screen"></i> {{ $emp->upi_id }}</div>
                            @elseif($emp->account_number)
                                <div style="font-size:11px; color:var(--text-dark); font-family:monospace;"><i class="fa fa-building-columns text-muted"></i> A/C: {{ substr($emp->account_number, -4) }} ({{ $emp->bank_name }})</div>
                            @else
                                <span style="font-size:11px; color:var(--text-muted);">Cash / Direct</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $emp->status === 'Active' ? 'badge-success' : 'badge-danger' }}">
                                {{ $emp->status }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; gap:6px; justify-content:flex-end;">
                                <a href="{{ route('employees.show', $emp) }}" class="btn btn-outline btn-sm btn-icon" title="View Full Employee Profile &amp; Attendance History">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <button class="btn btn-outline btn-sm btn-icon" title="Edit Employee" onclick="openEditEmployeeModal({{ $emp->id }})">
                                    <i class="fa fa-pen"></i>
                                </button>
                                <form action="{{ route('employees.destroy', $emp) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this employee?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px 20px; border-top:1px solid #f1f5f9;">
            {{ $employees->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add / Edit Employee Modal -->
<div class="modal-overlay" id="employeeModal">
    <div class="modal" style="max-width:720px;">
        <div class="modal-header">
            <h3 id="employeeModalTitle"><i class="fa-solid fa-user-plus text-primary"></i> Register New Staff</h3>
            <button class="modal-close" onclick="closeModal('employeeModal')">✕</button>
        </div>
        <form id="employeeForm" method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
            @csrf
            <div id="employeeMethodField"></div>
            <div class="modal-body">
                <div style="display:grid; grid-template-columns:1fr 2fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Employee Code</label>
                        <input type="text" name="emp_code" id="emp_code" placeholder="Auto (e.g. EMP-001)" class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Employee Full Name</label>
                        <input type="text" name="name" id="emp_name" required placeholder="e.g. Ramesh Patel" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Phone Number (Mobile)</label>
                        <input type="text" name="phone" id="emp_phone" placeholder="e.g. +91 98765 43210" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Email Address (Optional)</label>
                        <input type="email" name="email" id="emp_email" placeholder="staff@shreegiriraj.com" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Designation / Role</label>
                        <input type="text" name="designation" id="emp_designation" required placeholder="e.g. Injection Operator" class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Department</label>
                        <select name="department" id="emp_department" required class="form-control">
                            <option value="Production">Production (પ્લાન્ટ)</option>
                            <option value="Tool Room">Tool Room (ડાઈ)</option>
                            <option value="Quality">Quality &amp; Dispatch</option>
                            <option value="Maintenance">Maintenance (ઇલેક્ટ્રિક)</option>
                            <option value="Accounts & Admin">Accounts &amp; Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label required">Shift</label>
                        <select name="shift" id="emp_shift" required class="form-control">
                            <option value="Day Shift (8 AM - 8 PM)">Day Shift (8 AM - 8 PM)</option>
                            <option value="Night Shift (8 PM - 8 AM)">Night Shift (8 PM - 8 AM)</option>
                            <option value="General Shift (9 AM - 6 PM)">General Shift (9 AM - 6 PM)</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Salary Type</label>
                        <select name="salary_type" id="emp_salary_type" required class="form-control">
                            <option value="Monthly">Monthly Fixed Salary (માસિક પગાર)</option>
                            <option value="Daily Wage">Daily Wage (રોજમદાર / દિવસનો દર)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label required">Base Rate / Salary (₹)</label>
                        <input type="number" step="0.01" name="base_salary" id="emp_base_salary" required placeholder="e.g. 25000 or 600" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Overtime Hourly Rate (₹)</label>
                        <input type="number" step="0.01" name="overtime_hourly_rate" id="emp_overtime_hourly_rate" placeholder="e.g. 120" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">UPI ID (for Direct Pay)</label>
                        <input type="text" name="upi_id" id="emp_upi_id" placeholder="name@upi / phone@paytm" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Bank Account Number</label>
                        <input type="text" name="account_number" id="emp_account_number" placeholder="Account No" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">IFSC Code / Bank Name</label>
                        <input type="text" name="ifsc_code" id="emp_ifsc_code" placeholder="e.g. SBIN0001234" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Joining Date</label>
                        <input type="date" name="joining_date" id="emp_joining_date" class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Status</label>
                        <select name="status" id="emp_status" required class="form-control">
                            <option value="Active">Active (કાર્યરત)</option>
                            <option value="Inactive">Inactive</option>
                            <option value="On Leave">On Leave</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="photo" accept="image/*" class="form-control">
                    </div>
                </div>

                <div>
                    <label class="form-label">Residential Address &amp; Emergency Contact</label>
                    <textarea name="address" id="emp_address" rows="2" placeholder="Local address, native village, emergency contact phone..." class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('employeeModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Employee Profile</button>
            </div>
        </form>
    </div>
</div>

<script>
window.employeesData = @json($employees->items());
window.employeesMap = {};
window.employeesData.forEach(function(e) { window.employeesMap[e.id] = e; });

window.openCreateEmployeeModal = function() {
    document.getElementById('employeeModalTitle').innerHTML = '<i class="fa-solid fa-user-plus text-primary"></i> Register New Staff';
    document.getElementById('employeeForm').action = "{{ route('employees.store') }}";
    document.getElementById('employeeMethodField').innerHTML = '';
    document.getElementById('employeeForm').reset();
    openModal('employeeModal');
};

window.openEditEmployeeModal = function(empId) {
    const emp = window.employeesMap[empId] || {};
    document.getElementById('employeeModalTitle').innerHTML = '<i class="fa-solid fa-user-pen text-primary"></i> Edit Staff: ' + (emp.name || '');
    document.getElementById('employeeForm').action = "/employees/" + (emp.id || empId);
    document.getElementById('employeeMethodField').innerHTML = '@method("PUT")';
    
    document.getElementById('emp_code').value = emp.emp_code || '';
    document.getElementById('emp_name').value = emp.name || '';
    document.getElementById('emp_phone').value = emp.phone || '';
    document.getElementById('emp_email').value = emp.email || '';
    document.getElementById('emp_designation').value = emp.designation || '';
    document.getElementById('emp_department').value = emp.department || 'Production';
    document.getElementById('emp_shift').value = emp.shift || 'Day Shift (8 AM - 8 PM)';
    document.getElementById('emp_salary_type').value = emp.salary_type || 'Monthly';
    document.getElementById('emp_base_salary').value = emp.base_salary || '';
    document.getElementById('emp_overtime_hourly_rate').value = emp.overtime_hourly_rate || '';
    document.getElementById('emp_upi_id').value = emp.upi_id || '';
    document.getElementById('emp_account_number').value = emp.account_number || '';
    document.getElementById('emp_ifsc_code').value = emp.ifsc_code || '';
    document.getElementById('emp_joining_date').value = emp.joining_date ? emp.joining_date.substring(0, 10) : '';
    document.getElementById('emp_status').value = emp.status || 'Active';
    document.getElementById('emp_address').value = emp.address || '';
    
    openModal('employeeModal');
};
</script>
@endsection
