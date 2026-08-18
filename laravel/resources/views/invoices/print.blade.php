<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>TAX INVOICE - {{ $invoice->invoice_number }} - Shree Giriraj Poly Plast</title>
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

/* ── TOOLBAR (Screen Only) ── */
.no-print {
    position: sticky; top: 0; z-index: 1000;
    padding: 10px 24px;
    background: #0f172a;
    display: flex; justify-content: space-between; align-items: center;
    box-shadow: 0 4px 14px rgba(0,0,0,0.25);
    font-family: 'Montserrat', sans-serif;
}
.no-print .toolbar-brand {
    font-size: 13px; font-weight: 700; color: #fff;
}
.no-print .toolbar-brand span { color: #f43f5e; }
.no-print .toolbar-actions { display: flex; gap: 10px; }
.no-print a, .no-print button {
    padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600;
    cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    border: none; font-family: 'Montserrat', sans-serif; transition: all .2s;
}
.btn-back { background: #334155; color: #e2e8f0; }
.btn-back:hover { background: #475569; }
.btn-challan { background: #0d9488; color: #fff; }
.btn-print { background: #e11d48; color: #fff; box-shadow: 0 4px 12px rgba(225,29,72,0.35); }

/* ── EXACT PRINT PAGE ── */
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

/* ── TOP TEAL STRIP ── */
.top-color-strip {
    height: 12px;
    background: #00897b;
    width: 100%;
}

/* ── HEADER ── */
.sub-tag-top {
    text-align: center;
    font-size: 9.5px;
    color: #4b5563;
    margin-top: 4px;
    margin-bottom: 2px;
    font-family: Arial, sans-serif;
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
.header-center-logo {
    text-align: center;
}
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
.header-right-brand {
    text-align: right;
}
.brand-shree-txt {
    font-size: 11px;
    font-weight: bold;
    color: #dc2626;
    display: block;
    margin-bottom: -4px;
    font-family: Arial, sans-serif;
}
.brand-giriraj-txt {
    font-family: 'Playball', 'Great Vibes', cursive;
    font-size: 36px;
    font-weight: bold;
    color: #dc2626;
    line-height: 1;
}
.brand-giriraj-txt span {
    color: #1e3a8a;
}
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

.header-divider-red {
    height: 1.5px;
    background: #dc2626;
    margin: 4px 0 2px;
}
.header-address-txt {
    text-align: center;
    font-size: 8px;
    color: #000;
    line-height: 1.3;
    font-weight: 500;
    margin-bottom: 4px;
}

/* ── TAX INVOICE STRIP ── */
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
.tax-strip-title {
    font-size: 12px;
    font-weight: 900;
    letter-spacing: 0.5px;
}
.tax-strip-copies {
    font-size: 7.5px;
    color: #4b5563;
    font-weight: normal;
}

/* ── BUYER & INVOICE META ── */
.buyer-meta-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    border-bottom: 1px solid #000;
    padding: 4px 0;
    font-size: 9px;
    line-height: 1.35;
}
.buyer-col {
    padding-right: 12px;
}
.buyer-label {
    font-size: 8.5px;
    color: #374151;
    margin-bottom: 2px;
}
.buyer-name {
    font-weight: bold;
    font-size: 10px;
    text-transform: uppercase;
}
.buyer-gstin {
    font-weight: bold;
    margin-top: 2px;
}

.meta-col {
    padding-left: 6px;
}
.meta-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1.5px;
}
.meta-k {
    font-weight: bold;
}
.meta-v {
    font-weight: normal;
}

/* ── ITEMS TABLE ── */
.table-area {
    position: relative;
    margin-top: 0;
    flex: 1;
}
.watermark-text {
    position: absolute;
    top: 45%;
    left: 50%;
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
.bill-table {
    width: 100%;
    border-collapse: collapse;
    position: relative;
    z-index: 1;
    font-size: 9px;
}
.bill-table th {
    border-bottom: 1px solid #000;
    padding: 4px 2px;
    font-size: 8.5px;
    font-weight: bold;
    text-align: center;
    text-transform: uppercase;
}
.bill-table td {
    padding: 3px 2px;
    vertical-align: top;
    height: 18px;
}
.bill-table .al-left { text-align: left; }
.bill-table .al-center { text-align: center; }
.bill-table .al-right { text-align: right; }
.bill-table .item-name { font-weight: bold; text-transform: uppercase; }

/* ── TOTALS & SUMMARY SECTION ── */
.summary-container {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    margin-top: 6px;
    font-size: 9px;
}
.summary-left-box {
    padding-right: 12px;
}
.delivery-box {
    border: 1px solid #000;
    padding: 2px 6px;
    display: inline-block;
    min-width: 130px;
    font-weight: bold;
    font-size: 8.5px;
    margin-bottom: 6px;
}
.vehicle-line {
    font-weight: bold;
    font-size: 9.5px;
    margin-bottom: 10px;
}
.bank-section {
    font-size: 8.5px;
    line-height: 1.35;
    margin-top: 4px;
}
.bank-title {
    font-weight: bold;
}
.words-section {
    margin-top: 6px;
    font-size: 8.5px;
}
.words-title {
    font-weight: bold;
}
.words-val {
    font-weight: bold;
    line-height: 1.3;
}

/* Right calculation side */
.calc-side {
    font-size: 9px;
}
.calc-row {
    display: flex;
    justify-content: space-between;
    padding: 1.5px 0;
}
.calc-row.bordered-top {
    border-top: 1px solid #000;
    margin-top: 2px;
    padding-top: 2px;
}
.calc-row.total-bold {
    border-top: 1px solid #000;
    margin-top: 2px;
    padding-top: 3px;
    font-size: 10.5px;
    font-weight: bold;
}

/* ── FOOTER DECLARATION & SIGNATURE ── */
.full-divider {
    height: 1px;
    background: #000;
    margin: 8px 0 4px;
}
.reverse-charge-text {
    font-size: 8px;
    font-weight: bold;
    margin-bottom: 4px;
}
.footer-cols {
    display: grid;
    grid-template-columns: 1.25fr 1fr;
    gap: 12px;
    font-size: 8px;
    line-height: 1.3;
}
.decl-text p {
    margin-bottom: 1.5px;
    color: #222;
}
.jurisdiction-center {
    font-weight: bold;
    color: #000;
    margin-top: 4px;
    text-align: center;
    font-size: 8.5px;
}
.sign-block {
    text-align: right;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.for-title {
    font-size: 9px;
    font-weight: normal;
}
.company-name-bold {
    font-size: 10px;
    font-weight: bold;
}
.sign-label {
    margin-top: 32px;
    font-size: 8.5px;
    font-weight: normal;
}

/* ── BOTTOM DECORATIVE BARS ── */
.bottom-bars {
    margin-top: auto;
    width: 100%;
}
.bottom-bar-red {
    height: 3px;
    background: #dc2626;
}
.bottom-bar-teal {
    height: 14px;
    background: #00897b;
}

/* ── PRINT RULES ── */
@media print {
    .no-print { display: none !important; }
    body { background: #fff; }
    .page-wrap {
        width: 100%;
        margin: 0;
        box-shadow: none;
        min-height: 100vh;
    }
    .top-color-strip, .bottom-bar-red, .bottom-bar-teal, .logo-box-green {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .watermark-text {
        color: rgba(220, 38, 38, 0.08) !important;
    }
}
</style>
</head>
<body>

<!-- Action Bar (Hidden in Print) -->
<div class="no-print">
    <div class="toolbar-brand">
        🏭 <span>Shree Giriraj Poly Plast</span> — Tax Invoice #{{ $invoice->invoice_number }}
    </div>
    <div class="toolbar-actions">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn-back">← Back</a>
        <a href="{{ route('invoices.challan', $invoice) }}" class="btn-challan" target="_blank">🚚 Delivery Challan</a>
        <button onclick="window.print()" class="btn-print">🖨 Print Invoice</button>
    </div>
</div>

<div class="page-wrap">

    <!-- Top Solid Teal Band -->
    <div class="top-color-strip"></div>

    <div class="invoice-content">

        <!-- "Giriraj" Subtag -->
        <div class="sub-tag-top">"Giriraj"</div>

        <!-- Header Grid -->
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

        <!-- TAX INVOICE Bar -->
        <div class="tax-strip-row">
            <div>GSTIN:24AHUPP7924M1ZG</div>
            <div class="tax-strip-title">TAX-INVOICE</div>
            <div class="tax-strip-copies">ORIGINAL / DUPLICATE / TRIPLICATE</div>
        </div>

        <!-- Buyer & Meta Section -->
        <div class="buyer-meta-grid">
            <div class="buyer-col">
                <div class="buyer-label">Buyer's Name Address &amp; GSTIN:</div>
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
                    <span class="meta-k">INVOICE NO: &nbsp;<span class="meta-v">{{ $invoice->invoice_number }}</span></span>
                    <span class="meta-k">DATE: &nbsp;<span class="meta-v">{{ $invoice->invoice_date->format('d.m.Y') }}</span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">D CHALLAN NO: &nbsp;<span class="meta-v">{{ $invoice->challan_number ?: ($invoice->lr_number ?: '') }}</span></span>
                    <span class="meta-k">DATE: &nbsp;<span class="meta-v">{{ $invoice->invoice_date->format('d.m.Y') }}</span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">P.O.NO &amp; DATE: &nbsp;<span class="meta-v">{{ $invoice->po_number }} @if($invoice->po_date) ({{ $invoice->po_date }}) @endif</span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">INSURANCE : &nbsp;<span class="meta-v"></span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">PAYMENT TERMS: &nbsp;<span class="meta-v">{{ $invoice->payment_terms ? strtoupper($invoice->payment_terms) : '' }}</span></span>
                </div>
            </div>
        </div>

        <!-- Line Items Table with Watermark -->
        <div class="table-area">
            <div class="watermark-text">GiriRaj</div>
            <table class="bill-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">NO</th>
                        <th class="al-left" style="width: 44%;">Description Of Goods</th>
                        <th style="width: 65px;">HSN<br>SAC</th>
                        <th style="width: 75px;">QUANTITY<br><span style="font-weight: normal; text-transform: none;">Nos</span></th>
                        <th style="width: 65px;">RATE</th>
                        <th style="width: 60px;">RATE/</th>
                        <th class="al-right" style="width: 85px;">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $rowCount = count($invoice->items);
                        $fillCount = max(0, 10 - $rowCount);
                    @endphp
                    @foreach($invoice->items as $idx => $item)
                    <tr>
                        <td class="al-center">{{ $idx + 1 }}</td>
                        <td class="item-name al-left">{{ strtoupper($item->product->name) }}</td>
                        <td class="al-center">{{ $item->product->hsn_code ?? '392690' }}</td>
                        <td class="al-center" style="font-weight: bold;">{{ number_format($item->quantity, 0) }}</td>
                        <td class="al-center">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="al-center">1 Nos</td>
                        <td class="al-right" style="font-weight: bold;">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach

                    <!-- Blank Filler Rows with 0.00 like original bill -->
                    @for($f = 0; $f < $fillCount; $f++)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="al-right" style="color: #4b5563;">0.00</td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Summary & Totals Area -->
        <div class="summary-container">
            <div class="summary-left-box">
                <div class="delivery-box">
                    DELIVERY AT: &nbsp;{{ $invoice->delivery_at ?: '' }}
                </div>
                <div class="vehicle-line">
                    VEHICLE NO: {{ $invoice->transporter ? ($invoice->transporter->vehicle_no ?: $invoice->transporter->name) : ($invoice->lr_number ?: '') }}
                </div>

                <div class="bank-section">
                    <div class="bank-title">OUR BANK DETAILS : AXIS BANK, VASTRAL BRANCH.</div>
                    <div><strong>A/C NO:</strong> &nbsp; 913020054796962</div>
                    <div><strong>IFSC/RTGS:</strong> UTIB0001658</div>
                </div>

                <div class="words-section">
                    <div class="words-title">Amount In Words :</div>
                    <div class="words-val">
                        Rs. @php
                        function numToWordsINR($n){
                            $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                                     'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
                            $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
                            if ($n == 0) return 'Zero';
                            $res = '';
                            if ($n >= 10000000) { $res .= numToWordsINR((int)($n / 10000000)) . ' Crore '; $n %= 10000000; }
                            if ($n >= 100000) { $res .= numToWordsINR((int)($n / 100000)) . ' Lakh '; $n %= 100000; }
                            if ($n >= 1000) { $res .= numToWordsINR((int)($n / 1000)) . ' Thousand '; $n %= 1000; }
                            if ($n >= 100) { $res .= $ones[(int)($n / 100)] . ' Hundred '; $n %= 100; }
                            if ($n >= 20) { $res .= $tens[(int)($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '') . ' '; }
                            elseif ($n > 0) { $res .= $ones[$n] . ' '; }
                            return trim($res);
                        }
                        $totalVal = (int)round($invoice->grand_total);
                        echo numToWordsINR($totalVal) . ' Only.';
                        @endphp
                    </div>
                </div>
            </div>

            <div class="calc-side">
                @php
                    $isInter = ($invoice->igst > 0);
                    $cgstRate = ($invoice->subtotal > 0 && $invoice->cgst > 0) ? round(($invoice->cgst / $invoice->subtotal) * 100, 2) : 9.00;
                    $sgstRate = ($invoice->subtotal > 0 && $invoice->sgst > 0) ? round(($invoice->sgst / $invoice->subtotal) * 100, 2) : 9.00;
                    $igstRate = ($invoice->subtotal > 0 && $invoice->igst > 0) ? round(($invoice->igst / $invoice->subtotal) * 100, 2) : 18.00;
                    
                    $exactTotal = $invoice->subtotal + $invoice->cgst + $invoice->sgst + $invoice->igst;
                    $roundOff = round($invoice->grand_total - $exactTotal, 2);
                @endphp
                <div class="calc-row">
                    <span>PACKAGING &amp; FORWARDING CHARGES</span>
                    <span>0.00</span>
                </div>
                <div class="calc-row" style="font-weight: bold;">
                    <span>NET AMOUNT</span>
                    <span>{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="calc-row bordered-top">
                    <span>SGST &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $isInter ? '0.00%' : number_format($sgstRate, 2).'%' }}</span>
                    <span>{{ $isInter ? '0.00' : number_format($invoice->sgst, 2) }}</span>
                </div>
                <div class="calc-row">
                    <span>CGST &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $isInter ? '0.00%' : number_format($cgstRate, 2).'%' }}</span>
                    <span>{{ $isInter ? '0.00' : number_format($invoice->cgst, 2) }}</span>
                </div>
                <div class="calc-row">
                    <span>IGST &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $isInter ? number_format($igstRate, 2).'%' : '0.00%' }}</span>
                    <span>{{ $isInter ? number_format($invoice->igst, 2) : '0.00' }}</span>
                </div>
                <div class="calc-row">
                    <span>ROUND OFF(+/-)</span>
                    <span>{{ number_format($roundOff, 2) }}</span>
                </div>
                <div class="calc-row total-bold">
                    <span>TOTAL</span>
                    <span>{{ number_format($invoice->grand_total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Horizontal Divider -->
        <div class="full-divider"></div>

        <!-- Reverse charge line -->
        <div class="reverse-charge-text">
            Amount Of Tax Subject To Reverse Charge:
        </div>

        <!-- Declaration & Signature Grid -->
        <div class="footer-cols">
            <div class="decl-text">
                <p><strong>Declaration:</strong> &nbsp; We declare that this invoice shows the actual</p>
                <p>price of the Goods.</p>
                <p>Payments By Cheque Or Bank Transfer Requested.</p>
                <p>24% Interest Will Be Charged After 1 Month.</p>
                <p>In Case Of Any Discrepancy is Found in This Invoice</p>
                <p>Intimated in Writing Within 7 Days, Otherwise it Will</p>
                <p>Treated as an Order.</p>
                <div class="jurisdiction-center">Jurisdiction Ahmedabad.</div>
            </div>

            <div class="sign-block">
                <div>
                    <div class="for-title">For,</div>
                    <div class="company-name-bold">Shree Giriraj Poly Plast.</div>
                </div>
                <div class="sign-label">Authorised Signatory</div>
            </div>
        </div>

    </div><!-- invoice-content -->

    <!-- Bottom Double Stripe (Red + Teal) -->
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
