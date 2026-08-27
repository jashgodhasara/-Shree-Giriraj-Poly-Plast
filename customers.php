<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();
$stmt = $pdo->query("
    SELECT c.*, u.full_name as creator_name, u.username as creator_username 
    FROM customers c 
    LEFT JOIN users u ON c.created_by = u.id 
    ORDER BY c.id DESC
");
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customers - Shree Giriraj Poly Plast</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h1>Customers Master</h1>
                <p style="color:var(--text-muted); font-size:0.85rem;">Client Directory, GSTINs, Registered States &amp; Billing Profiles</p>
            </div>
            <button class="btn btn-primary" onclick="openAddCustomerModal()">+ Add Customer</button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Phone</th>
                            <th>State</th>
                            <th>GSTIN</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($customers as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td style="font-weight:600; color:var(--text-main);"><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= htmlspecialchars($c['phone'] ?: '—') ?></td>
                            <td>
                                <span class="badge" style="background:#e0e7ff; color:#3730a3; padding:3px 8px; border-radius:4px; font-weight:600; font-size:0.75rem;">
                                    <?= htmlspecialchars($c['state'] ?: 'Gujarat') ?>
                                </span>
                            </td>
                            <td><strong><?= htmlspecialchars($c['gstin'] ?: 'Unregistered') ?></strong></td>
                            <td>
                                <small style="color:var(--text-muted); font-weight:600;">
                                    <i class='bx bx-user'></i> <?= htmlspecialchars(!empty($c['creator_name']) ? $c['creator_name'] : (!empty($c['creator_username']) ? $c['creator_username'] : 'System')) ?>
                                </small>
                            </td>
                            <td>
                                <button class="btn btn-secondary" style="padding: 5px 10px; font-size:0.78rem;" 
                                        onclick='openEditCustomerModal(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                    <i class='bx bx-edit'></i> Edit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($customers)): ?>
                            <tr><td colspan="7" class="text-center" style="padding:30px; color:var(--text-muted);">No customers added yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Customer Modal (Add / Edit) -->
    <div class="modal-backdrop" id="customerModal">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="modalCustomerTitle">Add New Customer</h2>
                <button class="close-btn" onclick="closeModal('customerModal')">&times;</button>
            </div>
            <form id="customerForm" onsubmit="saveCustomer(event)">
                <input type="hidden" name="id" id="cust_id" value="">
                <div class="form-group">
                    <label>Customer / Firm Name *</label>
                    <input type="text" name="name" id="cust_name" class="form-control" required placeholder="e.g. Acme Plastics Ltd.">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" id="cust_phone" class="form-control" placeholder="10-digit mobile number">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="cust_email" class="form-control" placeholder="customer@example.com">
                </div>
                <div class="form-group">
                    <label>State * (Used for CGST+SGST vs IGST calculation)</label>
                    <input type="text" name="state" id="cust_state" class="form-control" value="Gujarat" required placeholder="e.g. Gujarat, Maharashtra, Rajasthan">
                </div>
                <div class="form-group">
                    <label>GSTIN (15 Digits)</label>
                    <input type="text" name="gstin" id="cust_gstin" class="form-control" placeholder="e.g. 24ABCDE1234F1Z5">
                </div>
                <div class="form-group">
                    <label>Billing &amp; Delivery Address</label>
                    <textarea name="address" id="cust_address" class="form-control" rows="3" placeholder="Full factory/office address..."></textarea>
                </div>
                <div class="text-right mt-4" style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn" style="background:#f1f5f9; color:#334155;" onclick="closeModal('customerModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCustomer">Save Customer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    function openAddCustomerModal() {
        document.getElementById('customerForm').reset();
        document.getElementById('cust_id').value = '';
        document.getElementById('cust_state').value = 'Gujarat';
        document.getElementById('modalCustomerTitle').innerText = 'Add New Customer';
        document.getElementById('btnSaveCustomer').innerText = 'Save Customer';
        openModal('customerModal');
    }

    function openEditCustomerModal(c) {
        document.getElementById('customerForm').reset();
        document.getElementById('cust_id').value = c.id;
        document.getElementById('cust_name').value = c.name || '';
        document.getElementById('cust_phone').value = c.phone || '';
        document.getElementById('cust_email').value = c.email || '';
        document.getElementById('cust_state').value = c.state || 'Gujarat';
        document.getElementById('cust_gstin').value = c.gstin || '';
        document.getElementById('cust_address').value = c.address || '';
        document.getElementById('modalCustomerTitle').innerText = 'Edit Customer #' + c.id;
        document.getElementById('btnSaveCustomer').innerText = 'Update Customer';
        openModal('customerModal');
    }

    async function saveCustomer(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('btnSaveCustomer');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';
        
        try {
            const res = await fetch('api/save_customer.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Customer saved!');
                closeModal('customerModal');
                setTimeout(() => location.reload(), 600);
            } else {
                showToast(data.message || 'Error saving customer', 'error');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Save Customer';
            }
        } catch(err) {
            showToast('Error saving customer', 'error');
            submitBtn.disabled = false;
            submitBtn.innerText = 'Save Customer';
        }
    }
    </script>
</body>
</html>

