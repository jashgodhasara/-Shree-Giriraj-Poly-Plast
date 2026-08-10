<?php
require_once 'config/db.php';
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
            <h1>Supplier Master</h1>
            <button class="btn btn-primary" onclick="openModal('supplierModal')"><i class='bx bx-plus'></i> Add Supplier</button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supplier Name</th>
                            <th>Phone</th>
                            <th>GSTIN</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($suppliers as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td style="font-weight:600"><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['phone']) ?></td>
                            <td><?= htmlspecialchars($s['gstin']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($suppliers)): ?>
                            <tr><td colspan="5" class="text-center">No suppliers added yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Supplier Modal -->
    <div class="modal-backdrop" id="supplierModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Add New Supplier</h2>
                <button class="close-btn">&times;</button>
            </div>
            <form id="supplierForm" onsubmit="saveSupplier(event)">
                <div class="form-group">
                    <label>Supplier Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label>GSTIN</label>
                    <input type="text" name="gstin" class="form-control">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    async function saveSupplier(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const res = await fetch('api/save_supplier.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast('Supplier saved successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message, 'error');
            }
        } catch(err) {
            showToast('Error connecting to server', 'error');
        }
    }
    </script>
</body>
</html>
