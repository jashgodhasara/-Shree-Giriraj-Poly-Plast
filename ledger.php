<?php
require_once 'config/db.php';

// Fetch entities for dropdowns
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();

// Fetch ledger entries
$ledgers = $pdo->query("
    SELECT * FROM ledgers 
    ORDER BY transaction_date DESC, id DESC 
    LIMIT 100
")->fetchAll();

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ledger / Book Keeping - Shree Giriraj Poly Plast</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1>Ledger & Book Keeping</h1>
            <button class="btn btn-primary" onclick="openModal('ledgerModal')"><i class='bx bx-plus'></i> Add Entry</button>
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
                            <tr><td colspan="6" class="text-center">No ledger entries found.</td></tr>
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
