<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoice->invoice_number }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',Arial,sans-serif;font-size:11px;color:#1a1a2e;background:#dde3ee}

/* ── TOOLBAR ── */
.no-print{
    position:sticky;top:0;z-index:100;
    padding:10px 24px;
    background:linear-gradient(135deg,#0f172a,#1e293b);
    display:flex;gap:10px;justify-content:space-between;align-items:center;
    box-shadow:0 2px 12px rgba(0,0,0,.3);
}
.no-print .toolbar-brand{
    font-size:13px;font-weight:700;color:#e2e8f0;
    display:flex;align-items:center;gap:8px;
}
.no-print .toolbar-brand span{color:#818cf8}
.no-print .toolbar-actions{display:flex;gap:8px}
.no-print a,.no-print button{
    padding:8px 16px;border-radius:8px;font-size:12px;font-family:'Inter',sans-serif;
    font-weight:600;cursor:pointer;text-decoration:none;
    display:inline-flex;align-items:center;gap:6px;border:none;transition:all .2s;
}
.btn-back   {background:rgba(255,255,255,.1);color:#e2e8f0;border:1px solid rgba(255,255,255,.15)!important}
.btn-back:hover{background:rgba(255,255,255,.2)}
.btn-challan{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 4px 10px rgba(16,185,129,.35)}
.btn-print  {background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 10px rgba(99,102,241,.35)}

/* ── PAGE ── */
.page-wrap{max-width:870px;margin:24px auto 40px;background:#fff;border-radius:4px;box-shadow:0 8px 40px rgba(0,0,0,.18);overflow:hidden}

/* ── HEADER BAND ── */
.inv-header{
    background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#0f172a 100%);
    padding:20px 28px 16px;
    display:grid;grid-template-columns:1fr auto;gap:12px;align-items:flex-start;
    position:relative;overflow:hidden;
}
.inv-header::before{
    content:'';position:absolute;top:-40px;right:-40px;
    width:200px;height:200px;border-radius:50%;
    background:radial-gradient(circle,rgba(99,102,241,.25),transparent 70%);
}
.inv-header::after{
    content:'';position:absolute;bottom:-30px;left:30%;
    width:150px;height:150px;border-radius:50%;
    background:radial-gradient(circle,rgba(16,185,129,.15),transparent 70%);
}
.company-block{position:relative;z-index:1}
.company-name{
    font-size:22px;font-weight:900;color:#fff;
    text-transform:uppercase;letter-spacing:1.5px;line-height:1;
}
.company-name span{color:#818cf8}
.company-tagline{font-size:9px;color:#94a3b8;margin-top:4px;letter-spacing:.5px}
.company-info{margin-top:8px;font-size:9px;color:#64748b;line-height:1.8}
.company-gstin{
    display:inline-block;margin-top:6px;
    font-size:9.5px;font-weight:700;color:#94a3b8;
    background:rgba(255,255,255,.06);
    padding:3px 10px;border-radius:4px;border:1px solid rgba(255,255,255,.1);
    letter-spacing:.5px;
}

.inv-header-right{position:relative;z-index:1;text-align:right}
.copy-badge{
    display:inline-block;padding:4px 12px;
    background:rgba(99,102,241,.2);
    border:1px solid rgba(99,102,241,.4);
    border-radius:4px;font-size:9.5px;font-weight:700;
    color:#a5b4fc;letter-spacing:1px;margin-bottom:10px;
}
.inv-number-block{
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
    border-radius:8px;padding:10px 16px;text-align:right;
}
.inv-number-label{font-size:8px;color:#64748b;text-transform:uppercase;letter-spacing:1px}
.inv-number-value{font-size:18px;font-weight:900;color:#818cf8;letter-spacing:.5px;margin-top:2px}
.inv-date-value{font-size:11px;color:#94a3b8;margin-top:3px}

/* ── ACCENT BAR ── */
.accent-bar{height:3px;background:linear-gradient(to right,#6366f1,#8b5cf6,#10b981,#0ea5e9)}

/* ── TITLE STRIP ── */
.inv-title-strip{
    background:linear-gradient(to right,#f8faff,#f1f5f9);
    text-align:center;padding:7px;
    font-size:13px;font-weight:800;
    letter-spacing:3px;text-transform:uppercase;color:#1e293b;
    border-bottom:1px solid #e2e8f0;
    position:relative;
}
.inv-title-strip::before,.inv-title-strip::after{
    content:'——';color:#6366f1;margin:0 10px;font-weight:300;
}

/* ── META SECTION ── */
.inv-meta{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid #e2e8f0}
.inv-meta-left{padding:14px 20px;border-right:1px solid #e2e8f0}
.inv-meta-right{padding:14px 20px}

.buyer-tag{
    display:inline-flex;align-items:center;gap:5px;
    font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:1px;
    color:#6366f1;background:#eef2ff;padding:2px 8px;border-radius:4px;
    margin-bottom:6px;
}
.buyer-ms{font-size:9px;color:#64748b;font-weight:600}
.buyer-name{font-size:15px;font-weight:900;color:#0f172a;margin-top:2px;text-transform:uppercase}
.buyer-addr{font-size:10px;color:#475569;margin-top:4px;line-height:1.7}
.buyer-meta-row{
    display:flex;gap:6px;align-items:center;margin-top:5px;
    font-size:10px;
}
.buyer-meta-label{
    font-size:8.5px;font-weight:700;color:#94a3b8;
    text-transform:uppercase;letter-spacing:.5px;min-width:50px;
}
.buyer-meta-val{color:#1e293b;font-weight:600}

.inv-detail-row{
    display:flex;justify-content:space-between;align-items:center;
    padding:5px 0;border-bottom:1px dashed #f1f5f9;font-size:10px;
}
.inv-detail-row:last-child{border-bottom:none}
.inv-detail-label{color:#94a3b8;font-weight:600;font-size:8.5px;text-transform:uppercase;letter-spacing:.5px}
.inv-detail-val{color:#0f172a;font-weight:700;font-size:11px}
.inv-detail-val.highlight{color:#6366f1;font-size:13px;font-weight:900}

/* ── ITEMS TABLE ── */
.items-wrap{padding:0}
.items-table{width:100%;border-collapse:collapse}
.items-table thead tr{background:linear-gradient(to right,#0f172a,#1e293b)}
.items-table th{
    padding:9px 12px;font-size:9px;font-weight:700;
    text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;
    text-align:left;white-space:nowrap;
}
.items-table th.c{text-align:center}
.items-table th.r{text-align:right}
.items-table tbody tr{transition:background .1s}
.items-table tbody tr:nth-child(even){background:#fafbff}
.items-table tbody tr:hover{background:#eef2ff}
.items-table td{
    padding:9px 12px;border-bottom:1px solid #f1f5f9;
    font-size:11px;vertical-align:middle;
}
.items-table td.c{text-align:center}
.items-table td.r{text-align:right}
.items-table .empty-row td{border-bottom:1px solid #f8f9fc;height:20px}
.items-table .pf-row td{
    background:#f8fafc;font-size:9.5px;color:#94a3b8;
    font-style:italic;border-bottom:1px solid #e2e8f0;
}
.sr-badge{
    width:22px;height:22px;border-radius:50%;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    color:#fff;font-size:9px;font-weight:700;
    display:inline-flex;align-items:center;justify-content:center;
}
.hsn-chip{
    background:#eef2ff;color:#4f46e5;
    padding:2px 7px;border-radius:4px;
    font-size:9.5px;font-weight:700;font-family:monospace;
}

/* ── BOTTOM SECTION ── */
.inv-bottom{display:grid;grid-template-columns:1fr 210px;border-top:2px solid #e2e8f0}
.inv-bottom-left{padding:14px 20px;border-right:1px solid #e2e8f0}
.inv-bottom-right{padding:10px 16px;background:#fafbff}

.section-tag{
    font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:1px;
    color:#94a3b8;margin-bottom:5px;display:flex;align-items:center;gap:5px;
}
.section-tag::after{content:'';flex:1;height:1px;background:#f1f5f9}

.bank-box{
    background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;
    padding:8px 12px;margin-bottom:10px;font-size:10px;line-height:1.8;
}
.bank-box strong{color:#0f172a}

.amount-words-box{
    background:linear-gradient(135deg,#eef2ff,#f5f3ff);
    border:1px solid #c7d2fe;border-radius:6px;
    padding:8px 12px;margin-top:8px;
}
.amount-words-label{font-size:8px;font-weight:700;color:#6366f1;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px}
.amount-words-text{font-size:10.5px;font-weight:700;color:#1e1b4b;line-height:1.5}

/* Tax table */
.tax-row{
    display:flex;justify-content:space-between;align-items:center;
    padding:5px 0;font-size:10.5px;border-bottom:1px dashed #f1f5f9;
}
.tax-row:last-child{border-bottom:none}
.tax-row .lbl{color:#475569;font-weight:500}
.tax-row .val{color:#0f172a;font-weight:700;font-variant-numeric:tabular-nums}
.tax-row.net .lbl{color:#0f172a;font-weight:700}
.tax-row.grand-total{
    background:linear-gradient(135deg,#0f172a,#1e293b);
    border-radius:6px;padding:10px 12px;margin-top:8px;
}
.tax-row.grand-total .lbl{color:#94a3b8;font-weight:700;font-size:11px}
.tax-row.grand-total .val{color:#818cf8;font-size:15px;font-weight:900}

/* ── TERMS ── */
.inv-terms{
    background:#f8fafc;border-top:1px solid #e2e8f0;
    padding:10px 20px;font-size:9.5px;color:#64748b;
    display:flex;gap:20px;flex-wrap:wrap;
}
.term-item{display:flex;gap:5px;align-items:flex-start}
.term-num{
    width:16px;height:16px;border-radius:50%;
    background:#6366f1;color:#fff;font-size:8px;font-weight:700;
    display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;
}

/* ── FOOTER ── */
.inv-footer{
    display:grid;grid-template-columns:1fr 1fr 1fr;
    border-top:2px solid #e2e8f0;
}
.footer-cell{padding:12px 20px}
.footer-cell:not(:last-child){border-right:1px solid #e2e8f0}
.footer-label{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;margin-bottom:5px}
.sign-line{
    border-top:1.5px solid #e2e8f0;margin-top:30px;
    padding-top:6px;font-size:9px;color:#94a3b8;text-align:center;
}
.eway-val{
    font-size:11px;font-weight:700;color:#0f172a;
    background:#f1f5f9;padding:4px 10px;border-radius:4px;
    border:1px solid #e2e8f0;display:inline-block;margin-top:4px;
    font-family:monospace;
}
.company-sign{font-size:13px;font-weight:900;color:#0f172a;letter-spacing:.3px}
.company-sign-sub{font-size:9px;color:#64748b}

@media print{
    .no-print{display:none!important}
    body{background:#fff}
    .page-wrap{margin:0;box-shadow:none;border-radius:0}
    .items-table thead tr{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .inv-header{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .tax-row.grand-total{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .accent-bar{-webkit-print-color-adjust:exact;print-color-adjust:exact}
}
</style>
</head>
<body>

<div class="no-print">
    <div class="toolbar-brand">
        🏭 <span>Shree Giriraj Poly Plast</span> — Invoice Preview
    </div>
    <div class="toolbar-actions">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn-back">← Back</a>
        <a href="{{ route('invoices.challan', $invoice) }}" class="btn-challan" target="_blank">🚚 Delivery Challan</a>
        <button onclick="window.print()" class="btn-print">🖨 Print Invoice</button>
    </div>
</div>

<div class="page-wrap">

{{-- ── HEADER ── --}}
<div class="inv-header">
    <div class="company-block">
        <div class="company-name">Shree Giriraj<span> Poly Plast</span></div>
        <div class="company-tagline">Mfr. of : PP Raffia · HDPE Bags · Leno Bags · Woven Sacks</div>
        <div class="company-info">
            Plot No. 1203, G.I.D.C., Vatva, Ahmedabad – 382 445 (Gujarat)<br>
            Mobile : 9876543210 &nbsp;·&nbsp; Email : shreegirirajpolyplast@gmail.com
        </div>
        <div class="company-gstin">GSTIN : 24AHUPP7924M1ZG</div>
    </div>
    <div class="inv-header-right">
        <div class="copy-badge">ORIGINAL / DUPLICATE</div>
        <div class="inv-number-block">
            <div class="inv-number-label">Proforma Invoice No.</div>
            <div class="inv-number-value">{{ $invoice->invoice_number }}</div>
            <div class="inv-date-value">{{ $invoice->invoice_date->format('d M Y') }}</div>
        </div>
    </div>
</div>

<div class="accent-bar"></div>
<div class="inv-title-strip">Proforma Invoice</div>

{{-- ── BUYER + META ── --}}
<div class="inv-meta">
    <div class="inv-meta-left">
        <div class="buyer-tag">📋 Buyer Details</div>
        <div class="buyer-ms">M/S.</div>
        <div class="buyer-name">{{ $invoice->customer->name }}</div>
        @if($invoice->customer->address)
        <div class="buyer-addr">{!! nl2br(e($invoice->customer->address)) !!}</div>
        @endif
        @if($invoice->customer->state)
        <div class="buyer-addr">{{ $invoice->customer->state }}</div>
        @endif
        @if($invoice->customer->gstin)
        <div class="buyer-meta-row">
            <span class="buyer-meta-label">GSTIN</span>
            <span class="buyer-meta-val" style="font-family:monospace">{{ $invoice->customer->gstin }}</span>
        </div>
        @endif
        @if($invoice->customer->phone)
        <div class="buyer-meta-row">
            <span class="buyer-meta-label">Phone</span>
            <span class="buyer-meta-val">{{ $invoice->customer->phone }}</span>
        </div>
        @endif
    </div>
    <div class="inv-meta-right">
        <div class="inv-detail-row">
            <span class="inv-detail-label">Invoice No.</span>
            <span class="inv-detail-val highlight">{{ $invoice->invoice_number }}</span>
        </div>
        <div class="inv-detail-row">
            <span class="inv-detail-label">Date</span>
            <span class="inv-detail-val">{{ $invoice->invoice_date->format('d.m.Y') }}</span>
        </div>
        @if($invoice->payment_terms)
        <div class="inv-detail-row">
            <span class="inv-detail-label">Payment Terms</span>
            <span class="inv-detail-val">{{ strtoupper($invoice->payment_terms) }}</span>
        </div>
        @endif
        @if($invoice->po_number)
        <div class="inv-detail-row">
            <span class="inv-detail-label">P.O. Number</span>
            <span class="inv-detail-val">{{ $invoice->po_number }}</span>
        </div>
        @endif
        @if($invoice->po_date)
        <div class="inv-detail-row">
            <span class="inv-detail-label">P.O. Date</span>
            <span class="inv-detail-val">{{ $invoice->po_date }}</span>
        </div>
        @endif
        @if($invoice->transporter)
        <div class="inv-detail-row">
            <span class="inv-detail-label">Transporter</span>
            <span class="inv-detail-val">{{ $invoice->transporter->name }}</span>
        </div>
        @endif
        @if($invoice->lr_number)
        <div class="inv-detail-row">
            <span class="inv-detail-label">LR No.</span>
            <span class="inv-detail-val">{{ $invoice->lr_number }}</span>
        </div>
        @endif
    </div>
</div>

{{-- ── ITEMS ── --}}
@php $emptyRows = max(0, 12 - count($invoice->items)); @endphp
<table class="items-table">
    <thead>
        <tr>
            <th class="c" style="width:36px">No.</th>
            <th>Description of Goods</th>
            <th class="c" style="width:64px">HSN/SAC</th>
            <th class="c" style="width:64px">Qty<br><span style="font-weight:400;font-size:8px;opacity:.7">NOS.</span></th>
            <th class="r" style="width:80px">Rate</th>
            <th class="c" style="width:36px">Per</th>
            <th class="r" style="width:96px">Amount (₹)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $i => $item)
        <tr>
            <td class="c"><span class="sr-badge">{{ $i+1 }}</span></td>
            <td style="font-weight:700;font-size:12px">{{ strtoupper($item->product->name) }}</td>
            <td class="c"><span class="hsn-chip">{{ $item->product->hsn_code ?? '—' }}</span></td>
            <td class="c" style="font-weight:700">{{ number_format($item->quantity, 0) }}</td>
            <td class="r">{{ number_format($item->unit_price, 2) }}</td>
            <td class="c" style="color:#94a3b8">1</td>
            <td class="r" style="font-weight:800;font-size:12px">{{ number_format($item->total_price, 2) }}</td>
        </tr>
        @endforeach
        @for($r=0;$r<$emptyRows;$r++)
        <tr class="empty-row"><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        @endfor
        <tr class="pf-row">
            <td colspan="5" class="r">P &amp; F Charges</td><td></td><td class="r">—</td>
        </tr>
    </tbody>
</table>

{{-- ── BOTTOM ── --}}
<div class="inv-bottom">
    <div class="inv-bottom-left">
        @if($invoice->delivery_at)
        <div class="section-tag">Delivery At</div>
        <div style="font-size:11px;color:#1e293b;font-weight:600;margin-bottom:10px">{{ $invoice->delivery_at }}</div>
        @else
        <div class="section-tag">Delivery At</div>
        <div style="font-size:11px;color:#94a3b8;margin-bottom:10px">— As per order —</div>
        @endif

        <div class="section-tag">Bank Details</div>
        <div class="bank-box">
            <strong>Axis Bank</strong> — Vastral Branch<br>
            A/C No. : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            NEFT / RTGS : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </div>

        <div class="section-tag">Amount in Words</div>
        <div class="amount-words-box">
            <div class="amount-words-label">Rupees (Total)</div>
            <div class="amount-words-text">
                Rs. @php
                function n2w($n){
                    $o=['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                        'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
                    $t=['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
                    if($n==0)return'Zero';$r='';
                    if($n>=10000000){$r.=n2w((int)($n/10000000)).' Crore ';$n%=10000000;}
                    if($n>=100000){$r.=n2w((int)($n/100000)).' Lakh ';$n%=100000;}
                    if($n>=1000){$r.=n2w((int)($n/1000)).' Thousand ';$n%=1000;}
                    if($n>=100){$r.=$o[(int)($n/100)].' Hundred ';$n%=100;}
                    if($n>=20){$r.=$t[(int)($n/10)];if($n%10)$r.='-'.$o[$n%10];$r.=' ';}
                    elseif($n>0){$r.=$o[$n].' ';}
                    return trim($r);
                }
                $w=(int)$invoice->grand_total;$p=round(($invoice->grand_total-$w)*100);
                $s=n2w($w);if($p>0)$s.=' and '.n2w($p).' Paise';$s.=' Only.';echo $s;
                @endphp
            </div>
        </div>
    </div>

    <div class="inv-bottom-right">
        @php
            $cgstRate = ($invoice->subtotal>0 && $invoice->cgst>0) ? round(($invoice->cgst/$invoice->subtotal)*100,2) : 0;
            $igstRate = ($invoice->subtotal>0 && $invoice->igst>0) ? round(($invoice->igst/$invoice->subtotal)*100,2) : 0;
        @endphp
        <div class="tax-row net">
            <span class="lbl">Net Amount</span>
            <span class="val">{{ number_format($invoice->subtotal,2) }}</span>
        </div>
        @if($invoice->cgst > 0)
        <div class="tax-row">
            <span class="lbl">SGST @ {{ $cgstRate }}%</span>
            <span class="val">{{ number_format($invoice->sgst,2) }}</span>
        </div>
        <div class="tax-row">
            <span class="lbl">CGST @ {{ $cgstRate }}%</span>
            <span class="val">{{ number_format($invoice->cgst,2) }}</span>
        </div>
        <div class="tax-row"><span class="lbl">IGST @ 0%</span><span class="val">0.00</span></div>
        @endif
        @if($invoice->igst > 0)
        <div class="tax-row"><span class="lbl">CGST @ 0%</span><span class="val">0.00</span></div>
        <div class="tax-row"><span class="lbl">SGST @ 0%</span><span class="val">0.00</span></div>
        <div class="tax-row">
            <span class="lbl">IGST @ {{ $igstRate }}%</span>
            <span class="val">{{ number_format($invoice->igst,2) }}</span>
        </div>
        @endif
        <div class="tax-row"><span class="lbl">Round Off (+/-)</span><span class="val">0.00</span></div>
        <div class="tax-row grand-total">
            <span class="lbl">Grand Total</span>
            <span class="val">₹{{ number_format($invoice->grand_total,2) }}</span>
        </div>
    </div>
</div>

{{-- ── TERMS ── --}}
<div class="inv-terms">
    <div class="term-item"><span class="term-num">1</span><span>Payments by Cheque or Bank Transfer requested.</span></div>
    <div class="term-item"><span class="term-num">2</span><span>Proforma Invoice validity: 15 Days.</span></div>
    <div class="term-item"><span class="term-num">3</span><span>Jurisdiction: Ahmedabad only.</span></div>
</div>

{{-- ── FOOTER ── --}}
<div class="inv-footer">
    <div class="footer-cell">
        <div class="footer-label">Receiver's Signature</div>
        <div class="sign-line">Receiver</div>
    </div>
    <div class="footer-cell" style="text-align:center">
        <div class="footer-label">E-Way Bill No.</div>
        <div class="eway-val">{{ $invoice->eway_bill_no ?: '— Not Applicable —' }}</div>
    </div>
    <div class="footer-cell" style="text-align:right">
        <div class="footer-label">For</div>
        <div class="company-sign">SHREE GIRIRAJ POLY PLAST</div>
        <div class="company-sign-sub">Ahmedabad</div>
        <div class="sign-line">Authorised Signatory</div>
    </div>
</div>

</div>{{-- page-wrap --}}
<script>window.onload=function(){if(window.location.hash!=='#preview')window.print()}</script>
</body>
</html>
