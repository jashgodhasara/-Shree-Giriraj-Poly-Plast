<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();
$stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC");
$suppliers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Master - Shree Giriraj Poly Plast</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h1>Supplier Master</h1>
                <p style="color:var(--text-muted); font-size:0.85rem;">Vendor Directory, GSTINs, Registered States &amp; Purchase Profiles</p>
            </div>
            <button class="btn btn-primary" onclick="openAddSupplierModal()"><i class='bx bx-plus'></i> Add Supplier</button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supplier Name</th>
                            <th>Phone</th>
                            <th>State</th>
                            <th>GSTIN</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($suppliers as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td style="font-weight:600; color:var(--text-main);"><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['phone'] ?: '—') ?></td>
                            <td>
                                <span class="badge" style="background:#e0e7ff; color:#3730a3; padding:3px 8px; border-radius:4px; font-weight:600; font-size:0.75rem;">
                                    <?= htmlspecialchars($s['state'] ?: 'Gujarat') ?>
                                </span>
                            </td>
                            <td><strong><?= htmlspecialchars($s['gstin'] ?: 'Unregistered') ?></strong></td>
                            <td><?= htmlspecialchars($s['email'] ?: '—') ?></td>
                            <td>
                                <button class="btn btn-secondary" style="padding: 5px 10px; font-size:0.78rem;" 
                                        onclick='openEditSupplierModal(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                    <i class='bx bx-edit'></i> Edit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($suppliers)): ?>
                            <tr><td colspan="7" class="text-center" style="padding:30px; color:var(--text-muted);">No suppliers added yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Supplier Modal (Add / Edit) -->
    <div class="modal-backdrop" id="supplierModal">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="modalSupplierTitle">Add New Supplier</h2>
                <button class="close-btn" onclick="closeModal('supplierModal')">&times;</button>
            </div>
            <form id="supplierForm" onsubmit="saveSupplier(event)">
                <input type="hidden" name="id" id="supp_id" value="">
                <div class="form-group">
                    <label>Supplier / Vendor Name *</label>
                    <input type="text" name="name" id="supp_name" class="form-control" required placeholder="e.g. Reliance Polymers Ltd.">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" id="supp_phone" class="form-control" placeholder="10-digit phone number">
                </div>
                <div class="form-group">
                    <label>State * (Used for CGST+SGST vs IGST)</label>
                    <input type="text" name="state" id="supp_state" class="form-control" value="Gujarat" required placeholder="e.g. Gujarat, Maharashtra">
                </div>
                <div class="form-group">
                    <label>GSTIN (15 Digits)</label>
                    <input type="text" name="gstin" id="supp_gstin" class="form-control" placeholder="e.g. 24ABCDE1234F1Z5">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="supp_email" class="form-control" placeholder="supplier@example.com">
                </div>
                <div class="form-group">
                    <label>Supplier Address</label>
                    <textarea name="address" id="supp_address" class="form-control" rows="2" placeholder="Full vendor address..."></textarea>
                </div>
                <div class="text-right mt-4" style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn" style="background:#f1f5f9; color:#334155;" onclick="closeModal('supplierModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveSupplier">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    function openAddSupplierModal() {
        document.getElementById('supplierForm').reset();
        document.getElementById('supp_id').value = '';
        document.getElementById('supp_state').value = 'Gujarat';
        document.getElementById('modalSupplierTitle').innerText = 'Add New Supplier';
        document.getElementById('btnSaveSupplier').innerText = 'Save Supplier';
        openModal('supplierModal');
    }

    function openEditSupplierModal(s) {
        document.getElementById('supplierForm').reset();
        document.getElementById('supp_id').value = s.id;
        document.getElementById('supp_name').value = s.name || '';
        document.getElementById('supp_phone').value = s.phone || '';
        document.getElementById('supp_state').value = s.state || 'Gujarat';
        document.getElementById('supp_gstin').value = s.gstin || '';
        document.getElementById('supp_email').value = s.email || '';
        document.getElementById('supp_address').value = s.address || '';
        document.getElementById('modalSupplierTitle').innerText = 'Edit Supplier #' + s.id;
        document.getElementById('btnSaveSupplier').innerText = 'Update Supplier';
        openModal('supplierModal');
    }

    async function saveSupplier(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('btnSaveSupplier');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';
        
        try {
            const res = await fetch('api/save_supplier.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Supplier saved successfully!');
                closeModal('supplierModal');
                setTimeout(() => location.reload(), 600);
            } else {
                showToast(data.message || 'Error saving supplier', 'error');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Save Supplier';
            }
        } catch(err) {
            showToast('Error connecting to server', 'error');
            submitBtn.disabled = false;
            submitBtn.innerText = 'Save Supplier';
        }
    }
    </script>
</body>
</html>

