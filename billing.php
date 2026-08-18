<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();
$stmtCust = $pdo->query("SELECT * FROM customers ORDER BY name ASC");
$customers = $stmtCust->fetchAll();

$stmtProd = $pdo->query("SELECT * FROM products ORDER BY name ASC");
$products = $stmtProd->fetchAll();

// Pre-render product options for JS securely
$productsJson = json_encode($products);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Bill - Shree Giriraj Poly Plast</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        const productsList = <?= $productsJson ?>;
        let productOptionsHTML = '';
        productsList.forEach(p => {
            // Escape names to prevent HTML injection in select options
            const safeName = p.name.replace(/</g, "&lt;").replace(/>/g, "&gt;");
            productOptionsHTML += `<option value="${p.id}">${safeName}</option>`;
        });
        window.productOptions = productOptionsHTML;
    </script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1>Create Bill</h1>
            <button class="btn btn-primary" onclick="saveInvoice()">Generate Invoice</button>
        </div>
        
        <div class="glass-card mb-4">
            <div class="flex gap-4">
                <div class="form-group" style="flex:1">
                    <label>Select Customer</label>
                    <select id="customer_id" class="form-control" onchange="calculateTotals()">
                        <option value="">-- Select Customer --</option>
                        <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" data-state="<?= htmlspecialchars($c['state']) ?>">
                                <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['gstin']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="glass-card mb-4">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th width="100">Qty</th>
                            <th width="150">Price</th>
                            <th width="100">GST %</th>
                            <th width="150">Total</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="billing-items">
                        <!-- Items added via JS -->
                    </tbody>
                </table>
                <button class="btn btn-secondary mt-4" onclick="addBillingRow()">+ Add Item</button>
            </div>
        </div>
        
        <div class="glass-card" style="max-width: 400px; margin-left: auto;">
            <div class="flex justify-between mb-4">
                <span class="text-muted">Subtotal:</span>
                <span style="font-weight:600">₹<span id="display-subtotal">0.00</span></span>
            </div>
            <div class="flex justify-between mb-4">
                <span class="text-muted">CGST:</span>
                <span style="font-weight:600">₹<span id="display-cgst">0.00</span></span>
            </div>
            <div class="flex justify-between mb-4">
                <span class="text-muted">SGST:</span>
                <span style="font-weight:600">₹<span id="display-sgst">0.00</span></span>
            </div>
            <div class="flex justify-between mb-4">
                <span class="text-muted">IGST:</span>
                <span style="font-weight:600">₹<span id="display-igst">0.00</span></span>
            </div>
            <div class="flex justify-between" style="border-top:1px solid var(--border-color); padding-top:16px;">
                <span style="font-weight:700; font-size:1.2rem; color:var(--primary-color)">Grand Total:</span>
                <span style="font-weight:700; font-size:1.5rem; color:var(--primary-color)">₹<span id="display-total">0.00</span></span>
            </div>
        </div>
    </main>

    <script src="js/app.js"></script>
    <script src="js/billing.js"></script>
    <script>
        // Add one empty row by default
        document.addEventListener('DOMContentLoaded', () => {
            addBillingRow();
        });
    </script>
</body>
</html>
