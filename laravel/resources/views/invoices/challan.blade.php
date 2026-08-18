<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DELIVERY CHALLAN - {{ $invoice->challan_number ?? $invoice->invoice_number }} - Shree Giriraj Poly Plast</title>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@500;600;700;800;900&family=Playball&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #000;
    background: #e2e8f0;
    line-height: 1.3;
}

.no-print {
    position: sticky; top: 0; z-index: 1000;
    padding: 10px 24px;
    background: #0f172a;
    display: flex; justify-content: space-between; align-items: center;
    box-shadow: 0 4px 14px rgba(0,0,0,0.25);
    font-family: 'Montserrat', sans-serif;
}
.no-print .toolbar-brand { font-size: 13px; font-weight: 700; color: #fff; }
.no-print .toolbar-brand span { color: #10b981; }
.no-print .toolbar-actions { display: flex; gap: 10px; }
.no-print a, .no-print button {
    padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600;
    cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    border: none; font-family: 'Montserrat', sans-serif; transition: all .2s;
}
.btn-back { background: #334155; color: #e2e8f0; }
.btn-invoice { background: #e11d48; color: #fff; }
.btn-print { background: #0d9488; color: #fff; }

.page-wrap {
    width: 210mm;
    min-height: 297mm;
    margin: 15px auto 30px;
    background: #ffffff;
    padding: 0;
    box-shadow: 0 10px 30px rgba(0,0,0,0.18);
    position: relative;
    display: flex;
    flex-direction: column;
}
.invoice-content {
    padding: 10px 18px 12px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.top-color-strip {
    height: 12px;
    background: #00897b;
    width: 100%;
}
.sub-tag-top {
    text-align: center;
    font-size: 9.5px;
    color: #4b5563;
    margin-top: 4px;
    margin-bottom: 2px;
}
.header-grid {
    display: grid;
    grid-template-columns: 1.4fr 70px 1.4fr;
    align-items: center;
    margin-bottom: 2px;
}
.header-left-text {
    font-size: 9px;
    font-weight: bold;
    color: #dc2626;
    line-height: 1.35;
}
.header-center-logo { text-align: center; }
.logo-box-green {
    width: 44px;
    height: 44px;
    background: #689f38;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.logo-box-green span {
    font-family: 'Great Vibes', cursive;
    font-size: 30px;
    color: #fef08a;
    line-height: 1;
}
.header-right-brand { text-align: right; }
.brand-shree-txt {
    font-size: 11px;
    font-weight: bold;
    color: #dc2626;
    display: block;
    margin-bottom: -4px;
}
.brand-giriraj-txt {
    font-family: 'Playball', 'Great Vibes', cursive;
    font-size: 36px;
    font-weight: bold;
    color: #dc2626;
    line-height: 1;
}
.brand-giriraj-txt span { color: #1e3a8a; }
.brand-polyplast-txt {
    font-family: Arial, sans-serif;
    font-size: 11px;
    font-weight: 900;
    color: #00796b;
    letter-spacing: 2px;
    text-transform: uppercase;
    display: block;
    margin-top: -2px;
}
.header-divider-red { height: 1.5px; background: #dc2626; margin: 4px 0 2px; }
.header-address-txt {
    text-align: center;
    font-size: 8px;
    color: #000;
    line-height: 1.3;
    font-weight: 500;
    margin-bottom: 4px;
}
.tax-strip-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #000;
    border-bottom: 1px solid #000;
    padding: 3px 0;
    font-size: 10px;
    font-weight: bold;
}
.tax-strip-title { font-size: 12px; font-weight: 900; letter-spacing: 0.5px; }
.tax-strip-copies { font-size: 7.5px; color: #4b5563; font-weight: normal; }

.buyer-meta-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    border-bottom: 1px solid #000;
    padding: 4px 0;
    font-size: 9px;
    line-height: 1.35;
}
.buyer-col { padding-right: 12px; }
.buyer-label { font-size: 8.5px; color: #374151; margin-bottom: 2px; }
.buyer-name { font-weight: bold; font-size: 10px; text-transform: uppercase; }
.buyer-gstin { font-weight: bold; margin-top: 2px; }
.meta-col { padding-left: 6px; }
.meta-line { display: flex; justify-content: space-between; margin-bottom: 1.5px; }
.meta-k { font-weight: bold; }
.meta-v { font-weight: normal; }

.table-area { position: relative; margin-top: 0; flex: 1; }
.watermark-text {
    position: absolute; top: 45%; left: 50%;
    transform: translate(-50%, -50%) rotate(-14deg);
    font-family: 'Great Vibes', 'Playball', cursive;
    font-size: 130px;
    color: rgba(220, 38, 38, 0.08);
    font-weight: bold;
    pointer-events: none;
    z-index: 0;
    user-select: none;
    white-space: nowrap;
}
.bill-table { width: 100%; border-collapse: collapse; position: relative; z-index: 1; font-size: 9px; }
.bill-table th {
    border-bottom: 1px solid #000;
    padding: 4px 2px;
    font-size: 8.5px;
    font-weight: bold;
    text-align: center;
    text-transform: uppercase;
}
.bill-table td { padding: 3px 2px; vertical-align: top; height: 18px; }
.bill-table .al-left { text-align: left; }
.bill-table .al-center { text-align: center; }
.bill-table .al-right { text-align: right; }
.bill-table .item-name { font-weight: bold; text-transform: uppercase; }

.delivery-box {
    border: 1px solid #000;
    padding: 2px 6px;
    display: inline-block;
    min-width: 130px;
    font-weight: bold;
    font-size: 8.5px;
    margin-bottom: 6px;
}
.vehicle-line { font-weight: bold; font-size: 9.5px; margin-bottom: 10px; }

.full-divider { height: 1px; background: #000; margin: 8px 0 4px; }
.footer-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    font-size: 8.5px;
    margin-top: 14px;
}
.sign-box-challan {
    border-top: 1px dashed #9ca3af;
    padding-top: 4px;
    text-align: center;
    margin-top: 30px;
    font-size: 8.5px;
}
.sign-block-right {
    text-align: right;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.company-name-bold { font-size: 10px; font-weight: bold; }
.auth-sign-txt { margin-top: 32px; font-size: 8.5px; }

.bottom-bars { margin-top: auto; width: 100%; }
.bottom-bar-red { height: 3px; background: #dc2626; }
.bottom-bar-teal { height: 14px; background: #00897b; }

@media print {
    .no-print { display: none !important; }
    body { background: #fff; }
    .page-wrap { width: 100%; margin: 0; box-shadow: none; min-height: 100vh; }
    .top-color-strip, .bottom-bar-red, .bottom-bar-teal, .logo-box-green { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .watermark-text { color: rgba(220, 38, 38, 0.08) !important; }
}
</style>
</head>
<body>

<div class="no-print">
    <div class="toolbar-brand">
        🚚 <span>Shree Giriraj Poly Plast</span> — Delivery Challan
    </div>
    <div class="toolbar-actions">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn-back">← Back</a>
        <a href="{{ route('invoices.print', $invoice) }}" class="btn-invoice">📄 View Tax Invoice</a>
        <button onclick="window.print()" class="btn-print">🖨 Print Challan</button>
    </div>
</div>

<div class="page-wrap">
    <div class="top-color-strip"></div>
    <div class="invoice-content">
        <div class="sub-tag-top">"Giriraj"</div>
        <div class="header-grid">
            <div class="header-left-text">
                Mfg. &amp; Trading All Type of Plastics Products<br>&amp; Raw Material
            </div>
            <div class="header-center-logo">
                <div class="logo-box-green">
                    <span>Gp</span>
                </div>
            </div>
            <div class="header-right-brand">
                <span class="brand-shree-txt">Shree</span>
                <div class="brand-giriraj-txt">Giri<span>Raj</span></div>
                <span class="brand-polyplast-txt">POLY PLAST</span>
            </div>
        </div>

        <div class="header-divider-red"></div>
        <div class="header-address-txt">
            86, Yamuna Ind. Estate, Part - 2, Nr. Chirag Estate, Opp Parth Estate, Jamfal Wadi Canal Road, C.T.M, Ahmedabad-26<br>
            E-mail : shreegirirajp.plast@gmail.com &nbsp; M.: +91 - 94283 81122 / +91 - 6351297816.
        </div>

        <div class="tax-strip-row">
            <div>GSTIN:24AHUPP7924M1ZG</div>
            <div class="tax-strip-title">DELIVERY CHALLAN</div>
            <div class="tax-strip-copies">CONSIGNEE / TRANSPORTER COPY</div>
        </div>

        <div class="buyer-meta-grid">
            <div class="buyer-col">
                <div class="buyer-label">Consignee Name Address &amp; GSTIN:</div>
                <div class="buyer-name">M/S. &nbsp;{{ strtoupper($invoice->customer->name) }}</div>
                @if($invoice->customer->address)
                    <div>{!! nl2br(e($invoice->customer->address)) !!}</div>
                @endif
                @if($invoice->customer->state)
                    <div style="font-weight: 600;">{{ strtoupper($invoice->customer->state) }}.</div>
                @endif
                <div class="buyer-gstin">GSTIN:{{ $invoice->customer->gstin ?: '—' }}</div>
            </div>

            <div class="meta-col">
                <div class="meta-line">
                    <span class="meta-k">CHALLAN NO: &nbsp;<span class="meta-v">{{ $invoice->challan_number ?: ($invoice->lr_number ?: $invoice->invoice_number) }}</span></span>
                    <span class="meta-k">DATE: &nbsp;<span class="meta-v">{{ $invoice->invoice_date->format('d.m.Y') }}</span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">INVOICE NO: &nbsp;<span class="meta-v">{{ $invoice->invoice_number }}</span></span>
                    <span class="meta-k">DATE: &nbsp;<span class="meta-v">{{ $invoice->invoice_date->format('d.m.Y') }}</span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">P.O.NO &amp; DATE: &nbsp;<span class="meta-v">{{ $invoice->po_number }} @if($invoice->po_date) ({{ $invoice->po_date }}) @endif</span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">PAYMENT TERMS: &nbsp;<span class="meta-v">{{ $invoice->payment_terms ? strtoupper($invoice->payment_terms) : '' }}</span></span>
                </div>
            </div>
        </div>

        <div class="table-area">
            <div class="watermark-text">GiriRaj</div>
            <table class="bill-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">NO</th>
                        <th class="al-left" style="width: 50%;">Description Of Goods</th>
                        <th style="width: 80px;">HSN/SAC</th>
                        <th style="width: 90px;">QUANTITY<br><span style="font-weight: normal; text-transform: none;">Nos / Pcs</span></th>
                        <th style="width: 110px;">REMARKS</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $rowCount = count($invoice->items);
                        $fillCount = max(0, 8 - $rowCount);
                    @endphp
                    @foreach($invoice->items as $idx => $item)
                    <tr>
                        <td class="al-center">{{ $idx + 1 }}</td>
                        <td class="item-name al-left">{{ strtoupper($item->product->name) }}</td>
                        <td class="al-center">{{ $item->product->hsn_code ?? '392690' }}</td>
                        <td class="al-center" style="font-weight: bold;">{{ number_format($item->quantity, 0) }} Nos</td>
                        <td class="al-center">Good Condition</td>
                    </tr>
                    @endforeach

                    @for($f = 0; $f < $fillCount; $f++)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div style="margin-top: 10px;">
            <div class="delivery-box">
                DELIVERY AT: &nbsp;{{ $invoice->delivery_at ?: '' }}
            </div>
            <div class="vehicle-line">
                VEHICLE NO: {{ $invoice->transporter ? ($invoice->transporter->vehicle_no ?: $invoice->transporter->name) : ($invoice->lr_number ?: '') }}
            </div>
        </div>

        <div class="full-divider"></div>

        <div class="footer-cols">
            <div>
                <p>Received the above goods in good order and condition.</p>
                <div class="sign-box-challan">Receiver's Signature &amp; Stamp</div>
            </div>

            <div class="sign-block-right">
                <div>
                    <div>For,</div>
                    <div class="company-name-bold">Shree Giriraj Poly Plast.</div>
                </div>
                <div class="auth-sign-txt">Authorised Signatory</div>
            </div>
        </div>

    </div>

    <div class="bottom-bars">
        <div class="bottom-bar-red"></div>
        <div class="bottom-bar-teal"></div>
    </div>
</div>

<script>
window.onload = function() {
    if (window.location.hash !== '#preview') {
        window.print();
    }
};
</script>
</body>
</html>
