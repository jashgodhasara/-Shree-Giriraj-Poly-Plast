<?php
require_once 'config/db.php';
$stmt = $pdo->query("SELECT * FROM customers ORDER BY id DESC");
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
            <h1>Customers</h1>
            <button class="btn btn-primary" onclick="openModal('customerModal')">+ Add Customer</button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>State</th>
                            <th>GSTIN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($customers as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td style="font-weight:600"><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= htmlspecialchars($c['phone']) ?></td>
                            <td><?= htmlspecialchars($c['state']) ?></td>
                            <td><?= htmlspecialchars($c['gstin']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Customer Modal -->
    <div class="modal-backdrop" id="customerModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Add New Customer</h2>
                <button class="close-btn">&times;</button>
            </div>
            <form id="customerForm" onsubmit="saveCustomer(event)">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" class="form-control" value="Gujarat">
                </div>
                <div class="form-group">
                    <label>GSTIN</label>
                    <input type="text" name="gstin" class="form-control">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3"></textarea>
                </div>
                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    async function saveCustomer(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        try {
            const res = await fetch('api/save_customer.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast('Customer saved!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message, 'error');
            }
        } catch(err) {
            showToast('Error saving customer', 'error');
        }
    }
    </script>
</body>
</html>
