<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();

// Date Filters
$dateFrom   = $_GET['date_from']  ?? '';
$dateTo     = $_GET['date_to']    ?? '';
$filterMonth = $_GET['month']     ?? '';
$filterYear  = $_GET['year']      ?? '';

// Build dynamic WHERE clause
$where  = ["1=1"];
$params = [];

if (!empty($dateFrom)) {
    $where[]  = "invoices.invoice_date >= ?";
    $params[] = $dateFrom;
}
if (!empty($dateTo)) {
    $where[]  = "invoices.invoice_date <= ?";
    $params[] = $dateTo;
}
if (!empty($filterMonth)) {
    $where[]  = "MONTH(invoices.invoice_date) = ?";
    $params[] = (int)$filterMonth;
}
if (!empty($filterYear)) {
    $where[]  = "YEAR(invoices.invoice_date) = ?";
    $params[] = (int)$filterYear;
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT invoices.*, customers.name as customer_name, u.full_name as creator_name, u.username as creator_username 
    FROM invoices 
    JOIN customers ON invoices.customer_id = customers.id 
    LEFT JOIN users u ON invoices.created_by = u.id
    WHERE $whereSql
    ORDER BY invoices.id DESC
");
$stmt->execute($params);
$invoices = $stmt->fetchAll();

// Totals for filtered results
$totalAmount = array_sum(array_column($invoices, 'grand_total'));

// Year list for dropdown
$yearRows = $pdo->query("SELECT DISTINCT YEAR(invoice_date) as yr FROM invoices ORDER BY yr DESC")->fetchAll(PDO::FETCH_COLUMN);

$hasFilter = !empty($dateFrom) || !empty($dateTo) || !empty($filterMonth) || !empty($filterYear);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - Shree Giriraj Poly Plast</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            align-items: end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .filter-group input,
        .filter-group select {
            padding: 9px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: inherit;
            color: var(--text-main);
            background: #f8fafc;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background: #fff;
        }
        .filter-divider {
            display: flex;
            align-items: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            padding-bottom: 2px;
        }
        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        .filter-actions .btn {
            flex: 1;
        }
        .active-filter-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .summary-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px 18px;
            box-shadow: var(--shadow-sm);
        }
        .summary-card .label { font-size: 0.78rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .summary-card .value { font-size: 1.4rem; font-weight: 700; color: var(--primary); }
        .summary-card .sub   { font-size: 0.8rem; color: var(--text-muted); margin-top: 2px; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h1>Invoice History</h1>
                <?php if ($hasFilter): ?>
                    <span class="active-filter-badge"><i class='bx bx-filter-alt'></i> Filter Active</span>
                <?php endif; ?>
            </div>
            <a href="billing.php" class="btn btn-primary">+ Create New Bill</a>
        </div>

        <!-- Date Filter Card -->
        <div class="date-filter-card">
            <h3><i class='bx bx-calendar-alt'></i> Filter by Date</h3>
            <form action="invoices.php" method="GET">
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
                            <a href="invoices.php" class="btn" style="background:#f1f5f9; color:#334155; padding: 9px 14px;">Clear</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="label">Total Invoices</div>
                <div class="value"><?= count($invoices) ?></div>
                <div class="sub"><?= $hasFilter ? 'Filtered results' : 'All time' ?></div>
            </div>
            <div class="summary-card">
                <div class="label">Total Amount</div>
                <div class="value">₹<?= number_format($totalAmount, 0) ?></div>
                <div class="sub">Grand Total (incl. GST)</div>
            </div>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Subtotal</th>
                            <th>Total GST</th>
                            <th>Grand Total</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($invoices as $inv): 
                            $total_gst = $inv['cgst'] + $inv['sgst'] + $inv['igst'];
                        ?>
                        <tr>
                            <td><?= $inv['invoice_number'] ?></td>
                            <td><?= date('d-M-Y', strtotime($inv['invoice_date'])) ?></td>
                            <td style="font-weight:600"><?= htmlspecialchars($inv['customer_name']) ?></td>
                            <td>₹<?= number_format($inv['subtotal'], 2) ?></td>
                            <td>₹<?= number_format($total_gst, 2) ?></td>
                            <td style="color:var(--primary-color); font-weight:bold;">₹<?= number_format($inv['grand_total'], 2) ?></td>
                            <td>
                                <small style="color:var(--text-muted); font-weight:600;">
                                    <i class='bx bx-user'></i> <?= htmlspecialchars(!empty($inv['creator_name']) ? $inv['creator_name'] : (!empty($inv['creator_username']) ? $inv['creator_username'] : 'System')) ?>
                                </small>
                            </td>
                            <td>
                                <a href="print-invoice.php?id=<?= $inv['id'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size:0.8rem;">View / Print</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($invoices)): ?>
                            <tr>
                                <td colspan="8" class="text-center" style="padding:30px; color:var(--text-muted);">
                                    <i class='bx bx-calendar-x' style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                                    <?= $hasFilter ? 'No invoices found for the selected date range.' : 'No invoices found.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="js/app.js"></script>
</body>
</html>
