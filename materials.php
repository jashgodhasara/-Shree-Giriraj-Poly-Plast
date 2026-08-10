<?php
require_once 'config/db.php';
$stmt = $pdo->query("SELECT * FROM materials ORDER BY type ASC, name ASC");
$materials = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Materials Master - Shree Giriraj Poly Plast</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1>Materials Master</h1>
            <button class="btn btn-primary" onclick="openModal('materialModal')"><i class='bx bx-plus'></i> Add Material</button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Unit</th>
                            <th>Details (Grade/Temp/Size)</th>
                            <th>Current Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($materials as $m): ?>
                        <tr>
                            <td><?= $m['id'] ?></td>
                            <td><span class="badge badge-<?= strtolower(str_replace(' ', '', $m['type'])) ?>" style="padding: 4px 8px; border-radius: 4px; background: rgba(99, 102, 241, 0.2); font-size: 0.8rem;"><?= htmlspecialchars($m['type']) ?></span></td>
                            <td style="font-weight:600"><?= htmlspecialchars($m['name']) ?></td>
                            <td><?= htmlspecialchars($m['unit']) ?></td>
                            <td>
                                <?php 
                                    if ($m['type'] == 'Additive') echo "Grade: " . htmlspecialchars($m['grade_variation']);
                                    if ($m['type'] == 'Final Product') echo "Temp: " . htmlspecialchars($m['temp']) . " | Size: " . htmlspecialchars($m['size']);
                                ?>
                            </td>
                            <td style="color:var(--success); font-weight:bold;"><?= number_format($m['stock_quantity'], 2) ?> <?= htmlspecialchars($m['unit']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($materials)): ?>
                            <tr><td colspan="6" class="text-center">No materials found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Material Modal -->
    <div class="modal-backdrop" id="materialModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Add New Material</h2>
                <button class="close-btn">&times;</button>
            </div>
            <form id="materialForm" onsubmit="saveMaterial(event)">
                <div class="form-group">
                    <label>Material Type</label>
                    <select name="type" class="form-control" required onchange="toggleFields(this.value)">
                        <option value="Raw Material">Raw Material</option>
                        <option value="Additive">Additive</option>
                        <option value="Final Product">Final Product</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Material Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Unit (e.g. Kg, Pcs)</label>
                    <input type="text" name="unit" class="form-control" required value="Kg">
                </div>
                
                <div id="additive-fields" style="display:none;">
                    <div class="form-group">
                        <label>Grade / Variation</label>
                        <input type="text" name="grade_variation" class="form-control">
                    </div>
                </div>
                
                <div id="final-fields" style="display:none;">
                    <div class="form-group">
                        <label>Temp (Temperature properties)</label>
                        <input type="text" name="temp" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Size</label>
                        <input type="text" name="size" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Opening Stock Quantity</label>
                    <input type="number" step="0.01" name="stock_quantity" class="form-control" value="0">
                </div>
                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">Save Material</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    function toggleFields(type) {
        document.getElementById('additive-fields').style.display = type === 'Additive' ? 'block' : 'none';
        document.getElementById('final-fields').style.display = type === 'Final Product' ? 'block' : 'none';
        
        // Auto adjust unit hint
        const unitInput = document.querySelector('input[name="unit"]');
        if (type === 'Final Product') unitInput.value = 'Pcs';
        else unitInput.value = 'Kg';
    }

    async function saveMaterial(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch('api/save_material.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast('Material saved successfully!');
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
