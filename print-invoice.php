<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    die("Invalid Invoice ID");
}

$stmt = $pdo->prepare("
    SELECT invoices.*, customers.name as c_name, customers.address as c_address, customers.phone as c_phone, 
           customers.gstin as c_gstin, customers.state as c_state 
    FROM invoices 
    JOIN customers ON invoices.customer_id = customers.id 
    WHERE invoices.id = ?
");
$stmt->execute([$id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    die("Invoice not found");
}

$stmtItems = $pdo->prepare("
    SELECT invoice_items.*, products.name as p_name, 
           COALESCE(invoice_items.hsn_code, products.hsn_code, '392690') as hsn_code,
           COALESCE(invoice_items.unit, products.unit, 'PCS') as item_unit,
           COALESCE(invoice_items.gst_rate, products.gst_rate, 18) as item_gst_rate
    FROM invoice_items 
    JOIN products ON invoice_items.product_id = products.id 
    WHERE invoice_id = ?
");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

function numToWordsINR_php($n){
    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
             'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    if ($n == 0) return 'Zero';
    $res = '';
    if ($n >= 10000000) { $res .= numToWordsINR_php((int)($n / 10000000)) . ' Crore '; $n %= 10000000; }
    if ($n >= 100000) { $res .= numToWordsINR_php((int)($n / 100000)) . ' Lakh '; $n %= 100000; }
    if ($n >= 1000) { $res .= numToWordsINR_php((int)($n / 1000)) . ' Thousand '; $n %= 1000; }
    if ($n >= 100) { $res .= $ones[(int)($n / 100)] . ' Hundred '; $n %= 100; }
    if ($n >= 20) { $res .= $tens[(int)($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '') . ' '; }
    elseif ($n > 0) { $res .= $ones[$n] . ' '; }
    return trim($res);
}

$subtotal = (float)$invoice['subtotal'];
$cgst = (float)$invoice['cgst'];
$sgst = (float)$invoice['sgst'];
$igst = (float)$invoice['igst'];
$grand_total = (float)$invoice['grand_total'];
$isInter = ($igst > 0);
$cgstRate = ($subtotal > 0 && $cgst > 0) ? round(($cgst / $subtotal) * 100, 2) : 9.00;
$sgstRate = ($subtotal > 0 && $sgst > 0) ? round(($sgst / $subtotal) * 100, 2) : 9.00;
$igstRate = ($subtotal > 0 && $igst > 0) ? round(($igst / $subtotal) * 100, 2) : 18.00;
$exactTotal = $subtotal + $cgst + $sgst + $igst;
$roundOff = round($grand_total - $exactTotal, 2);
$rowCount = count($items);
$fillCount = max(0, 8 - $rowCount);
$words = numToWordsINR_php((int)round($grand_total));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>TAX INVOICE - <?= htmlspecialchars($invoice['invoice_number']) ?> - Shree Giriraj Poly Plast</title>
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
    padding: 10px 24px; background: #0f172a;
    display: flex; justify-content: space-between; align-items: center;
    box-shadow: 0 4px 14px rgba(0,0,0,0.25); font-family: 'Montserrat', sans-serif;
}
.no-print .toolbar-brand { font-size: 13px; font-weight: 700; color: #fff; }
.no-print .toolbar-brand span { color: #f43f5e; }
.no-print .toolbar-actions { display: flex; gap: 10px; }
.no-print a, .no-print button {
    padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600;
    cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    border: none; font-family: 'Montserrat', sans-serif; transition: all .2s;
}
.btn-back { background: #334155; color: #e2e8f0; }
.btn-print { background: #e11d48; color: #fff; box-shadow: 0 4px 12px rgba(225,29,72,0.35); }

.page-wrap {
    width: 210mm; min-height: 297mm; margin: 15px auto 30px;
    background: #ffffff; padding: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.18);
    position: relative; display: flex; flex-direction: column;
}
.invoice-content { padding: 10px 18px 12px; flex: 1; display: flex; flex-direction: column; }
.top-color-strip { height: 12px; background: #00897b; width: 100%; }
.sub-tag-top { text-align: center; font-size: 9.5px; color: #4b5563; margin-top: 4px; margin-bottom: 2px; }
.header-grid { display: grid; grid-template-columns: 1.4fr 70px 1.4fr; align-items: center; margin-bottom: 2px; }
.header-left-text { font-size: 9px; font-weight: bold; color: #dc2626; line-height: 1.35; }
.header-center-logo { text-align: center; }
.logo-box-green { width: 44px; height: 44px; background: #689f38; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; }
.logo-box-green span { font-family: 'Great Vibes', cursive; font-size: 30px; color: #fef08a; line-height: 1; }
.header-right-brand { text-align: right; }
.brand-shree-txt { font-size: 11px; font-weight: bold; color: #dc2626; display: block; margin-bottom: -4px; }
.brand-giriraj-txt { font-family: 'Playball', 'Great Vibes', cursive; font-size: 36px; font-weight: bold; color: #dc2626; line-height: 1; }
.brand-giriraj-txt span { color: #1e3a8a; }
.brand-polyplast-txt { font-family: Arial, sans-serif; font-size: 11px; font-weight: 900; color: #00796b; letter-spacing: 2px; text-transform: uppercase; display: block; margin-top: -2px; }
.header-divider-red { height: 1.5px; background: #dc2626; margin: 4px 0 2px; }
.header-address-txt { text-align: center; font-size: 8px; color: #000; line-height: 1.3; font-weight: 500; margin-bottom: 4px; }
.tax-strip-row { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 0; font-size: 10px; font-weight: bold; }
.tax-strip-title { font-size: 12px; font-weight: 900; letter-spacing: 0.5px; }
.tax-strip-copies { font-size: 7.5px; color: #4b5563; font-weight: normal; }

.buyer-meta-grid { display: grid; grid-template-columns: 1.15fr 1fr; border-bottom: 1px solid #000; padding: 4px 0; font-size: 9px; line-height: 1.35; }
.buyer-col { padding-right: 12px; }
.buyer-label { font-size: 8.5px; color: #374151; margin-bottom: 2px; }
.buyer-name { font-weight: bold; font-size: 10px; text-transform: uppercase; }
.buyer-gstin { font-weight: bold; margin-top: 2px; }
.meta-col { padding-left: 6px; }
.meta-line { display: flex; justify-content: space-between; margin-bottom: 1.5px; }
.meta-k { font-weight: bold; }
.meta-v { font-weight: normal; }

.table-area { position: relative; margin-top: 0; flex: 1; }
.watermark-text { position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-14deg); font-family: 'Great Vibes', 'Playball', cursive; font-size: 130px; color: rgba(220, 38, 38, 0.08); font-weight: bold; pointer-events: none; z-index: 0; user-select: none; white-space: nowrap; }
.bill-table { width: 100%; border-collapse: collapse; position: relative; z-index: 1; font-size: 9px; }
.bill-table th { border-bottom: 1px solid #000; padding: 4px 2px; font-size: 8.5px; font-weight: bold; text-align: center; text-transform: uppercase; background: #fafafa; }
.bill-table td { padding: 4px 2px; vertical-align: top; height: 18px; border-bottom: 1px solid #f1f5f9; }
.bill-table .al-left { text-align: left; }
.bill-table .al-center { text-align: center; }
.bill-table .al-right { text-align: right; }
.bill-table .item-name { font-weight: bold; text-transform: uppercase; }

.summary-container { display: grid; grid-template-columns: 1.15fr 1fr; margin-top: 6px; font-size: 9px; }
.summary-left-box { padding-right: 12px; }
.delivery-box { border: 1px solid #000; padding: 2px 6px; display: inline-block; min-width: 130px; font-weight: bold; font-size: 8.5px; margin-bottom: 6px; }
.vehicle-line { font-weight: bold; font-size: 9.5px; margin-bottom: 6px; }
.bank-section { font-size: 8.5px; line-height: 1.35; margin-top: 4px; }
.bank-title { font-weight: bold; }
.words-section { margin-top: 6px; font-size: 8.5px; }
.words-title { font-weight: bold; }
.words-val { font-weight: bold; line-height: 1.3; }

.calc-side { font-size: 9px; }
.calc-row { display: flex; justify-content: space-between; padding: 1.5px 0; }
.calc-row.bordered-top { border-top: 1px solid #000; margin-top: 2px; padding-top: 2px; }
.calc-row.total-bold { border-top: 1px solid #000; margin-top: 2px; padding-top: 3px; font-size: 10.5px; font-weight: bold; }

.full-divider { height: 1px; background: #000; margin: 8px 0 4px; }
.reverse-charge-text { font-size: 8px; font-weight: bold; margin-bottom: 4px; }
.footer-cols { display: grid; grid-template-columns: 1.25fr 1fr; gap: 12px; font-size: 8px; line-height: 1.3; }
.decl-text p { margin-bottom: 1.5px; color: #222; }
.jurisdiction-center { font-weight: bold; color: #000; margin-top: 4px; text-align: center; font-size: 8.5px; }
.sign-block { text-align: right; display: flex; flex-direction: column; justify-content: space-between; }
.for-title { font-size: 9px; }
.company-name-bold { font-size: 10px; font-weight: bold; }
.sign-label { margin-top: 32px; font-size: 8.5px; font-weight: bold; }

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
        🏭 <span>Shree Giriraj Poly Plast</span> — Tax Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?>
    </div>
    <div class="toolbar-actions">
        <a href="invoices.php" class="btn-back"><i class='bx bx-arrow-back'></i> ← Back to Invoices</a>
        <button onclick="exitPrintView()" class="btn-back" style="background:#475569; color:#fff;" title="Close print preview">✕ Exit Print</button>
        <button onclick="window.print()" class="btn-print">🖨 Print Invoice</button>
    </div>
</div>

<script>
function exitPrintView() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.close();
    }
    // Fallback if window.close was blocked by browser
    setTimeout(function() {
        window.location.href = 'invoices.php';
    }, 200);
}
</script>

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
            <div>GSTIN: 24AHUPP7924M1ZG &nbsp;|&nbsp; STATE: GUJARAT (24)</div>
            <div class="tax-strip-title">TAX-INVOICE</div>
            <div class="tax-strip-copies">ORIGINAL / DUPLICATE / TRIPLICATE</div>
        </div>

        <div class="buyer-meta-grid">
            <div class="buyer-col">
                <div class="buyer-label">Buyer's Name, Address &amp; GSTIN:</div>
                <div class="buyer-name">M/S. &nbsp;<?= strtoupper(htmlspecialchars($invoice['c_name'])) ?></div>
                <?php if (!empty($invoice['c_address'])): ?>
                    <div><?= nl2br(htmlspecialchars($invoice['c_address'])) ?></div>
                <?php endif; ?>
                <div>
                    <strong>State:</strong> <?= strtoupper(htmlspecialchars($invoice['c_state'] ?: 'GUJARAT')) ?> 
                    <?php if (strtolower(trim($invoice['c_state'] ?? '')) === 'gujarat' || empty($invoice['c_state'])): ?>
                        <span>(Code: 24)</span>
                    <?php endif; ?>
                </div>
                <div class="buyer-gstin">GSTIN: <?= !empty($invoice['c_gstin']) ? htmlspecialchars($invoice['c_gstin']) : '—' ?></div>
            </div>

            <div class="meta-col">
                <div class="meta-line">
                    <span class="meta-k">INVOICE NO: &nbsp;<span class="meta-v" style="font-weight:bold;"><?= htmlspecialchars($invoice['invoice_number']) ?></span></span>
                    <span class="meta-k">DATE: &nbsp;<span class="meta-v"><?= date('d.m.Y', strtotime($invoice['invoice_date'])) ?></span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">D CHALLAN NO: &nbsp;<span class="meta-v"><?= htmlspecialchars($invoice['challan_number'] ?: ($invoice['lr_number'] ?? '')) ?></span></span>
                    <span class="meta-k">DATE: &nbsp;<span class="meta-v"><?= date('d.m.Y', strtotime($invoice['invoice_date'])) ?></span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">P.O.NO &amp; DATE: &nbsp;<span class="meta-v"><?= htmlspecialchars($invoice['po_number'] ?? '') ?></span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">PAYMENT TERMS: &nbsp;<span class="meta-v"><?= strtoupper(htmlspecialchars($invoice['payment_terms'] ?: '30 Days')) ?></span></span>
                </div>
                <div class="meta-line">
                    <span class="meta-k">VEHICLE NO: &nbsp;<span class="meta-v"><?= htmlspecialchars($invoice['lr_number'] ?? '') ?></span></span>
                </div>
            </div>
        </div>

        <div class="table-area">
            <div class="watermark-text">GiriRaj</div>
            <table class="bill-table">
                <thead>
                    <tr>
                        <th style="width: 30px;">NO</th>
                        <th class="al-left" style="width: 40%;">Description Of Goods</th>
                        <th style="width: 60px;">HSN/SAC</th>
                        <th style="width: 65px;">QUANTITY</th>
                        <th style="width: 50px;">UNIT</th>
                        <th style="width: 65px;">RATE (₹)</th>
                        <th style="width: 55px;">GST %</th>
                        <th class="al-right" style="width: 85px;">AMOUNT (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $idx => $item): 
                        $itemUnit = htmlspecialchars($item['item_unit'] ?: 'PCS');
                    ?>
                    <tr>
                        <td class="al-center"><?= $idx + 1 ?></td>
                        <td class="item-name al-left"><?= strtoupper(htmlspecialchars($item['p_name'])) ?></td>
                        <td class="al-center"><?= htmlspecialchars($item['hsn_code'] ?? '392690') ?></td>
                        <td class="al-center" style="font-weight: bold;"><?= number_format((float)$item['quantity'], 2) ?></td>
                        <td class="al-center"><?= $itemUnit ?></td>
                        <td class="al-center"><?= number_format((float)$item['unit_price'], 2) ?></td>
                        <td class="al-center"><?= htmlspecialchars($item['item_gst_rate']) ?>%</td>
                        <td class="al-right" style="font-weight: bold;"><?= number_format((float)$item['total_price'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <?php for ($f = 0; $f < $fillCount; $f++): ?>
                    <tr>
                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        <td class="al-right" style="color: #cbd5e1;">-</td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <div class="summary-container">
            <div class="summary-left-box">
                <div class="delivery-box">
                    DELIVERY AT: &nbsp;<?= htmlspecialchars($invoice['delivery_at'] ?: 'AS PER ORDER') ?>
                </div>

                <div class="bank-section">
                    <div class="bank-title">OUR BANK DETAILS : AXIS BANK, VASTRAL BRANCH.</div>
                    <div><strong>A/C NO:</strong> &nbsp; 913020054796962</div>
                    <div><strong>IFSC/RTGS:</strong> UTIB0001658</div>
                </div>

                <div class="words-section">
                    <div class="words-title">Amount In Words:</div>
                    <div class="words-val">
                        Rupees <?= $words ?> Only.
                    </div>
                </div>
            </div>

            <div class="calc-side">
                <div class="calc-row" style="font-weight: bold;">
                    <span>TAXABLE AMOUNT</span>
                    <span>₹<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="calc-row bordered-top">
                    <span>CGST &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $isInter ? '0.00%' : number_format($cgstRate, 2).'%' ?></span>
                    <span>₹<?= $isInter ? '0.00' : number_format($cgst, 2) ?></span>
                </div>
                <div class="calc-row">
                    <span>SGST &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $isInter ? '0.00%' : number_format($sgstRate, 2).'%' ?></span>
                    <span>₹<?= $isInter ? '0.00' : number_format($sgst, 2) ?></span>
                </div>
                <div class="calc-row">
                    <span>IGST &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $isInter ? number_format($igstRate, 2).'%' : '0.00%' ?></span>
                    <span>₹<?= $isInter ? number_format($igst, 2) : '0.00' ?></span>
                </div>
                <?php if ($roundOff != 0): ?>
                <div class="calc-row">
                    <span>ROUND OFF (+/-)</span>
                    <span>₹<?= number_format($roundOff, 2) ?></span>
                </div>
                <?php endif; ?>
                <div class="calc-row total-bold">
                    <span>GRAND TOTAL</span>
                    <span style="font-size:12px;">₹<?= number_format($grand_total, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="full-divider"></div>
        <div class="reverse-charge-text">
            Amount Of Tax Subject To Reverse Charge: &nbsp;<strong>NO</strong>
        </div>

        <div class="footer-cols">
            <div class="decl-text">
                <p><strong>Declaration:</strong> We declare that this invoice shows the actual price of the Goods described and that all particulars are true and correct.</p>
                <p>Payments By Cheque Or Bank Transfer Requested. 24% Interest Will Be Charged After Due Date.</p>
                <div class="jurisdiction-center">Subject to Ahmedabad Jurisdiction.</div>
            </div>

            <div class="sign-block">
                <div>
                    <div class="for-title">For,</div>
                    <div class="company-name-bold">Shree Giriraj Poly Plast</div>
                </div>
                <div class="sign-label">Authorised Signatory</div>
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
