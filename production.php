<?php
require_once 'config/db.php';

// Fetch materials for dropdowns
$raw_materials = $pdo->query("SELECT id, name FROM materials WHERE type='Raw Material' ORDER BY name")->fetchAll();
$additives = $pdo->query("SELECT id, name FROM materials WHERE type='Additive' ORDER BY name")->fetchAll();
$final_products = $pdo->query("SELECT id, name FROM materials WHERE type='Final Product' ORDER BY name")->fetchAll();

// Fetch production logs
$logs = $pdo->query("
    SELECT p.*, 
           rm.name as rm_name, 
           ad.name as ad_name, 
           fp.name as fp_name 
    FROM production_logs p 
    LEFT JOIN materials rm ON p.raw_material_id = rm.id
    LEFT JOIN materials ad ON p.additive_id = ad.id
    LEFT JOIN materials fp ON p.final_product_id = fp.id
    ORDER BY p.date DESC, p.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Production Processing - Shree Giriraj Poly Plast</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1>Production / Processing Logs</h1>
            <button class="btn btn-primary" onclick="openModal('productionModal')"><i class='bx bx-plus'></i> Log Production</button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Input (Raw Material + Additive)</th>
                            <th>Output (Final Product)</th>
                            <th>Salvage (Scrap)</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                        <tr>
                            <td><?= date('d-M-Y', strtotime($log['date'])) ?></td>
                            <td>
                                <div><strong><?= htmlspecialchars($log['rm_name']) ?>:</strong> <?= $log['raw_material_used_kg'] ?> Kg</div>
                                <?php if($log['ad_name']): ?>
                                    <div style="color:var(--text-muted); font-size:0.85rem;">+ <?= htmlspecialchars($log['ad_name']) ?>: <?= $log['additive_used_kg'] ?> Kg</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><strong><?= htmlspecialchars($log['fp_name']) ?>:</strong></div>
                                <div style="color:var(--success); font-weight:bold; font-size:1.1rem;"><?= $log['final_product_qty_pcs'] ?> Pcs</div>
                            </td>
                            <td style="color:var(--danger);"><?= $log['salvage_qty_kg'] ?> Kg</td>
                            <td style="font-size:0.85rem; color:var(--text-muted); max-width:200px;"><?= htmlspecialchars($log['notes']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($logs)): ?>
                            <tr><td colspan="5" class="text-center">No production logs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Production Modal -->
    <div class="modal-backdrop" id="productionModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Log Daily Production</h2>
                <button class="close-btn">&times;</button>
            </div>
            <form id="productionForm" onsubmit="saveProduction(event)">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <!-- INPUTS -->
                <div style="border-left: 3px solid var(--primary); padding-left: 15px; margin-bottom: 20px;">
                    <h4 style="margin-bottom:10px; color:var(--text-muted);">1. INPUTS</h4>
                    <div class="flex gap-4">
                        <div class="form-group" style="flex:2">
                            <label>Raw Material</label>
                            <select name="raw_material_id" class="form-control" required>
                                <option value="">-- Select Raw Material --</option>
                                <?php foreach($raw_materials as $rm): ?>
                                    <option value="<?= $rm['id'] ?>"><?= htmlspecialchars($rm['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>Used (Kg)</label>
                            <input type="number" step="0.01" name="raw_material_used_kg" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="form-group" style="flex:2">
                            <label>Additive (Optional)</label>
                            <select name="additive_id" class="form-control">
                                <option value="">-- No Additive --</option>
                                <?php foreach($additives as $ad): ?>
                                    <option value="<?= $ad['id'] ?>"><?= htmlspecialchars($ad['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>Used (Kg)</label>
                            <input type="number" step="0.01" name="additive_used_kg" class="form-control" value="0">
                        </div>
                    </div>
                </div>

                <!-- OUTPUTS -->
                <div style="border-left: 3px solid var(--success); padding-left: 15px; margin-bottom: 20px;">
                    <h4 style="margin-bottom:10px; color:var(--text-muted);">2. OUTPUTS</h4>
                    <div class="flex gap-4">
                        <div class="form-group" style="flex:2">
                            <label>Final Product Generated</label>
                            <select name="final_product_id" class="form-control" required>
                                <option value="">-- Select Final Product --</option>
                                <?php foreach($final_products as $fp): ?>
                                    <option value="<?= $fp['id'] ?>"><?= htmlspecialchars($fp['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>Output (Pieces)</label>
                            <input type="number" name="final_product_qty_pcs" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Salvage / Scrap Generated (Kg)</label>
                        <input type="number" step="0.01" name="salvage_qty_kg" class="form-control" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Remarks / Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary"><i class='bx bx-check'></i> Save Production Log</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    async function saveProduction(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const res = await fetch('api/save_production.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message);
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
