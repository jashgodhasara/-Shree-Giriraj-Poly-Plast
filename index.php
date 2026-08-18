<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();
error_reporting(0);

// Fetch key metrics
$cust_count = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$supp_count = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();

// Fetch stock metrics
$raw_stock = $pdo->query("SELECT SUM(stock_quantity) FROM materials WHERE type='Raw Material'")->fetchColumn() ?: 0;
$final_stock = $pdo->query("SELECT SUM(stock_quantity) FROM materials WHERE type='Final Product'")->fetchColumn() ?: 0;

// Fetch recent production
$recent_prod = $pdo->query("
    SELECT p.*, rm.name as rm_name, fp.name as fp_name 
    FROM production_logs p 
    LEFT JOIN materials rm ON p.raw_material_id = rm.id
    LEFT JOIN materials fp ON p.final_product_id = fp.id
    ORDER BY p.date DESC LIMIT 5
")->fetchAll();

// Fetch recent invoices
$recent_inv = $pdo->query("
    SELECT i.*, c.name as customer_name 
    FROM invoices i 
    JOIN customers c ON i.customer_id = c.id 
    ORDER BY i.id DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="manifest.json">
    <title>ERP Dashboard - Shree Giriraj Poly Plast</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border);
            border-top: 4px solid var(--primary);
            box-shadow: var(--shadow-sm);
        }
        .stat-card.green { border-top-color: var(--success); }
        .stat-card.orange { border-top-color: #f59e0b; }
        .stat-card.purple { border-top-color: #8b5cf6; }
        
        .stat-title { color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
        .stat-value { font-size: 1.8rem; font-weight: 700; color: var(--text-main); margin-top: 8px; }
        
        .two-col-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media(max-width: 1024px) { .two-col-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header" style="margin-bottom: 20px;">
            <h1>Overview Dashboard</h1>
            <div style="color:var(--text-muted); font-weight:500;">Welcome to Shree Giriraj ERP</div>
        </div>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-title">Customers</div>
                <div class="stat-value"><?= $cust_count ?></div>
            </div>
            <div class="stat-card purple">
                <div class="stat-title">Suppliers</div>
                <div class="stat-value"><?= $supp_count ?></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-title">Raw Material Stock</div>
                <div class="stat-value"><?= number_format($raw_stock, 2) ?> <span style="font-size:1rem; color:var(--text-muted)">Kg</span></div>
            </div>
            <div class="stat-card green">
                <div class="stat-title">Final Product Stock</div>
                <div class="stat-value"><?= number_format($final_stock, 0) ?> <span style="font-size:1rem; color:var(--text-muted)">Pcs</span></div>
            </div>
        </div>

        <div class="two-col-layout">
            <div class="glass-card" style="padding: 0; overflow: hidden;">
                <div style="padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid var(--border); font-weight: 600;">
                    <i class='bx bx-cog'></i> Recent Production Activity
                </div>
                <div class="table-container" style="padding: 0 10px 10px 10px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Input (Kg)</th>
                                <th>Output (Pcs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_prod as $p): ?>
                            <tr>
                                <td><?= date('d M', strtotime($p['date'])) ?></td>
                                <td><?= htmlspecialchars($p['rm_name']) ?> <br><small style="color:var(--text-muted)"><?= $p['raw_material_used_kg'] ?> Kg</small></td>
                                <td style="color:var(--success); font-weight:bold;"><?= $p['final_product_qty_pcs'] ?> Pcs</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recent_prod)): ?>
                                <tr><td colspan="3" class="text-center">No production logged yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card" style="padding: 0; overflow: hidden;">
                <div style="padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid var(--border); font-weight: 600;">
                    <i class='bx bx-receipt'></i> Recent Sales Invoices
                </div>
                <div class="table-container" style="padding: 0 10px 10px 10px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_inv as $inv): ?>
                            <tr>
                                <td>#<?= str_pad($inv['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($inv['customer_name']) ?></td>
                                <td style="font-weight:bold;">₹<?= number_format($inv['grand_total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recent_inv)): ?>
                                <tr><td colspan="3" class="text-center">No invoices created yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
