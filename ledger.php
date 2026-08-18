<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();

// Fetch entities for dropdowns
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
$suppliers  = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();

// Date Filters
$dateFrom    = $_GET['date_from']  ?? '';
$dateTo      = $_GET['date_to']    ?? '';
$filterMonth = $_GET['month']      ?? '';
$filterYear  = $_GET['year']       ?? '';
$filterType  = $_GET['type']       ?? '';   // Credit / Debit

// Build WHERE
$where  = ["1=1"];
$params = [];

if (!empty($dateFrom)) {
    $where[]  = "transaction_date >= ?";
    $params[] = $dateFrom;
}
if (!empty($dateTo)) {
    $where[]  = "transaction_date <= ?";
    $params[] = $dateTo;
}
if (!empty($filterMonth)) {
    $where[]  = "MONTH(transaction_date) = ?";
    $params[] = (int)$filterMonth;
}
if (!empty($filterYear)) {
    $where[]  = "YEAR(transaction_date) = ?";
    $params[] = (int)$filterYear;
}
if (!empty($filterType)) {
    $where[]  = "type = ?";
    $params[] = $filterType;
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT * FROM ledgers 
    WHERE $whereSql
    ORDER BY transaction_date DESC, id DESC
    LIMIT 300
");
$stmt->execute($params);
$ledgers = $stmt->fetchAll();

// Year list for dropdown
$yearRows = $pdo->query("SELECT DISTINCT YEAR(transaction_date) as yr FROM ledgers ORDER BY yr DESC")->fetchAll(PDO::FETCH_COLUMN);

// Resolve names
foreach ($ledgers as &$l) {
    $name = 'Unknown';
    if ($l['entity_type'] == 'Customer') {
        $key = array_search($l['entity_id'], array_column($customers, 'id'));
        if ($key !== false) $name = $customers[$key]['name'];
    } elseif ($l['entity_type'] == 'Supplier') {
        $key = array_search($l['entity_id'], array_column($suppliers, 'id'));
        if ($key !== false) $name = $suppliers[$key]['name'];
    } elseif ($l['entity_type'] == 'Job Work' || $l['entity_type'] == 'Investor') {
        $name = 'Entity #' . $l['entity_id'];
    }
    $l['entity_name'] = $name;
}
unset($l);

// Summary
$totalCredit = array_sum(array_column(array_filter($ledgers, fn($r) => $r['type'] === 'Credit'), 'amount'));
$totalDebit  = array_sum(array_column(array_filter($ledgers, fn($r) => $r['type'] === 'Debit'),  'amount'));
$netBalance  = $totalCredit - $totalDebit;

$hasFilter = !empty($dateFrom) || !empty($dateTo) || !empty($filterMonth) || !empty($filterYear) || !empty($filterType);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger / Book Keeping - Shree Giriraj Poly Plast</title>
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
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.6px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
            gap: 14px;
            align-items: end;
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
            gap: 14px;
            margin-bottom: 20px;
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
                <h1>Ledger &amp; Book Keeping</h1>
                <?php if ($hasFilter): ?>
                    <span class="active-filter-badge"><i class='bx bx-filter-alt'></i> Filter Active</span>
                <?php endif; ?>
            </div>
            <button class="btn btn-primary" onclick="openModal('ledgerModal')"><i class='bx bx-plus'></i> Add Entry</button>
        </div>

        <!-- Date Filter Card -->
        <div class="date-filter-card">
            <h3><i class='bx bx-calendar-alt'></i> Filter by Date / Period</h3>
            <form action="ledger.php" method="GET">
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

                    <div class="filter-group">
                        <label>Type</label>
                        <select name="type">
                            <option value="">All Types</option>
                            <option value="Credit" <?= $filterType === 'Credit' ? 'selected' : '' ?>>Credit (Inflow)</option>
                            <option value="Debit"  <?= $filterType === 'Debit'  ? 'selected' : '' ?>>Debit (Outflow)</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary" style="padding: 9px 16px;">
                            <i class='bx bx-search'></i> Filter
                        </button>
                        <?php if ($hasFilter): ?>
                            <a href="ledger.php" class="btn" style="background:#f1f5f9; color:#334155; padding: 9px 14px;">Clear</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="label">Total Entries</div>
                <div class="value" style="color:var(--primary)"><?= count($ledgers) ?></div>
                <div class="sub"><?= $hasFilter ? 'Filtered results' : 'All records' ?></div>
            </div>
            <div class="summary-card">
                <div class="label">Total Credit</div>
                <div class="value" style="color:var(--success)">₹<?= number_format($totalCredit, 0) ?></div>
                <div class="sub">Total Inflow</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Debit</div>
                <div class="value" style="color:var(--danger)">₹<?= number_format($totalDebit, 0) ?></div>
                <div class="sub">Total Outflow</div>
            </div>
            <div class="summary-card">
                <div class="label">Net Balance</div>
                <div class="value" style="color:<?= $netBalance >= 0 ? 'var(--success)' : 'var(--danger)' ?>">
                    <?= $netBalance >= 0 ? '+' : '' ?>₹<?= number_format(abs($netBalance), 0) ?>
                </div>
                <div class="sub">Credit - Debit</div>
            </div>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Entity Type</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>HSN/CSM</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ledgers as $l): ?>
                        <tr>
                            <td><?= date('d-M-Y', strtotime($l['transaction_date'])) ?></td>
                            <td><span class="badge" style="padding: 4px 8px; border-radius: 4px; background: rgba(255,255,255,0.1); font-size: 0.8rem;"><?= htmlspecialchars($l['entity_type']) ?></span></td>
                            <td style="font-weight:600"><?= htmlspecialchars($l['entity_name']) ?></td>
                            <td style="font-size:0.85rem; color:var(--text-muted);"><?= htmlspecialchars($l['description']) ?></td>
                            <td style="font-size:0.85rem; font-family:monospace;">
                                <?= $l['hsn_code'] ? 'HSN: '.$l['hsn_code'].'<br>' : '' ?>
                                <?= $l['csm_code'] ? 'CSM: '.$l['csm_code'] : '' ?>
                            </td>
                            <td style="font-weight:bold; color: <?= $l['type'] == 'Credit' ? 'var(--success)' : 'var(--danger)' ?>">
                                <?= $l['type'] == 'Credit' ? '+' : '-' ?>₹<?= number_format($l['amount'], 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($ledgers)): ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding:30px; color:var(--text-muted);">
                                    <i class='bx bx-calendar-x' style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                                    <?= $hasFilter ? 'No ledger entries found for the selected period.' : 'No ledger entries found.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Ledger Modal -->
    <div class="modal-backdrop" id="ledgerModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Add Ledger Entry</h2>
                <button class="close-btn">&times;</button>
            </div>
            <form id="ledgerForm" onsubmit="saveLedger(event)">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="flex gap-4">
                    <div class="form-group" style="flex:1">
                        <label>Entity Type</label>
                        <select name="entity_type" class="form-control" required onchange="toggleEntitySelect(this.value)">
                            <option value="Customer">Customer</option>
                            <option value="Supplier">Supplier</option>
                            <option value="Investor">Investor</option>
                            <option value="Job Work">Job Work</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="flex:2" id="entity-customer">
                        <label>Select Customer</label>
                        <select name="entity_id_cust" class="form-control entity-select">
                            <?php foreach($customers as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="flex:2; display:none;" id="entity-supplier">
                        <label>Select Supplier</label>
                        <select name="entity_id_supp" class="form-control entity-select">
                            <?php foreach($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="flex:2; display:none;" id="entity-manual">
                        <label>Entity ID (Manual)</label>
                        <input type="number" name="entity_id_manual" class="form-control entity-select" value="1">
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <div class="form-group" style="flex:1">
                        <label>Transaction Type</label>
                        <select name="type" class="form-control" required>
                            <option value="Credit">Credit (+ Inflow)</option>
                            <option value="Debit">Debit (- Outflow)</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1">
                        <label>Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="form-group" style="flex:1">
                        <label>HSN Code (Optional)</label>
                        <input type="text" name="hsn_code" class="form-control">
                    </div>
                    <div class="form-group" style="flex:1">
                        <label>CSM Code (Optional)</label>
                        <input type="text" name="csm_code" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description / Notes</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>

                <input type="hidden" name="entity_id" id="final_entity_id" value="0">

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary"><i class='bx bx-check'></i> Save Entry</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    function toggleEntitySelect(type) {
        document.getElementById('entity-customer').style.display = 'none';
        document.getElementById('entity-supplier').style.display = 'none';
        document.getElementById('entity-manual').style.display = 'none';
        
        if (type === 'Customer') document.getElementById('entity-customer').style.display = 'block';
        else if (type === 'Supplier') document.getElementById('entity-supplier').style.display = 'block';
        else document.getElementById('entity-manual').style.display = 'block';
    }

    async function saveLedger(e) {
        e.preventDefault();
        
        // Determine final entity ID
        const type = e.target.querySelector('select[name="entity_type"]').value;
        let entityId = 0;
        if (type === 'Customer') entityId = e.target.querySelector('select[name="entity_id_cust"]').value;
        else if (type === 'Supplier') entityId = e.target.querySelector('select[name="entity_id_supp"]').value;
        else entityId = e.target.querySelector('input[name="entity_id_manual"]').value;
        
        document.getElementById('final_entity_id').value = entityId;

        const formData = new FormData(e.target);
        
        try {
            const res = await fetch('api/save_ledger.php', { method: 'POST', body: formData });
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
    
    // Init state
    toggleEntitySelect('Customer');
    </script>
</body>
</html>
