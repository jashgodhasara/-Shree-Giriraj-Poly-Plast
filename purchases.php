<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();

// Date Filters
$dateFrom   = $_GET['date_from']  ?? '';
$dateTo     = $_GET['date_to']    ?? '';
$filterSupplier = $_GET['supplier_id'] ?? '';

$where  = ["1=1"];
$params = [];

if (!empty($dateFrom)) {
    $where[]  = "purchases.purchase_date >= ?";
    $params[] = $dateFrom;
}
if (!empty($dateTo)) {
    $where[]  = "purchases.purchase_date <= ?";
    $params[] = $dateTo;
}
if (!empty($filterSupplier)) {
    $where[]  = "purchases.supplier_id = ?";
    $params[] = (int)$filterSupplier;
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT purchases.*, suppliers.name as supplier_name, suppliers.gstin as supplier_gstin, suppliers.phone as supplier_phone,
           u.full_name as creator_name, u.username as creator_username
    FROM purchases
    JOIN suppliers ON purchases.supplier_id = suppliers.id
    LEFT JOIN users u ON purchases.created_by = u.id
    WHERE $whereSql
    ORDER BY purchases.id DESC
");
$stmt->execute($params);
$purchases = $stmt->fetchAll();

// Totals
$totalPurchasesAmount = array_sum(array_column($purchases, 'grand_total'));

// Suppliers & Materials & Products for modals
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();
$materials = $pdo->query("SELECT * FROM materials ORDER BY name ASC")->fetchAll();
$products  = $pdo->query("SELECT * FROM products ORDER BY name ASC")->fetchAll();

$hasFilter = !empty($dateFrom) || !empty($dateTo) || !empty($filterSupplier);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Bills - Shree Giriraj Poly Plast</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .supplier-info-box {
            display: none;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid var(--primary);
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 10px;
            font-size: 0.85rem;
        }
        .supplier-info-grid {
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
        .item-row { border-bottom: 1px solid var(--border); }
        .summary-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px 20px;
            box-shadow: var(--shadow-sm);
        }
        .summary-card .label { font-size: 0.78rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .summary-card .value { font-size: 1.4rem; font-weight: 700; color: var(--primary); }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h1>Purchase Bills</h1>
                <p style="color:var(--text-muted); font-size:0.85rem;">Direct Purchase Entries, Raw Material Inward, Rate/KG &amp; Stock Auto-Sync</p>
            </div>
            <button class="btn btn-primary" onclick="openNewPurchaseModal()"><i class='bx bx-plus'></i> + Direct Purchase Bill</button>
        </div>

        <!-- Filter Card -->
        <div class="glass-card mb-4">
            <form action="purchases.php" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
                <div class="form-group" style="margin-bottom:0; min-width:140px;">
                    <label style="font-size:0.75rem; font-weight:600; text-transform:uppercase;">From Date</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" class="form-control" style="padding:7px 10px;">
                </div>
                <div class="form-group" style="margin-bottom:0; min-width:140px;">
                    <label style="font-size:0.75rem; font-weight:600; text-transform:uppercase;">To Date</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" class="form-control" style="padding:7px 10px;">
                </div>
                <div class="form-group" style="margin-bottom:0; min-width:200px;">
                    <label style="font-size:0.75rem; font-weight:600; text-transform:uppercase;">Supplier</label>
                    <select name="supplier_id" class="form-control" style="padding:7px 10px;">
                        <option value="">All Suppliers</option>
                        <?php foreach($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $filterSupplier == $s['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="padding:8px 16px;"><i class='bx bx-search'></i> Filter</button>
                    <?php if ($hasFilter): ?>
                        <a href="purchases.php" class="btn" style="background:#f1f5f9; color:#334155; padding:8px 12px;">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Summary -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px; margin-bottom:20px;">
            <div class="summary-card">
                <div class="label">Total Purchases</div>
                <div class="value"><?= count($purchases) ?></div>
                <small style="color:var(--text-muted);"><?= $hasFilter ? 'Filtered bills' : 'All time' ?></small>
            </div>
            <div class="summary-card">
                <div class="label">Total Inward Value</div>
                <div class="value" style="color:#059669;">₹<?= number_format($totalPurchasesAmount, 2) ?></div>
                <small style="color:var(--text-muted);">Grand Total (incl. GST)</small>
            </div>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Purchase No</th>
                            <th>Date</th>
                            <th>Supplier Name</th>
                            <th>Supplier GSTIN</th>
                            <th>Subtotal</th>
                            <th>GST Amount</th>
                            <th>Grand Total</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($purchases as $pur): 
                            $gstTotal = $pur['cgst'] + $pur['sgst'] + $pur['igst'];
                        ?>
                        <tr>
                            <td style="font-weight:700; color:var(--primary);"><?= htmlspecialchars($pur['purchase_number']) ?></td>
                            <td><?= date('d-M-Y', strtotime($pur['purchase_date'])) ?></td>
                            <td style="font-weight:600; color:var(--text-main);"><?= htmlspecialchars($pur['supplier_name']) ?></td>
                            <td><?= htmlspecialchars($pur['supplier_gstin'] ?: '—') ?></td>
                            <td>₹<?= number_format($pur['subtotal'], 2) ?></td>
                            <td>₹<?= number_format($gstTotal, 2) ?></td>
                            <td style="color:#059669; font-weight:bold; font-size:1rem;">₹<?= number_format($pur['grand_total'], 2) ?></td>
                            <td>
                                <small style="color:var(--text-muted); font-weight:600;">
                                    <i class='bx bx-user'></i> <?= htmlspecialchars($pur['creator_name'] ?: ($pur['creator_username'] ?: 'System')) ?>
                                </small>
                            </td>
                            <td>
                                <a href="print-purchase.php?id=<?= $pur['id'] ?>" class="btn btn-secondary" style="padding:5px 10px; font-size:0.8rem;">
                                    <i class='bx bx-printer'></i> View Bill
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($purchases)): ?>
                            <tr><td colspan="9" class="text-center" style="padding:30px; color:var(--text-muted);">No purchase bills recorded yet. Click "+ Direct Purchase Bill" to record a purchase.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Create Direct Purchase Modal -->
    <div class="modal-backdrop" id="purchaseModal">
        <div class="modal" style="max-width: 950px; width: 95%;">
            <div class="modal-header">
                <h2>Create Direct Purchase Bill</h2>
                <button class="close-btn" onclick="closeModal('purchaseModal')">&times;</button>
            </div>
            
            <form id="purchaseForm" onsubmit="savePurchaseBill(event)">
                <!-- Supplier Section with Instant Live Details -->
                <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                    <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-weight:700;">Select Supplier *</label>
                            <select id="pur_supplier_id" class="form-control" required onchange="onSupplierSelected(this)">
                                <option value="">-- Choose Supplier --</option>
                                <?php foreach($suppliers as $s): ?>
                                    <option value="<?= $s['id'] ?>" 
                                            data-name="<?= htmlspecialchars($s['name']) ?>"
                                            data-phone="<?= htmlspecialchars($s['phone'] ?? '') ?>"
                                            data-email="<?= htmlspecialchars($s['email'] ?? '') ?>"
                                            data-gstin="<?= htmlspecialchars($s['gstin'] ?? '') ?>"
                                            data-state="<?= htmlspecialchars($s['state'] ?? 'Gujarat') ?>"
                                            data-address="<?= htmlspecialchars($s['address'] ?? '') ?>">
                                        <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['gstin'] ?: 'Unregistered') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-weight:700;">Purchase Date *</label>
                            <input type="date" id="pur_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Supplier Invoice / Bill No</label>
                            <input type="text" id="pur_bill_number" class="form-control" placeholder="e.g. INV/2026/049">
                        </div>
                    </div>

                    <!-- Live Supplier Info Card -->
                    <div id="supplierInfoCard" class="supplier-info-box">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <strong style="color:var(--primary);" id="dispSuppName">Supplier Name</strong>
                            <span id="dispSuppGstBadge" class="badge-gst-type badge-intra">Intra-State (CGST + SGST)</span>
                        </div>
                        <div class="supplier-info-grid">
                            <div><div class="info-label">GSTIN</div><div class="info-val" id="dispSuppGstin">—</div></div>
                            <div><div class="info-label">Phone</div><div class="info-val" id="dispSuppPhone">—</div></div>
                            <div><div class="info-label">State</div><div class="info-val" id="dispSuppState">Gujarat</div></div>
                            <div><div class="info-label">Address</div><div class="info-val" id="dispSuppAddress">—</div></div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Items Table -->
                <div class="table-container mb-4" style="border: 1px solid var(--border); border-radius: 8px;">
                    <table style="font-size: 0.9rem;">
                        <thead style="background: #f1f5f9;">
                            <tr>
                                <th style="width: 140px;">Type</th>
                                <th>Item / Material</th>
                                <th style="width: 100px;">Unit</th>
                                <th style="width: 120px;">Qty / Weight</th>
                                <th style="width: 120px;">Rate / KG</th>
                                <th style="width: 120px;">Unit Price (₹)</th>
                                <th style="width: 90px;">GST %</th>
                                <th style="width: 130px;">Amount (₹)</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="purchaseItemsBody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                    <div style="padding: 10px 14px; background: #fafafa; border-top: 1px solid var(--border);">
                        <button type="button" class="btn btn-secondary" onclick="addPurchaseRow()" style="padding: 6px 14px; font-size:0.85rem;">
                            <i class='bx bx-plus'></i> + Add Item Row
                        </button>
                    </div>
                </div>

                <!-- Calculation Summary & Submit -->
                <div style="display:grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start;">
                    <div>
                        <div class="form-group">
                            <label>Vehicle / Transport Number</label>
                            <input type="text" id="pur_vehicle" class="form-control" placeholder="e.g. GJ-01-AB-1234">
                        </div>
                        <div class="form-group">
                            <label>Remarks / Notes</label>
                            <textarea id="pur_notes" class="form-control" rows="2" placeholder="Material quality remarks, payment term details..."></textarea>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 16px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;">
                            <span class="text-muted">Subtotal (Taxable):</span>
                            <strong>₹<span id="dispPurSubtotal">0.00</span></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;" id="rowPurCgst">
                            <span class="text-muted">CGST:</span>
                            <strong>₹<span id="dispPurCgst">0.00</span></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;" id="rowPurSgst">
                            <span class="text-muted">SGST:</span>
                            <strong>₹<span id="dispPurSgst">0.00</span></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;" id="rowPurIgst">
                            <span class="text-muted">IGST:</span>
                            <strong>₹<span id="dispPurIgst">0.00</span></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;">
                            <span class="text-muted">Round Off (+/-):</span>
                            <strong>₹<span id="dispPurRoundOff">0.00</span></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; border-top:2px solid var(--border); padding-top:10px; margin-top:6px;">
                            <span style="font-weight:700; font-size:1.1rem; color:var(--primary);">Grand Total:</span>
                            <span style="font-weight:800; font-size:1.3rem; color:#059669;">₹<span id="dispPurGrandTotal">0.00</span></span>
                        </div>
                    </div>
                </div>

                <div class="text-right mt-4" style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn" style="background:#f1f5f9; color:#334155;" onclick="closeModal('purchaseModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitPurchase" style="padding: 10px 24px; font-size:1rem;">
                        <i class='bx bx-check-double'></i> Save Purchase Bill &amp; Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    const allMaterials = <?= json_encode($materials) ?>;
    const allProducts = <?= json_encode($products) ?>;
    let purItemCount = 0;

    function openNewPurchaseModal() {
        document.getElementById('purchaseForm').reset();
        document.getElementById('purchaseItemsBody').innerHTML = '';
        document.getElementById('supplierInfoCard').style.display = 'none';
        purItemCount = 0;
        addPurchaseRow();
        openModal('purchaseModal');
    }

    function onSupplierSelected(select) {
        const opt = select.options[select.selectedIndex];
        const infoCard = document.getElementById('supplierInfoCard');
        if (!opt || !select.value) {
            infoCard.style.display = 'none';
            calcPurchaseTotals();
            return;
        }

        const name = opt.dataset.name || '—';
        const phone = opt.dataset.phone || '—';
        const gstin = opt.dataset.gstin || 'Unregistered';
        const state = opt.dataset.state || 'Gujarat';
        const address = opt.dataset.address || '—';

        document.getElementById('dispSuppName').innerText = name;
        document.getElementById('dispSuppGstin').innerText = gstin;
        document.getElementById('dispSuppPhone').innerText = phone;
        document.getElementById('dispSuppState').innerText = state;
        document.getElementById('dispSuppAddress').innerText = address;

        const isIgst = state.toLowerCase().trim() !== 'gujarat' && state.toLowerCase().trim() !== '24';
        const badge = document.getElementById('dispSuppGstBadge');
        if (isIgst) {
            badge.innerText = 'Inter-State (IGST Applicable)';
            badge.className = 'badge-gst-type badge-inter';
        } else {
            badge.innerText = 'Intra-State (CGST + SGST Applicable)';
            badge.className = 'badge-gst-type badge-intra';
        }

        infoCard.style.display = 'block';
        calcPurchaseTotals();
    }

    function addPurchaseRow() {
        const tbody = document.getElementById('purchaseItemsBody');
        const rIndex = purItemCount++;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.id = `pur_row_${rIndex}`;

        tr.innerHTML = `
            <td>
                <select class="form-control item-type-sel" onchange="onItemTypeChange(${rIndex})">
                    <option value="material">Material (Raw/Additive)</option>
                    <option value="product">Product (Finished/Mould)</option>
                </select>
            </td>
            <td>
                <select class="form-control item-id-sel" required onchange="onItemSelectChange(${rIndex})">
                    <option value="">-- Choose Item --</option>
                    ${buildItemOptions('material')}
                </select>
            </td>
            <td>
                <select class="form-control item-unit-sel">
                    <option value="Kg">Kg</option>
                    <option value="Pcs">Pcs</option>
                    <option value="MTR">MTR</option>
                    <option value="Bag">Bag</option>
                    <option value="Roll">Roll</option>
                    <option value="Ton">Ton</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control item-qty" value="1" min="0.01" required oninput="onItemQtyOrRateChange(${rIndex})">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control item-rate-kg" placeholder="0.00" oninput="onRateKgChange(${rIndex})">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control item-unit-price" placeholder="0.00" required oninput="calcPurchaseTotals()">
            </td>
            <td>
                <select class="form-control item-gst-rate" onchange="calcPurchaseTotals()">
                    <option value="18">18%</option>
                    <option value="12">12%</option>
                    <option value="5">5%</option>
                    <option value="28">28%</option>
                    <option value="0">0%</option>
                </select>
            </td>
            <td style="font-weight:700; color:var(--text-main); vertical-align:middle;">
                ₹<span class="item-line-total">0.00</span>
            </td>
            <td style="vertical-align:middle; text-align:center;">
                <button type="button" class="btn btn-danger" style="padding:4px 8px; font-size:0.8rem;" onclick="removePurchaseRow(${rIndex})">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
    }

    function removePurchaseRow(idx) {
        const row = document.getElementById(`pur_row_${idx}`);
        if (row) row.remove();
        calcPurchaseTotals();
    }

    function buildItemOptions(type) {
        let html = '';
        if (type === 'material') {
            allMaterials.forEach(m => {
                const safeName = m.name.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                html += `<option value="${m.id}" data-unit="${m.unit||'Kg'}" data-rate="${m.rate_per_kg||m.price_per_unit||0}" data-hsn="${m.hsn_code||'390110'}">${safeName} (${m.type})</option>`;
            });
        } else {
            allProducts.forEach(p => {
                const safeName = p.name.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                html += `<option value="${p.id}" data-unit="${p.unit||'PCS'}" data-rate="${p.price||0}" data-rate-kg="${p.rate_per_kg||0}" data-gst="${p.gst_rate||18}" data-hsn="${p.hsn_code||'392690'}">${safeName}</option>`;
            });
        }
        return html;
    }

    function onItemTypeChange(idx) {
        const row = document.getElementById(`pur_row_${idx}`);
        const type = row.querySelector('.item-type-sel').value;
        const itemSelect = row.querySelector('.item-id-sel');
        itemSelect.innerHTML = `<option value="">-- Choose Item --</option>` + buildItemOptions(type);
    }

    function onItemSelectChange(idx) {
        const row = document.getElementById(`pur_row_${idx}`);
        const select = row.querySelector('.item-id-sel');
        const opt = select.options[select.selectedIndex];
        if (!opt || !select.value) return;

        const unit = opt.dataset.unit || 'Kg';
        const rate = parseFloat(opt.dataset.rate) || 0;
        const rateKg = parseFloat(opt.dataset.rateKg) || 0;
        const gst = opt.dataset.gst || '18';

        row.querySelector('.item-unit-sel').value = unit;
        row.querySelector('.item-unit-price').value = rate > 0 ? rate : '';
        if (rateKg > 0) row.querySelector('.item-rate-kg').value = rateKg;
        if (gst) row.querySelector('.item-gst-rate').value = gst;

        calcPurchaseTotals();
    }

    function onRateKgChange(idx) {
        const row = document.getElementById(`pur_row_${idx}`);
        const rateKg = parseFloat(row.querySelector('.item-rate-kg').value) || 0;
        const unit = row.querySelector('.item-unit-sel').value;
        if (rateKg > 0 && (unit.toLowerCase() === 'kg' || !row.querySelector('.item-unit-price').value)) {
            row.querySelector('.item-unit-price').value = rateKg;
        }
        calcPurchaseTotals();
    }

    function onItemQtyOrRateChange(idx) {
        calcPurchaseTotals();
    }

    function calcPurchaseTotals() {
        let subtotal = 0;
        let totalCgst = 0;
        let totalSgst = 0;
        let totalIgst = 0;

        const suppSelect = document.getElementById('pur_supplier_id');
        const suppOpt = suppSelect.options[suppSelect.selectedIndex];
        const suppState = suppOpt ? (suppOpt.dataset.state || 'Gujarat').toLowerCase().trim() : 'gujarat';
        const isIgst = (suppState !== 'gujarat' && suppState !== '24');

        document.querySelectorAll('#purchaseItemsBody tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
            const unitPrice = parseFloat(tr.querySelector('.item-unit-price').value) || 0;
            const gstRate = parseFloat(tr.querySelector('.item-gst-rate').value) || 0;

            const taxable = round2(qty * unitPrice);
            const gstAmt = round2((taxable * gstRate) / 100);
            const lineTotal = taxable + gstAmt;

            tr.querySelector('.item-line-total').innerText = lineTotal.toFixed(2);

            subtotal += taxable;
            if (isIgst) {
                totalIgst += gstAmt;
            } else {
                totalCgst += round2(gstAmt / 2);
                totalSgst += round2(gstAmt / 2);
            }
        });

        const exactTotal = subtotal + totalCgst + totalSgst + totalIgst;
        const grandTotal = Math.round(exactTotal);
        const roundOff = round2(grandTotal - exactTotal);

        document.getElementById('dispPurSubtotal').innerText = subtotal.toFixed(2);
        document.getElementById('dispPurCgst').innerText = totalCgst.toFixed(2);
        document.getElementById('dispPurSgst').innerText = totalSgst.toFixed(2);
        document.getElementById('dispPurIgst').innerText = totalIgst.toFixed(2);
        document.getElementById('dispPurRoundOff').innerText = roundOff.toFixed(2);
        document.getElementById('dispPurGrandTotal').innerText = grandTotal.toFixed(2);
    }

    function round2(val) {
        return Math.round((val + Number.EPSILON) * 100) / 100;
    }

    async function savePurchaseBill(e) {
        e.preventDefault();
        const supplierId = document.getElementById('pur_supplier_id').value;
        if (!supplierId) {
            showToast('Please select a supplier', 'error');
            return;
        }

        const items = [];
        document.querySelectorAll('#purchaseItemsBody tr').forEach(tr => {
            const itemType = tr.querySelector('.item-type-sel').value;
            const itemId = tr.querySelector('.item-id-sel').value;
            const unit = tr.querySelector('.item-unit-sel').value;
            const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
            const rateKg = parseFloat(tr.querySelector('.item-rate-kg').value) || 0;
            const unitPrice = parseFloat(tr.querySelector('.item-unit-price').value) || 0;
            const gstRate = parseFloat(tr.querySelector('.item-gst-rate').value) || 0;

            if (itemId && qty > 0) {
                items.push({
                    type: itemType,
                    id: itemId,
                    unit: unit,
                    quantity: qty,
                    rate_per_kg: rateKg,
                    unit_price: unitPrice,
                    gst_rate: gstRate
                });
            }
        });

        if (items.length === 0) {
            showToast('Please select at least one item with valid quantity', 'error');
            return;
        }

        const payload = {
            supplier_id: supplierId,
            date: document.getElementById('pur_date').value,
            bill_number: document.getElementById('pur_bill_number').value,
            vehicle_number: document.getElementById('pur_vehicle').value,
            notes: document.getElementById('pur_notes').value,
            items: items
        };

        const submitBtn = document.getElementById('btnSubmitPurchase');
        submitBtn.disabled = true;
        submitBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Saving &amp; Updating Stock...";

        try {
            const res = await fetch('api/save_purchase.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Purchase bill created successfully!');
                closeModal('purchaseModal');
                setTimeout(() => {
                    window.location.href = `print-purchase.php?id=${data.purchase_id}`;
                }, 800);
            } else {
                showToast(data.message || 'Error saving purchase bill', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = "<i class='bx bx-check-double'></i> Save Purchase Bill &amp; Update Stock";
            }
        } catch(err) {
            showToast('Server connection error', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = "<i class='bx bx-check-double'></i> Save Purchase Bill &amp; Update Stock";
        }
    }
    </script>
</body>
</html>
