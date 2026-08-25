<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Slip — {{ $payroll->employee->name }} ({{ $payroll->month_year }})</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 24px;
        }
        .payslip-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .company-name {
            font-size: 20px;
            font-weight: 800;
            color: #1e1b4b;
            margin: 0;
        }
        .company-sub {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        .slip-title {
            text-align: right;
        }
        .slip-title h2 {
            font-size: 18px;
            font-weight: 800;
            color: #4f46e5;
            margin: 0;
        }
        .slip-title p {
            font-size: 12px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .emp-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            font-size: 13px;
        }
        .emp-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
        .emp-label {
            color: #64748b;
        }
        .emp-val {
            font-weight: 700;
            color: #0f172a;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 13px;
        }
        .salary-table th {
            background: #f1f5f9;
            padding: 10px 14px;
            text-align: left;
            font-weight: 700;
            color: #334155;
            border-bottom: 2px solid #cbd5e1;
        }
        .salary-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .total-row {
            background: #eef2ff;
            font-weight: 800;
            font-size: 14px;
            color: #1e1b4b;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 48px;
            padding-top: 24px;
        }
        .sig-box {
            width: 200px;
            text-align: center;
            border-top: 1px dashed #94a3b8;
            padding-top: 8px;
            font-size: 12px;
            color: #64748b;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print {
            padding: 10px 24px;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .payslip-container { box-shadow: none; border: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <div class="payslip-container">
        <div class="header">
            <div>
                <h1 class="company-name">SHREE GIRIRAJ POLY PLAST</h1>
                <div class="company-sub">Plastic Processing, Moulding &amp; Packaging | Vatva GIDC, Ahmedabad</div>
            </div>
            <div class="slip-title">
                <h2>SALARY PAYSLIP</h2>
                <p>Month: <strong>{{ date('F Y', strtotime($payroll->month_year . '-01')) }}</strong></p>
                <p>Pay Slip #: <strong>{{ $payroll->payroll_number }}</strong></p>
            </div>
        </div>

        <div class="emp-grid">
            <div>
                <div class="emp-row">
                    <span class="emp-label">Employee Name:</span>
                    <span class="emp-val">{{ $payroll->employee->name }}</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">Employee Code:</span>
                    <span class="emp-val">{{ $payroll->employee->emp_code }}</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">Designation:</span>
                    <span class="emp-val">{{ $payroll->employee->designation }}</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">Department:</span>
                    <span class="emp-val">{{ $payroll->employee->department }}</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">Shift:</span>
                    <span class="emp-val">{{ $payroll->employee->shift }}</span>
                </div>
            </div>
            <div>
                <div class="emp-row">
                    <span class="emp-label">Salary Basis:</span>
                    <span class="emp-val">{{ $payroll->employee->salary_type }}</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">Base Rate:</span>
                    <span class="emp-val">{{ $payroll->employee->formatted_salary }}</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">Month Total Days:</span>
                    <span class="emp-val">{{ $payroll->total_month_days }} Days</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">Payable Days:</span>
                    <span class="emp-val" style="color:#16a34a;">{{ $payroll->payable_days }} Days (Present: {{ $payroll->present_days }})</span>
                </div>
                <div class="emp-row">
                    <span class="emp-label">Bank / UPI:</span>
                    <span class="emp-val">{{ $payroll->employee->upi_id ?: ($payroll->employee->account_number ? substr($payroll->employee->account_number, -4) : 'Cash') }}</span>
                </div>
            </div>
        </div>

        <table class="salary-table">
            <thead>
                <tr>
                    <th>Earnings &amp; Allowances</th>
                    <th style="text-align:right;">Amount (₹)</th>
                    <th>Deductions &amp; Upad</th>
                    <th style="text-align:right;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Earned Wages ({{ $payroll->payable_days }} Days)</td>
                    <td style="text-align:right; font-weight:700;">₹{{ number_format((float)$payroll->gross_salary, 2) }}</td>
                    <td>Staff Advance (ઉપાડ) Deductions</td>
                    <td style="text-align:right; color:#ef4444; font-weight:700;">₹{{ number_format((float)$payroll->advance_deductions, 2) }}</td>
                </tr>
                <tr>
                    <td>Overtime (OT) Pay ({{ $payroll->total_ot_hours }} hrs)</td>
                    <td style="text-align:right; font-weight:700;">₹{{ number_format((float)$payroll->overtime_amount, 2) }}</td>
                    <td>Other Deductions / Penalties</td>
                    <td style="text-align:right; color:#ef4444; font-weight:700;">₹{{ number_format((float)$payroll->other_deductions, 2) }}</td>
                </tr>
                <tr>
                    <td>Special Allowances / Bonus</td>
                    <td style="text-align:right; font-weight:700;">₹{{ number_format((float)$payroll->bonus_allowances, 2) }}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="total-row">
                    <td>Total Gross Earnings</td>
                    <td style="text-align:right; font-family:'JetBrains Mono', monospace;">₹{{ number_format((float)($payroll->gross_salary + $payroll->overtime_amount + $payroll->bonus_allowances), 2) }}</td>
                    <td>Total Deductions</td>
                    <td style="text-align:right; color:#ef4444; font-family:'JetBrains Mono', monospace;">₹{{ number_format((float)($payroll->advance_deductions + $payroll->other_deductions), 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div style="background:#f0fdf4; border:2px solid #bbf7d0; border-radius:8px; padding:16px; display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div>
                <div style="font-size:12px; color:#166534; font-weight:700; text-transform:uppercase;">Net Salary Payable (ચૂકવવાપાત્ર ચોખ્ખો પગાર)</div>
                <div style="font-size:12px; color:#475569; margin-top:2px;">Status: <strong>{{ $payroll->payment_status }}</strong> @if($payroll->payment_date) (Paid on {{ $payroll->payment_date->format('d M Y') }} via {{ $payroll->payment_mode }}) @endif</div>
            </div>
            <div style="font-size:24px; font-weight:800; color:#15803d; font-family:'JetBrains Mono', monospace;">
                ₹{{ number_format((float)$payroll->net_salary, 2) }}
            </div>
        </div>

        <div class="signatures">
            <div class="sig-box">
                Employee Signature / Thumb
            </div>
            <div class="sig-box">
                Authorized Signatory / Shree Giriraj
            </div>
        </div>
    </div>
</body>
</html>
