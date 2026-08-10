<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delivery Challan {{ $invoice->challan_number ?? $invoice->invoice_number }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',Arial,sans-serif;font-size:10px;color:#1a1a2e;background:#dde3ee}

/* ── TOOLBAR ── */
.no-print{
    position:sticky;top:0;z-index:100;
    padding:10px 24px;
    background:linear-gradient(135deg,#0f172a,#1e293b);
    display:flex;gap:10px;justify-content:space-between;align-items:center;
}
.no-print .toolbar-brand{font-size:13px;font-weight:700;color:#e2e8f0;display:flex;align-items:center;gap:8px}
.no-print .toolbar-brand span{color:#10b981}
.no-print .toolbar-actions{display:flex;gap:8px}
.no-print a,.no-print button{
    padding:8px 16px;border-radius:8px;font-size:12px;font-family:'Inter',sans-serif;
    font-weight:600;cursor:pointer;text-decoration:none;
    display:inline-flex;align-items:center;gap:6px;border:none;transition:all .2s;
}
.btn-back   {background:rgba(255,255,255,.1);color:#e2e8f0;border:1px solid rgba(255,255,255,.15)!important}
.btn-invoice{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff}
.btn-print  {background:linear-gradient(135deg,#10b981,#059669);color:#fff}

/* ── PAGE ── */
.page-wrap{max-width:1120px;margin:20px auto 40px}

/* ── TWO COPIES ── */
.challan-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:12px}

/* ── SINGLE COPY ── */
.ch-copy{
    background:#fff;border-radius:4px;
    box-shadow:0 4px 20px rgba(0,0,0,.12);
    overflow:hidden;
}

/* ── COPY HEADER ── */
.ch-header{
    background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#0f172a 100%);
    padding:14px 16px;display:grid;grid-template-columns:1fr auto;gap:8px;
    align-items:flex-start;position:relative;overflow:hidden;
}
.ch-header::before{
    content:'';position:absolute;top:-30px;right:-30px;
    width:120px;height:120px;border-radius:50%;
    background:radial-gradient(circle,rgba(16,185,129,.2),transparent 70%);
}
.ch-company{font-size:14px;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:.8px;position:relative;z-index:1}
.ch-company span{color:#34d399}
.ch-tagline{font-size:7.5px;color:#94a3b8;margin-top:2px;position:relative;z-index:1}
.ch-addr{font-size:7.5px;color:#64748b;margin-top:3px;line-height:1.6;position:relative;z-index:1}
.ch-gstin{
    display:inline-block;margin-top:5px;font-size:8px;font-weight:700;color:#94a3b8;
    background:rgba(255,255,255,.06);padding:2px 8px;border-radius:3px;
    border:1px solid rgba(255,255,255,.1);letter-spacing:.3px;position:relative;z-index:1;
}
.copy-badge{
    position:relative;z-index:1;
    display:inline-block;padding:4px 10px;
    background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.4);
    border-radius:4px;font-size:8px;font-weight:700;color:#34d399;
    letter-spacing:.8px;text-align:center;white-space:nowrap;
}

/* ── ACCENT ── */
.ch-accent{height:3px;background:linear-gradient(to right,#10b981,#34d399,#6366f1,#0ea5e9)}

/* ── TITLE ── */
.ch-title{
    background:linear-gradient(to right,#f0fdf4,#f8fafc);
    text-align:center;padding:6px;
    font-size:12px;font-weight:800;letter-spacing:2.5px;text-transform:uppercase;color:#065f46;
    border-bottom:1px solid #d1fae5;
    position:relative;
}
.ch-title::before,.ch-title::after{content:'——';color:#10b981;margin:0 8px;font-weight:300}

/* ── META ── */
.ch-meta{padding:10px 14px;border-bottom:1px solid #f1f5f9}
.ch-meta-row{display:flex;gap:6px;margin-bottom:4px;font-size:9.5px;align-items:flex-start}
.ch-meta-row:last-child{margin-bottom:0}
.ch-meta-label{font-weight:700;color:#64748b;min-width:72px;font-size:8.5px;text-transform:uppercase;letter-spacing:.3px;padding-top:1px}
.ch-meta-val{color:#0f172a;font-weight:600;flex:1}
.ch-meta-val.big{font-size:12px;font-weight:900;text-transform:uppercase}
.ch-meta-val.mono{font-family:monospace;font-weight:700;color:#0f172a;background:#f1f5f9;padding:2px 6px;border-radius:3px}

/* ── TABLE ── */
.ch-table{width:100%;border-collapse:collapse}
.ch-table thead tr{background:linear-gradient(to right,#065f46,#047857)}
.ch-table th{
    padding:7px 10px;font-size:8px;font-weight:700;
    text-transform:uppercase;letter-spacing:.8px;color:#a7f3d0;text-align:left;
}
.ch-table th.c{text-align:center}
.ch-table th.r{text-align:right}
.ch-table tbody tr:nth-child(even){background:#f0fdf4}
.ch-table td{
    padding:7px 10px;border-bottom:1px solid #f1f5f9;
    font-size:10px;vertical-align:middle;
}
.ch-table td.c{text-align:center}
.ch-table td.r{text-align:right;font-weight:700}
.ch-table .empty-row td{height:17px;border-bottom:1px solid #f8f9fc}
.ch-sr{
    width:18px;height:18px;border-radius:50%;
    background:linear-gradient(135deg,#10b981,#059669);
    color:#fff;font-size:8px;font-weight:700;
    display:inline-flex;align-items:center;justify-content:center;
}

/* ── FOOTER ROWS ── */
.ch-delivery-row{
    display:grid;grid-template-columns:1fr 1fr;
    border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;
}
.ch-delivery-cell{padding:8px 12px}
.ch-delivery-cell:first-child{border-right:1px solid #e2e8f0}
.ch-dlabel{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:3px}
.ch-dval{font-size:10px;font-weight:600;color:#0f172a}
.vehicle-chip{
    display:inline-block;background:linear-gradient(135deg,#eef2ff,#f5f3ff);
    border:1px solid #c7d2fe;border-radius:4px;
    padding:3px 10px;font-size:11px;font-weight:800;color:#4f46e5;
    font-family:monospace;letter-spacing:.5px;
}

/* ── TOTAL ROW ── */
.ch-total{
    display:flex;justify-content:space-between;align-items:center;
    padding:7px 14px;background:linear-gradient(to right,#f0fdf4,#fff);
    border-bottom:1px solid #d1fae5;
}
.ch-total-label{font-size:9px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:.5px}
.ch-total-val{font-size:14px;font-weight:900;color:#065f46}

/* ── EWAY ── */
.ch-eway{
    padding:6px 12px;background:#f8fafc;border-bottom:1px solid #e2e8f0;
    display:flex;align-items:center;gap:8px;
}
.ch-eway-label{font-size:8px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
.ch-eway-val{
    font-size:10px;font-weight:700;color:#0f172a;font-family:monospace;
    background:#fff;padding:2px 8px;border-radius:3px;border:1px solid #e2e8f0;
}

/* ── SIGN ROW ── */
.ch-sign-row{display:grid;grid-template-columns:1fr 1fr}
.ch-sign-cell{padding:8px 12px}
.ch-sign-cell:first-child{border-right:1px solid #e2e8f0}
.ch-sign-label{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:3px}
.ch-sign-line{
    border-top:1.5px solid #e2e8f0;margin-top:26px;
    padding-top:4px;font-size:8.5px;color:#94a3b8;text-align:center;
}
.ch-for-company{font-size:11px;font-weight:900;color:#0f172a;text-transform:uppercase;letter-spacing:.3px}
.ch-for-city{font-size:8px;color:#64748b;margin-top:1px}

@media print{
    .no-print{display:none!important}
    body{background:#fff}
    .page-wrap{margin:0}
    .challan-grid{padding:0;gap:0}
    .ch-copy{box-shadow:none;border-radius:0;border:1px solid #ccc}
    .ch-header{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .ch-accent{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .ch-title{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .ch-table thead tr{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .ch-total{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    @page{size:A4 landscape;margin:8mm}
}
</style>
</head>
<body>

<div class="no-print">
    <div class="toolbar-brand">🚚 <span>Shree Giriraj Poly Plast</span> — Delivery Challan</div>
    <div class="toolbar-actions">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn-back">← Back</a>
        <a href="{{ route('invoices.print', $invoice) }}" class="btn-invoice" target="_blank">📄 Tax Invoice</a>
        <button onclick="window.print()" class="btn-print">🖨 Print Challan</button>
    </div>
</div>

@php
$challanNo  = $invoice->challan_number ?: $invoice->invoice_number;
$poNumber   = $invoice->po_number ?? '';
$poDate     = $invoice->po_date ?? '';
$vehicleNo  = $invoice->transporter?->vehicle_no ?? '';
$ewayBill   = $invoice->eway_bill_no ?? '';
$deliveryAt = $invoice->delivery_at ?? '';
$emptyRows  = max(0, 12 - count($invoice->items));
$totalNos   = $invoice->items->sum('quantity');
@endphp

<div class="page-wrap">
<div class="challan-grid">

@foreach(['ORIGINAL COPY','CUSTOMER COPY'] as $copyLabel)
<div class="ch-copy">

    <div class="ch-header">
        <div>
            <div class="ch-company">Shree Giriraj <span>Poly Plast</span></div>
            <div class="ch-tagline">Mfr. of : PP Raffia · HDPE Bags · Leno Bags · Woven Sacks</div>
            <div class="ch-addr">Plot No. 1203, G.I.D.C., Vatva, Ahmedabad – 382 445 (Gujarat)</div>
            <div class="ch-gstin">GSTIN : 24AHUPP7924M1ZG</div>
        </div>
        <div><div class="copy-badge">{{ $copyLabel }}</div></div>
    </div>

    <div class="ch-accent"></div>
    <div class="ch-title">Delivery Challan</div>

    <div class="ch-meta">
        <div class="ch-meta-row">
            <span class="ch-meta-label">Challan No.</span>
            <span class="ch-meta-val mono">{{ $challanNo }}</span>
            <span class="ch-meta-label" style="margin-left:12px">Date</span>
            <span class="ch-meta-val mono">{{ $invoice->invoice_date->format('d.m.Y') }}</span>
        </div>
        <div class="ch-meta-row">
            <span class="ch-meta-label">M/S.</span>
            <span class="ch-meta-val big">{{ $invoice->customer->name }}</span>
        </div>
        @if($invoice->customer->address)
        <div class="ch-meta-row">
            <span class="ch-meta-label"></span>
            <span class="ch-meta-val" style="color:#475569;font-size:9px">{{ $invoice->customer->address }}</span>
        </div>
        @endif
        @if($poNumber)
        <div class="ch-meta-row">
            <span class="ch-meta-label">P.O. No.</span>
            <span class="ch-meta-val mono">{{ $poNumber }}</span>
            @if($poDate)
            <span class="ch-meta-label" style="margin-left:12px">P.O. Date</span>
            <span class="ch-meta-val mono">{{ $poDate }}</span>
            @endif
        </div>
        @endif
    </div>

    <table class="ch-table">
        <thead>
            <tr>
                <th class="c" style="width:28px">No.</th>
                <th>Description of Goods</th>
                <th class="c" style="width:52px">Bundle Qty</th>
                <th class="c" style="width:48px">NOS.</th>
                <th class="r" style="width:64px">Total NOS.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td class="c"><span class="ch-sr">{{ $i+1 }}</span></td>
                <td style="font-weight:700;font-size:11px">{{ strtoupper($item->product->name) }}</td>
                <td class="c" style="color:#94a3b8">—</td>
                <td class="c" style="font-weight:700">{{ number_format($item->quantity,0) }}</td>
                <td class="r" style="font-size:11px">{{ number_format($item->quantity,0) }}</td>
            </tr>
            @endforeach
            @for($r=0;$r<$emptyRows;$r++)
            <tr class="empty-row"><td></td><td></td><td></td><td></td><td></td></tr>
            @endfor
        </tbody>
    </table>

    <div class="ch-delivery-row">
        <div class="ch-delivery-cell">
            <div class="ch-dlabel">Delivery At</div>
            <div class="ch-dval">{{ $deliveryAt ?: '—' }}</div>
        </div>
        <div class="ch-delivery-cell">
            <div class="ch-dlabel">Vehicle No.</div>
            @if($vehicleNo)
            <div class="vehicle-chip">{{ $vehicleNo }}</div>
            @else
            <div class="ch-dval" style="color:#94a3b8">— Not specified —</div>
            @endif
        </div>
    </div>

    <div class="ch-total">
        <span class="ch-total-label">Total NOS.</span>
        <span class="ch-total-val">{{ number_format($totalNos,0) }}</span>
    </div>

    <div class="ch-eway">
        <span class="ch-eway-label">E-Way Bill No.</span>
        <span class="ch-eway-val">{{ $ewayBill ?: '— Not Generated —' }}</span>
    </div>

    <div class="ch-sign-row">
        <div class="ch-sign-cell">
            <div class="ch-sign-label">Receiver's Signature</div>
            <div class="ch-sign-line">Receiver</div>
        </div>
        <div class="ch-sign-cell" style="text-align:right">
            <div class="ch-sign-label" style="text-align:right">For</div>
            <div class="ch-for-company">Shree Giriraj Poly Plast</div>
            <div class="ch-for-city">Ahmedabad</div>
            <div class="ch-sign-line">Authorised Signatory</div>
        </div>
    </div>

</div>{{-- ch-copy --}}
@endforeach

</div>{{-- challan-grid --}}
</div>{{-- page-wrap --}}
<script>window.onload=function(){if(window.location.hash!=='#preview')window.print()}</script>
</body>
</html>
