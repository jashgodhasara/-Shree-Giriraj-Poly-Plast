<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();
$stmtCust = $pdo->query("SELECT * FROM customers ORDER BY name ASC");
$customers = $stmtCust->fetchAll();

$stmtProd = $pdo->query("SELECT * FROM products ORDER BY name ASC");
$products = $stmtProd->fetchAll();

$stmtMat = $pdo->query("SELECT * FROM materials ORDER BY name ASC");
$materials = $stmtMat->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Tax Invoice - Shree Giriraj Poly Plast</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .customer-info-box {
            display: none;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid var(--primary);
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 10px;
            font-size: 0.85rem;
        }
        .customer-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            margin-top: 6px;
        }
        .info-label { font-weight: 600; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; }
        .info-val { font-weight: 600; color: var(--text-main); }
        .badge-gst-type {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-intra { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-inter { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .item-table th { background: #f1f5f9; padding: 8px 6px; font-size: 0.82rem; text-transform: uppercase; font-weight: 700; }
        .item-table td { padding: 6px 4px; vertical-align: middle; }
    </style>
    <script>
        window.productsList = <?= json_encode($products) ?>;
        window.materialsList = <?= json_encode($materials) ?>;
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h1>Create Tax Invoice</h1>
                <p style="color:var(--text-muted); font-size:0.85rem;">GST Billing, Rate/KG calculation, Meter Category &amp; Instant Stock Deductions</p>
            </div>
            <button class="btn btn-primary" onclick="saveInvoice()" id="btnGenerateInvoice">
                <i class='bx bx-receipt'></i> Generate &amp; Print Invoice
            </button>
        </div>
        
        <!-- Customer & Bill Meta Card -->
        <div class="glass-card mb-4">
            <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-weight:700;">Select Customer *</label>
                    <select id="customer_id" class="form-control" onchange="onCustomerChange(this)" required>
                        <option value="">-- Choose Customer --</option>
                        <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" 
                                    data-name="<?= htmlspecialchars($c['name']) ?>"
                                    data-phone="<?= htmlspecialchars($c['phone'] ?? '') ?>"
                                    data-email="<?= htmlspecialchars($c['email'] ?? '') ?>"
                                    data-gstin="<?= htmlspecialchars($c['gstin'] ?? '') ?>"
                                    data-state="<?= htmlspecialchars($c['state'] ?? 'Gujarat') ?>"
                                    data-address="<?= htmlspecialchars($c['address'] ?? '') ?>">
                                <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['gstin'] ?: 'Unregistered') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-weight:700;">Invoice Date *</label>
                    <input type="date" id="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>PO No &amp; Date</label>
                    <input type="text" id="po_number" class="form-control" placeholder="e.g. PO/2026/101">
                </div>
            </div>

            <!-- Live Customer Info Card -->
            <div id="customerInfoCard" class="customer-info-box">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <strong style="color:var(--primary);" id="dispCustName">Customer Name</strong>
                    <span id="dispCustGstBadge" class="badge-gst-type badge-intra">Intra-State (CGST + SGST)</span>
                </div>
                <div class="customer-info-grid">
                    <div><div class="info-label">GSTIN</div><div class="info-val" id="dispCustGstin">—</div></div>
                    <div><div class="info-label">Phone</div><div class="info-val" id="dispCustPhone">—</div></div>
                    <div><div class="info-label">State</div><div class="info-val" id="dispCustState">Gujarat</div></div>
                    <div><div class="info-label">Address</div><div class="info-val" id="dispCustAddress">—</div></div>
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <div class="glass-card mb-4">
            <div class="table-container">
                <table class="item-table">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Product / Item Description</th>
                            <th style="width: 90px;">Unit</th>
                            <th style="width: 110px;">Quantity</th>
                            <th style="width: 110px;">Rate / KG</th>
                            <th style="width: 120px;">Price / Unit</th>
                            <th style="width: 90px;">GST %</th>
                            <th style="width: 130px;">Amount (₹)</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="billing-items">
                        <!-- Items added via JS -->
                    </tbody>
                </table>
                <div style="padding: 10px 0; border-top: 1px solid var(--border); margin-top: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="addBillingRow()">
                        <i class='bx bx-plus'></i> + Add Item Row
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Transport, Remarks & Totals -->
        <div style="display:grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: start;">
            <div class="glass-card">
                <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 12px; color: var(--text-main);">
                    <i class='bx bx-truck'></i> Dispatch &amp; Delivery Details
                </h3>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>Delivery At / Destination</label>
                        <input type="text" id="delivery_at" class="form-control" placeholder="e.g. Factory Godown, Ahmedabad">
                    </div>
                    <div class="form-group">
                        <label>Vehicle No / LR No</label>
                        <input type="text" id="vehicle_number" class="form-control" placeholder="e.g. GJ-27-TT-8899">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>Delivery Challan No</label>
                        <input type="text" id="challan_number" class="form-control" placeholder="e.g. DC-1045">
                    </div>
                    <div class="form-group">
                        <label>Payment Terms</label>
                        <input type="text" id="payment_terms" class="form-control" placeholder="e.g. 30 Days Credit / Immediate">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Remarks / Notes</label>
                    <textarea id="invoice_notes" class="form-control" rows="2" placeholder="Special delivery instructions or remarks..."></textarea>
                </div>
            </div>

            <div class="glass-card">
                <div class="flex justify-between mb-4" style="font-size:0.9rem;">
                    <span class="text-muted">Subtotal (Taxable):</span>
                    <span style="font-weight:600">₹<span id="display-subtotal">0.00</span></span>
                </div>
                <div class="flex justify-between mb-4" id="row-cgst" style="font-size:0.9rem;">
                    <span class="text-muted">CGST:</span>
                    <span style="font-weight:600">₹<span id="display-cgst">0.00</span></span>
                </div>
                <div class="flex justify-between mb-4" id="row-sgst" style="font-size:0.9rem;">
                    <span class="text-muted">SGST:</span>
                    <span style="font-weight:600">₹<span id="display-sgst">0.00</span></span>
                </div>
                <div class="flex justify-between mb-4" id="row-igst" style="font-size:0.9rem;">
                    <span class="text-muted">IGST:</span>
                    <span style="font-weight:600">₹<span id="display-igst">0.00</span></span>
                </div>
                <div class="flex justify-between mb-4" style="font-size:0.9rem;">
                    <span class="text-muted">Round Off (+/-):</span>
                    <span style="font-weight:600">₹<span id="display-round-off">0.00</span></span>
                </div>
                <div class="flex justify-between" style="border-top:2px solid var(--border); padding-top:14px; margin-top:8px;">
                    <span style="font-weight:700; font-size:1.1rem; color:var(--primary)">Grand Total:</span>
                    <span style="font-weight:800; font-size:1.4rem; color:#059669">₹<span id="display-total">0.00</span></span>
                </div>
            </div>
        </div>
    </main>

    <script src="js/app.js"></script>
    <script src="js/billing.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            addBillingRow();
        });
    </script>
</body>
</html>

