<?php
require_once 'config/db.php';
$stmt = $pdo->query("SELECT invoices.*, customers.name as customer_name FROM invoices JOIN customers ON invoices.customer_id = customers.id ORDER BY invoices.id DESC");
$invoices = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoices - Shree Giriraj Poly Plast</title>
    <link rel="stylesheet" href="css/style.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1>Invoice History</h1>
            <a href="billing.php" class="btn btn-primary">+ Create New Bill</a>
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
                                <a href="print-invoice.php?id=<?= $inv['id'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size:0.8rem;">View / Print</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="js/app.js"></script>
</body>
</html>
