<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();
$stmt = $pdo->query("SELECT * FROM transporters ORDER BY name ASC");
$transporters = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transporters Master - Shree Giriraj Poly Plast</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1>Transporters Master</h1>
            <button class="btn btn-primary" onclick="openModal('transporterModal')"><i class='bx bx-plus'></i> Add Transporter</button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Transporter Name</th>
                            <th>Vehicle Number</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transporters as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td style="font-weight:600"><?= htmlspecialchars($t['name']) ?></td>
                            <td><span style="border:1px solid var(--border); padding:2px 8px; border-radius:4px; font-family:monospace;"><?= htmlspecialchars($t['vehicle_no']) ?></span></td>
                            <td><?= htmlspecialchars($t['phone']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($transporters)): ?>
                            <tr><td colspan="4" class="text-center">No transporters added yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Transporter Modal -->
    <div class="modal-backdrop" id="transporterModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Add New Transporter</h2>
                <button class="close-btn">&times;</button>
            </div>
            <form id="transporterForm" onsubmit="saveTransporter(event)">
                <div class="form-group">
                    <label>Transporter Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Vehicle Number (e.g. GJ-01-AB-1234)</label>
                    <input type="text" name="vehicle_no" class="form-control">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">Save Transporter</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    async function saveTransporter(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const res = await fetch('api/save_transporter.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast('Transporter saved successfully!');
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
