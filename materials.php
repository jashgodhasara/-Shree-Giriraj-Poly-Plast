<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();
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
    <style>
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .badge-type {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .badge-raw { background: #eff6ff; color: #1d4ed8; }
        .badge-additive { background: #fdf2f8; color: #db2777; }
        .badge-final { background: #ecfdf5; color: #047857; }
        .badge-meter { background: #fef3c7; color: #d97706; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h1>Materials &amp; Stock Master</h1>
                <p style="color:var(--text-muted); font-size:0.85rem;">Raw Materials, Additives, Granules, Meter items &amp; Inventory</p>
            </div>
            <button class="btn btn-primary" onclick="openAddMaterialModal()"><i class='bx bx-plus'></i> Add Material</button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Material Name</th>
                            <th>Unit</th>
                            <th>Rate / KG</th>
                            <th>HSN Code</th>
                            <th>Details (Grade/Temp/Size)</th>
                            <th>Current Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($materials as $m): 
                            $typeClass = 'badge-raw';
                            if ($m['type'] === 'Additive') $typeClass = 'badge-additive';
                            if ($m['type'] === 'Final Product') $typeClass = 'badge-final';
                            if (stripos($m['unit'] ?? '', 'mtr') !== false || stripos($m['unit'] ?? '', 'meter') !== false) $typeClass = 'badge-meter';
                        ?>
                        <tr>
                            <td><?= $m['id'] ?></td>
                            <td><span class="badge-type <?= $typeClass ?>"><?= htmlspecialchars($m['type']) ?></span></td>
                            <td style="font-weight:600; color:var(--text-main);"><?= htmlspecialchars($m['name']) ?></td>
                            <td><span style="font-weight:600;"><?= htmlspecialchars($m['unit']) ?></span></td>
                            <td style="color:#059669; font-weight:bold;">
                                <?= (!empty($m['rate_per_kg']) && $m['rate_per_kg'] > 0) ? '₹' . number_format($m['rate_per_kg'], 2) : '—' ?>
                            </td>
                            <td><?= htmlspecialchars($m['hsn_code'] ?: '390110') ?></td>
                            <td>
                                <?php 
                                    if ($m['type'] == 'Additive' && !empty($m['grade_variation'])) echo "Grade: " . htmlspecialchars($m['grade_variation']);
                                    if ($m['type'] == 'Final Product') {
                                        $details = [];
                                        if (!empty($m['temp'])) $details[] = "Temp: " . htmlspecialchars($m['temp']);
                                        if (!empty($m['size'])) $details[] = "Size: " . htmlspecialchars($m['size']);
                                        echo implode(' | ', $details);
                                    }
                                ?>
                            </td>
                            <td style="font-weight:bold; color: <?= ($m['stock_quantity'] > 0 ? '#16a34a' : '#dc2626') ?>;">
                                <?= number_format($m['stock_quantity'], 2) ?> <?= htmlspecialchars($m['unit']) ?>
                            </td>
                            <td>
                                <button class="btn btn-secondary" style="padding: 5px 10px; font-size:0.78rem;" 
                                        onclick='openEditMaterialModal(<?= json_encode($m, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                    <i class='bx bx-edit'></i> Edit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($materials)): ?>
                            <tr><td colspan="9" class="text-center" style="padding:30px; color:var(--text-muted);">No materials found. Click "+ Add Material" to add one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Material Modal (Add / Edit) -->
    <div class="modal-backdrop" id="materialModal">
        <div class="modal" style="max-width: 520px;">
            <div class="modal-header">
                <h2 id="modalMaterialTitle">Add New Material</h2>
                <button class="close-btn" onclick="closeModal('materialModal')">&times;</button>
            </div>
            <form id="materialForm" onsubmit="saveMaterial(event)">
                <input type="hidden" name="id" id="mat_id" value="">

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Material Type *</label>
                        <select name="type" id="mat_type" class="form-control" required onchange="toggleFields(this.value)">
                            <option value="Raw Material">Raw Material (Polymer/Granule)</option>
                            <option value="Additive">Additive / Masterbatch</option>
                            <option value="Final Product">Final Product / Moulded</option>
                            <option value="Meter Category">Meter Category (Pipe/Extruded)</option>
                            <option value="Packaging">Packaging Material</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Unit of Measurement *</label>
                        <select name="unit" id="mat_unit" class="form-control" required>
                            <option value="Kg">Kg (Kilograms)</option>
                            <option value="Pcs">Pcs (Pieces)</option>
                            <option value="MTR">MTR (Meters)</option>
                            <option value="Meter">Meter</option>
                            <option value="Bag">Bag</option>
                            <option value="Roll">Roll</option>
                            <option value="Ton">Ton</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Material Name *</label>
                    <input type="text" name="name" id="mat_name" class="form-control" required placeholder="e.g. PP Homopolymer, White Masterbatch, 20mm PVC Pipe">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Rate per KG / Unit (₹)</label>
                        <input type="number" step="0.01" name="rate_per_kg" id="mat_rate_per_kg" class="form-control" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>HSN Code</label>
                        <input type="text" name="hsn_code" id="mat_hsn_code" class="form-control" value="390110" placeholder="e.g. 390110">
                    </div>
                </div>
                
                <div id="additive-fields" style="display:none;">
                    <div class="form-group">
                        <label>Grade / Color Variation</label>
                        <input type="text" name="grade_variation" id="mat_grade" class="form-control" placeholder="e.g. Milky White, Grade A">
                    </div>
                </div>
                
                <div id="final-fields" style="display:none;">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Temp / Mold Specs</label>
                            <input type="text" name="temp" id="mat_temp" class="form-control" placeholder="e.g. 210°C">
                        </div>
                        <div class="form-group">
                            <label>Size / Dimensions</label>
                            <input type="text" name="size" id="mat_size" class="form-control" placeholder="e.g. 150ml / 25mm">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" step="0.01" name="stock_quantity" id="mat_stock_quantity" class="form-control" value="0">
                </div>

                <div class="text-right mt-4" style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn" style="background:#f1f5f9; color:#334155;" onclick="closeModal('materialModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveMaterial">Save Material</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    function toggleFields(type) {
        document.getElementById('additive-fields').style.display = type === 'Additive' ? 'block' : 'none';
        document.getElementById('final-fields').style.display = type === 'Final Product' ? 'block' : 'none';
        
        const unitSelect = document.getElementById('mat_unit');
        if (type === 'Final Product') {
            unitSelect.value = 'Pcs';
        } else if (type === 'Meter Category') {
            unitSelect.value = 'MTR';
        } else if (type === 'Raw Material') {
            unitSelect.value = 'Kg';
        }
    }

    function openAddMaterialModal() {
        document.getElementById('materialForm').reset();
        document.getElementById('mat_id').value = '';
        document.getElementById('mat_hsn_code').value = '390110';
        document.getElementById('mat_stock_quantity').value = '0';
        document.getElementById('modalMaterialTitle').innerText = 'Add New Material';
        document.getElementById('btnSaveMaterial').innerText = 'Save Material';
        toggleFields('Raw Material');
        openModal('materialModal');
    }

    function openEditMaterialModal(m) {
        document.getElementById('materialForm').reset();
        document.getElementById('mat_id').value = m.id;
        document.getElementById('mat_type').value = m.type || 'Raw Material';
        document.getElementById('mat_name').value = m.name || '';
        document.getElementById('mat_unit').value = m.unit || 'Kg';
        document.getElementById('mat_rate_per_kg').value = (m.rate_per_kg && m.rate_per_kg > 0) ? m.rate_per_kg : (m.price_per_unit || '');
        document.getElementById('mat_hsn_code').value = m.hsn_code || '390110';
        document.getElementById('mat_grade').value = m.grade_variation || '';
        document.getElementById('mat_temp').value = m.temp || '';
        document.getElementById('mat_size').value = m.size || '';
        document.getElementById('mat_stock_quantity').value = m.stock_quantity ?? 0;
        document.getElementById('modalMaterialTitle').innerText = 'Edit Material #' + m.id;
        document.getElementById('btnSaveMaterial').innerText = 'Update Material';
        toggleFields(m.type || 'Raw Material');
        openModal('materialModal');
    }

    async function saveMaterial(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('btnSaveMaterial');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        try {
            const res = await fetch('api/save_material.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Material saved successfully!');
                closeModal('materialModal');
                setTimeout(() => location.reload(), 600);
            } else {
                showToast(data.message || 'Error saving material', 'error');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Save Material';
            }
        } catch(err) {
            showToast('Error connecting to server', 'error');
            submitBtn.disabled = false;
            submitBtn.innerText = 'Save Material';
        }
    }
    </script>
</body>
</html>

