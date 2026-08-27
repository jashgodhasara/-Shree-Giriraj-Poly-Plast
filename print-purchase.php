<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die("Invalid Purchase ID");
}

$stmt = $pdo->prepare("
    SELECT purchases.*, suppliers.name as s_name, suppliers.phone as s_phone, suppliers.email as s_email,
           suppliers.gstin as s_gstin, suppliers.address as s_address, suppliers.state as s_state
    FROM purchases 
    JOIN suppliers ON purchases.supplier_id = suppliers.id 
    WHERE purchases.id = ?
");
$stmt->execute([$id]);
$purchase = $stmt->fetch();

if (!$purchase) {
    die("Purchase Bill not found");
}

$stmtItems = $pdo->prepare("SELECT * FROM purchase_items WHERE purchase_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

function numToWordsINR($n) {
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

$subtotal = (float)$purchase['subtotal'];
$cgst = (float)$purchase['cgst'];
$sgst = (float)$purchase['sgst'];
$igst = (float)$purchase['igst'];
$grand_total = (float)$purchase['grand_total'];
$isInter = ($igst > 0);
$words = numToWordsINR((int)round($grand_total));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PURCHASE VOUCHER - <?= htmlspecialchars($purchase['purchase_number']) ?> - Shree Giriraj Poly Plast</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&family=Playball&family=Great+Vibes&display=swap" rel="stylesheet">
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
.no-print .toolbar-brand span { color: #10b981; }
.no-print .toolbar-actions { display: flex; gap: 10px; }
.no-print a, .no-print button {
    padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600;
    cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    border: none; font-family: 'Montserrat', sans-serif;
}
.btn-back { background: #334155; color: #e2e8f0; }
.btn-print { background: #059669; color: #fff; box-shadow: 0 4px 12px rgba(5,150,105,0.35); }

.page-wrap {
    width: 210mm; min-height: 297mm; margin: 15px auto 30px;
    background: #ffffff; padding: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.18);
    position: relative; display: flex; flex-direction: column;
}
.invoice-content { padding: 12px 20px; flex: 1; display: flex; flex-direction: column; }
.top-color-strip { height: 10px; background: #059669; width: 100%; }

.header-grid { display: grid; grid-template-columns: 1.4fr 70px 1.4fr; align-items: center; margin-bottom: 4px; }
.header-left-text { font-size: 9px; font-weight: bold; color: #047857; line-height: 1.35; }
.header-center-logo { text-align: center; }
.logo-box-green { width: 44px; height: 44px; background: #047857; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; }
.logo-box-green span { font-family: 'Great Vibes', cursive; font-size: 30px; color: #fef08a; }
.header-right-brand { text-align: right; }
.brand-shree-txt { font-size: 11px; font-weight: bold; color: #dc2626; }
.brand-giriraj-txt { font-family: 'Playball', 'Great Vibes', cursive; font-size: 32px; font-weight: bold; color: #dc2626; line-height: 1; }
.brand-polyplast-txt { font-family: Arial, sans-serif; font-size: 11px; font-weight: 900; color: #00796b; letter-spacing: 2px; }

.header-divider { height: 1.5px; background: #059669; margin: 4px 0 2px; }
.header-address-txt { text-align: center; font-size: 8.5px; color: #333; margin-bottom: 6px; }

.tax-strip-row { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 4px 0; font-size: 11px; font-weight: bold; }

.party-meta-grid { display: grid; grid-template-columns: 1.2fr 1fr; border-bottom: 1px solid #000; padding: 6px 0; font-size: 9.5px; }
.party-name { font-weight: bold; font-size: 11px; text-transform: uppercase; }

.bill-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 9.5px; }
.bill-table th { border-bottom: 1.5px solid #000; border-top: 1px solid #000; padding: 5px 3px; font-size: 9px; font-weight: bold; text-align: center; text-transform: uppercase; background: #f8fafc; }
.bill-table td { padding: 5px 3px; border-bottom: 1px solid #e2e8f0; }
.al-left { text-align: left; }
.al-center { text-align: center; }
.al-right { text-align: right; }

.summary-container { display: grid; grid-template-columns: 1.2fr 1fr; margin-top: 10px; font-size: 9.5px; }
.calc-row { display: flex; justify-content: space-between; padding: 2px 0; }
.calc-row.total-bold { border-top: 1.5px solid #000; margin-top: 4px; padding-top: 4px; font-size: 11px; font-weight: bold; }

.footer-cols { display: grid; grid-template-columns: 1.2fr 1fr; gap: 16px; margin-top: 24px; font-size: 9px; }
.sign-block { text-align: right; }
.sign-label { margin-top: 40px; font-weight: bold; }

@media print {
    .no-print { display: none !important; }
    body { background: #fff; }
    .page-wrap { width: 100%; margin: 0; box-shadow: none; }
}
</style>
</head>
<body>

<div class="no-print">
    <div class="toolbar-brand">
        📥 <span>Shree Giriraj Poly Plast</span> — Purchase Inward Voucher #<?= htmlspecialchars($purchase['purchase_number']) ?>
    </div>
    <div class="toolbar-actions">
        <a href="purchases.php" class="btn-back">← Back to Purchases</a>
        <button onclick="exitPurchaseView()" class="btn-back" style="background:#475569; color:#fff;" title="Close purchase print">✕ Exit</button>
        <button onclick="window.print()" class="btn-print">🖨 Print Purchase Bill</button>
    </div>
</div>

<script>
function exitPurchaseView() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.close();
    }
    setTimeout(function() {
        window.location.href = 'purchases.php';
    }, 200);
}
</script>

<div class="page-wrap">
    <div class="top-color-strip"></div>
    <div class="invoice-content">
        <div class="header-grid">
            <div class="header-left-text">
                Direct Inward Purchase Receipt<br>
                Raw Material &amp; Goods Purchase
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

        <div class="header-divider"></div>
        <div class="header-address-txt">
            86, Yamuna Ind. Estate, Part - 2, Nr. Chirag Estate, Opp Parth Estate, Jamfal Wadi Canal Road, C.T.M, Ahmedabad-26<br>
            E-mail : shreegirirajp.plast@gmail.com &nbsp; M.: +91 - 94283 81122 / +91 - 6351297816 &nbsp; GSTIN: 24AHUPP7924M1ZG
        </div>

        <div class="tax-strip-row">
            <div>PURCHASE INWARD VOUCHER</div>
            <div>STATUS: COMPLETED / STOCK INWARDED</div>
        </div>

        <div class="party-meta-grid">
            <div>
                <div style="font-size:8.5px; color:#555;">Supplier Details:</div>
                <div class="party-name"><?= htmlspecialchars($purchase['s_name']) ?></div>
                <?php if(!empty($purchase['s_address'])): ?>
                    <div><?= nl2br(htmlspecialchars($purchase['s_address'])) ?></div>
                <?php endif; ?>
                <div><strong>State:</strong> <?= htmlspecialchars($purchase['s_state'] ?: 'Gujarat') ?></div>
                <div><strong>GSTIN:</strong> <?= htmlspecialchars($purchase['s_gstin'] ?: 'Unregistered') ?></div>
                <?php if(!empty($purchase['s_phone'])): ?>
                    <div><strong>Phone:</strong> <?= htmlspecialchars($purchase['s_phone']) ?></div>
                <?php endif; ?>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between;"><strong>Purchase Voucher No:</strong> <span><?= htmlspecialchars($purchase['purchase_number']) ?></span></div>
                <div style="display:flex; justify-content:space-between;"><strong>Date:</strong> <span><?= date('d-M-Y', strtotime($purchase['purchase_date'])) ?></span></div>
                <?php if(!empty($purchase['bill_number'])): ?>
                    <div style="display:flex; justify-content:space-between;"><strong>Supplier Bill No:</strong> <span><?= htmlspecialchars($purchase['bill_number']) ?></span></div>
                <?php endif; ?>
                <?php if(!empty($purchase['vehicle_number'])): ?>
                    <div style="display:flex; justify-content:space-between;"><strong>Vehicle No:</strong> <span><?= htmlspecialchars($purchase['vehicle_number']) ?></span></div>
                <?php endif; ?>
                <div style="display:flex; justify-content:space-between;"><strong>Payment Terms:</strong> <span><?= htmlspecialchars($purchase['payment_terms'] ?: 'Direct Purchase') ?></span></div>
            </div>
        </div>

        <table class="bill-table">
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
                    <th class="al-left">Item Description</th>
                    <th style="width:60px;">Type</th>
                    <th style="width:60px;">HSN</th>
                    <th style="width:70px;">Qty/Weight</th>
                    <th style="width:50px;">Unit</th>
                    <th style="width:70px;">Rate/Unit</th>
                    <th style="width:50px;">GST</th>
                    <th style="width:85px;" class="al-right">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $i => $item): ?>
                <tr>
                    <td class="al-center"><?= $i + 1 ?></td>
                    <td class="al-left" style="font-weight:bold;"><?= htmlspecialchars($item['item_name']) ?></td>
                    <td class="al-center"><?= ucfirst($item['item_type']) ?></td>
                    <td class="al-center"><?= htmlspecialchars($item['hsn_code'] ?: '390110') ?></td>
                    <td class="al-center" style="font-weight:bold;"><?= number_format((float)$item['quantity'], 2) ?></td>
                    <td class="al-center"><?= htmlspecialchars($item['unit'] ?: 'KG') ?></td>
                    <td class="al-center">₹<?= number_format((float)$item['unit_price'], 2) ?></td>
                    <td class="al-center"><?= htmlspecialchars($item['gst_rate']) ?>%</td>
                    <td class="al-right" style="font-weight:bold;">₹<?= number_format((float)$item['total_price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-container">
            <div>
                <div style="font-size:8.5px; font-weight:bold;">Amount In Words:</div>
                <div style="font-weight:bold; font-size:10px; color:#059669; margin-top:2px;">
                    Rupees <?= $words ?> Only.
                </div>
                <?php if(!empty($purchase['notes'])): ?>
                    <div style="margin-top:10px; font-size:8.5px; color:#444;">
                        <strong>Notes:</strong> <?= nl2br(htmlspecialchars($purchase['notes'])) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <div class="calc-row"><span>Subtotal (Taxable):</span> <span>₹<?= number_format($subtotal, 2) ?></span></div>
                <?php if(!$isInter): ?>
                    <div class="calc-row"><span>CGST:</span> <span>₹<?= number_format($cgst, 2) ?></span></div>
                    <div class="calc-row"><span>SGST:</span> <span>₹<?= number_format($sgst, 2) ?></span></div>
                <?php else: ?>
                    <div class="calc-row"><span>IGST:</span> <span>₹<?= number_format($igst, 2) ?></span></div>
                <?php endif; ?>
                <?php if($purchase['round_off'] != 0): ?>
                    <div class="calc-row"><span>Round Off (+/-):</span> <span>₹<?= number_format($purchase['round_off'], 2) ?></span></div>
                <?php endif; ?>
                <div class="calc-row total-bold">
                    <span>GRAND TOTAL:</span>
                    <span style="color:#059669;">₹<?= number_format($grand_total, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="footer-cols">
            <div>
                <p><strong>Note:</strong> Goods received in good condition &amp; added into factory inventory.</p>
            </div>
            <div class="sign-block">
                <div>For, <strong>Shree Giriraj Poly Plast</strong></div>
                <div class="sign-label">Receiver / Store Incharge</div>
            </div>
        </div>
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
