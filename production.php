<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();

// Fetch materials for dropdowns
$raw_materials = $pdo->query("SELECT id, name FROM materials WHERE type='Raw Material' ORDER BY name")->fetchAll();
$additives     = $pdo->query("SELECT id, name FROM materials WHERE type='Additive' ORDER BY name")->fetchAll();
$final_products= $pdo->query("SELECT id, name FROM materials WHERE type='Final Product' ORDER BY name")->fetchAll();

// Date Filters
$dateFrom    = $_GET['date_from'] ?? '';
$dateTo      = $_GET['date_to']   ?? '';
$filterMonth = $_GET['month']     ?? '';
$filterYear  = $_GET['year']      ?? '';

// Build WHERE
$where  = ["1=1"];
$params = [];

if (!empty($dateFrom)) {
    $where[]  = "p.date >= ?";
    $params[] = $dateFrom;
}
if (!empty($dateTo)) {
    $where[]  = "p.date <= ?";
    $params[] = $dateTo;
}
if (!empty($filterMonth)) {
    $where[]  = "MONTH(p.date) = ?";
    $params[] = (int)$filterMonth;
}
if (!empty($filterYear)) {
    $where[]  = "YEAR(p.date) = ?";
    $params[] = (int)$filterYear;
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT p.*, 
           rm.name as rm_name, 
           ad.name as ad_name, 
           fp.name as fp_name 
    FROM production_logs p 
    LEFT JOIN materials rm ON p.raw_material_id = rm.id
    LEFT JOIN materials ad ON p.additive_id = ad.id
    LEFT JOIN materials fp ON p.final_product_id = fp.id
    WHERE $whereSql
    ORDER BY p.date DESC, p.id DESC
");
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Year list for dropdown
$yearRows = $pdo->query("SELECT DISTINCT YEAR(date) as yr FROM production_logs ORDER BY yr DESC")->fetchAll(PDO::FETCH_COLUMN);

// Summaries
$totalRawKg   = array_sum(array_column($logs, 'raw_material_used_kg'));
$totalPieces  = array_sum(array_column($logs, 'final_product_qty_pcs'));
$totalSalvage = array_sum(array_column($logs, 'salvage_qty_kg'));

$hasFilter = !empty($dateFrom) || !empty($dateTo) || !empty($filterMonth) || !empty($filterYear);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Processing - Shree Giriraj Poly Plast</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .date-filter-card {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }
        .date-filter-card h3 {
            font-size: 0.8rem; font-weight: 700;
            text-transform: uppercase; color: var(--text-muted);
            letter-spacing: 0.6px; margin-bottom: 14px;
            display: flex; align-items: center; gap: 6px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
            gap: 14px; align-items: end;
        }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label {
            font-size: 0.78rem; font-weight: 600;
            color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px;
        }
        .filter-group input, .filter-group select {
            padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px;
            font-size: 0.9rem; font-family: inherit; color: var(--text-main);
            background: #f8fafc; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .filter-group input:focus, .filter-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
            background: #fff;
        }
        .filter-divider { display: flex; align-items: center; font-size: 0.8rem; color: var(--text-muted); padding-bottom: 2px; }
        .filter-actions { display: flex; gap: 8px; align-items: flex-end; }
        .filter-actions .btn { flex: 1; }
        .active-filter-badge {
            display: inline-flex; align-items: center; gap: 4px;
            background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;
            border-radius: 20px; padding: 3px 10px; font-size: 0.75rem; font-weight: 600;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 14px; margin-bottom: 20px;
        }
        .summary-card {
            background: #fff; border: 1px solid var(--border);
            border-radius: 10px; padding: 16px 18px; box-shadow: var(--shadow-sm);
        }
        .summary-card .label { font-size: 0.78rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .summary-card .value { font-size: 1.3rem; font-weight: 700; }
        .summary-card .sub   { font-size: 0.8rem; color: var(--text-muted); margin-top: 2px; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h1>Production / Processing Logs</h1>
                <?php if ($hasFilter): ?>
                    <span class="active-filter-badge"><i class='bx bx-filter-alt'></i> Filter Active</span>
                <?php endif; ?>
            </div>
            <button class="btn btn-primary" onclick="openModal('productionModal')"><i class='bx bx-plus'></i> Log Production</button>
        </div>

        <!-- Date Filter Card -->
        <div class="date-filter-card">
            <h3><i class='bx bx-calendar-alt'></i> Filter by Date / Period</h3>
            <form action="production.php" method="GET">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>From Date</label>
                        <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="filter-divider">to</div>
                    <div class="filter-group">
                        <label>To Date</label>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" max="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="filter-group">
                        <label>Month</label>
                        <select name="month">
                            <option value="">All Months</option>
                            <?php
                            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                            foreach ($months as $i => $m):
                                $val = $i + 1;
                            ?>
                                <option value="<?= $val ?>" <?= $filterMonth == $val ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Year</label>
                        <select name="year">
                            <option value="">All Years</option>
                            <?php foreach ($yearRows as $yr): ?>
                                <option value="<?= $yr ?>" <?= $filterYear == $yr ? 'selected' : '' ?>><?= $yr ?></option>
                            <?php endforeach; ?>
                            <?php if (empty($yearRows)): ?>
                                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                    <option value="<?= $y ?>" <?= $filterYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary" style="padding: 9px 16px;">
                            <i class='bx bx-search'></i> Filter
                        </button>
                        <?php if ($hasFilter): ?>
                            <a href="production.php" class="btn" style="background:#f1f5f9; color:#334155; padding: 9px 14px;">Clear</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="label">Log Entries</div>
                <div class="value" style="color:var(--primary)"><?= count($logs) ?></div>
                <div class="sub"><?= $hasFilter ? 'Filtered' : 'Total' ?> records</div>
            </div>
            <div class="summary-card">
                <div class="label">Raw Material Used</div>
                <div class="value" style="color:#8b5cf6"><?= number_format($totalRawKg, 1) ?> <span style="font-size:0.9rem;">Kg</span></div>
                <div class="sub">Total input</div>
            </div>
            <div class="summary-card">
                <div class="label">Output Produced</div>
                <div class="value" style="color:var(--success)"><?= number_format($totalPieces) ?> <span style="font-size:0.9rem;">Pcs</span></div>
                <div class="sub">Final product</div>
            </div>
            <div class="summary-card">
                <div class="label">Salvage / Scrap</div>
                <div class="value" style="color:var(--danger)"><?= number_format($totalSalvage, 1) ?> <span style="font-size:0.9rem;">Kg</span></div>
                <div class="sub">Total scrap</div>
            </div>
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
                            <tr>
                                <td colspan="5" class="text-center" style="padding:30px; color:var(--text-muted);">
                                    <i class='bx bx-calendar-x' style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                                    <?= $hasFilter ? 'No production logs found for the selected period.' : 'No production logs found.' ?>
                                </td>
                            </tr>
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
