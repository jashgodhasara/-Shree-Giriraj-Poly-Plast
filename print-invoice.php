<?php
require_once 'config/db.php';
$id = $_GET['id'] ?? 0;

if (!$id) {
    die("Invalid Invoice ID");
}

$stmt = $pdo->prepare("SELECT invoices.*, customers.name as c_name, customers.address as c_address, customers.phone as c_phone, customers.gstin as c_gstin, customers.state as c_state FROM invoices JOIN customers ON invoices.customer_id = customers.id WHERE invoices.id = ?");
$stmt->execute([$id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    die("Invoice not found");
}

$stmtItems = $pdo->prepare("SELECT invoice_items.*, products.name as p_name, products.hsn_code, products.gst_rate FROM invoice_items JOIN products ON invoice_items.product_id = products.id WHERE invoice_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

// Our Company Details
$company_name = "SHREE GIRIRAJ POLY PLAST";
$company_address = "Amraiwadi, Ahmedabad, Gujarat - 380026";
$company_phone = "+91 8047647358";
$company_gst = "24XXXXXXXXXX1ZG"; // Using masked GST from Indiamart for demo
$company_state = "Gujarat";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= $invoice['invoice_number'] ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f0f0;
            padding: 20px;
            color: #111;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1a56db;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #555;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-section div {
            width: 48%;
        }
        .info-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .info-box h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            font-size: 16px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
        }
        table.items th, table.items td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }
        table.items th {
            background: #f9fafb;
            font-weight: 600;
        }
        table.items td.right, table.items th.right {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }
        .totals table {
            width: 300px;
            border-collapse: collapse;
        }
        .totals table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .totals table tr:last-child td {
            border-bottom: none;
            font-weight: 700;
            font-size: 18px;
            color: #1a56db;
        }
        .print-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background: #10B981;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-box { box-shadow: none; border: none; max-width: 100%; padding: 0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    
    <button class="print-btn" onclick="window.print()">Print Invoice</button>

    <div class="invoice-box">
        <div class="header">
            <h1><?= $company_name ?></h1>
            <p><?= $company_address ?></p>
            <p>Phone: <?= $company_phone ?> | GSTIN: <?= $company_gst ?></p>
            <h2>TAX INVOICE</h2>
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>Billed To:</h3>
                <strong><?= htmlspecialchars($invoice['c_name']) ?></strong><br>
                <?= nl2br(htmlspecialchars($invoice['c_address'])) ?><br>
                Phone: <?= htmlspecialchars($invoice['c_phone']) ?><br>
                GSTIN: <?= htmlspecialchars($invoice['c_gstin']) ?><br>
                State: <?= htmlspecialchars($invoice['c_state']) ?>
            </div>
            <div class="info-box">
                <h3>Invoice Details:</h3>
                <strong>Invoice No:</strong> <?= $invoice['invoice_number'] ?><br>
                <strong>Date:</strong> <?= date('d-M-Y', strtotime($invoice['invoice_date'])) ?><br>
                <strong>Place of Supply:</strong> <?= htmlspecialchars($invoice['c_state']) ?>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Product Description</th>
                    <th>HSN Code</th>
                    <th class="right">Qty</th>
                    <th class="right">Rate (₹)</th>
                    <th class="right">GST %</th>
                    <th class="right">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($items as $item): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($item['p_name']) ?></td>
                    <td><?= htmlspecialchars($item['hsn_code']) ?></td>
                    <td class="right"><?= $item['quantity'] ?></td>
                    <td class="right"><?= number_format($item['unit_price'], 2) ?></td>
                    <td class="right"><?= $item['gst_rate'] ?>%</td>
                    <td class="right"><?= number_format($item['total_price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Taxable Amount</td>
                    <td class="right">₹<?= number_format($invoice['subtotal'], 2) ?></td>
                </tr>
                <?php if ($invoice['igst'] > 0): ?>
                <tr>
                    <td>IGST</td>
                    <td class="right">₹<?= number_format($invoice['igst'], 2) ?></td>
                </tr>
                <?php else: ?>
                <tr>
                    <td>CGST</td>
                    <td class="right">₹<?= number_format($invoice['cgst'], 2) ?></td>
                </tr>
                <tr>
                    <td>SGST</td>
                    <td class="right">₹<?= number_format($invoice['sgst'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Grand Total</td>
                    <td class="right">₹<?= number_format($invoice['grand_total'], 2) ?></td>
                </tr>
            </table>
        </div>
        
        <div style="margin-top: 50px; display: flex; justify-content: space-between;">
            <div>
                <p><strong>Terms & Conditions:</strong></p>
                <p style="font-size:12px; color:#555;">1. Goods once sold will not be taken back.<br>2. Interest @ 18% p.a. will be charged if payment is delayed.</p>
            </div>
            <div style="text-align: center; margin-top:20px;">
                <p>For <strong><?= $company_name ?></strong></p>
                <br><br><br>
                <p>Authorized Signatory</p>
            </div>
        </div>
    </div>
</body>
</html>
