<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

// Get raw JSON data
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $customer_id = $data['customer_id'] ?? 0;
    $date = $data['date'] ?? date('Y-m-d');
    $items = $data['items'] ?? [];
    
    if (empty($customer_id) || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Customer and Items are required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        $pdo->beginTransaction();
        
        // Generate Invoice Number (e.g., INV-YYYYMM-XXXX)
        $prefix = 'INV-' . date('Ym') . '-';
        $stmtMax = $pdo->prepare("SELECT invoice_number FROM invoices WHERE invoice_number LIKE ? ORDER BY CAST(SUBSTRING(invoice_number, ?) AS UNSIGNED) DESC LIMIT 1");
        $stmtMax->execute([$prefix . '%', strlen($prefix) + 1]);
        $lastRow = $stmtMax->fetch();
        
        if ($lastRow) {
            $lastNum = (int) substr($lastRow['invoice_number'], strlen($prefix));
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }
        
        $invoice_number = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        
        // Safety loop to ensure uniqueness
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE invoice_number = ?");
        while (true) {
            $stmtCheck->execute([$invoice_number]);
            if ($stmtCheck->fetchColumn() == 0) {
                break;
            }
            $nextNum++;
            $invoice_number = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }
        
        $subtotal = 0;
        $total_cgst = 0;
        $total_sgst = 0;
        $total_igst = 0;
        $grand_total = 0;
        
        // Determine if IGST applies (check customer state)
        $stmtCust = $pdo->prepare("SELECT name, state FROM customers WHERE id = ?");
        $stmtCust->execute([$customer_id]);
        $customer = $stmtCust->fetch();
        $customer_name = $customer['name'] ?? 'Customer #' . $customer_id;
        $is_igst = false;
        if ($customer && $customer['state']) {
            if (strtolower(trim($customer['state'])) !== 'gujarat') {
                $is_igst = true;
            }
        }
        
        // Insert empty invoice first to get ID
        $stmtInv = $pdo->prepare("INSERT INTO invoices (invoice_number, customer_id, invoice_date, subtotal, cgst, sgst, igst, grand_total, created_by) VALUES (?, ?, ?, 0, 0, 0, 0, 0, ?)");
        $stmtInv->execute([$invoice_number, $customer_id, $date, $createdBy]);
        $invoice_id = $pdo->lastInsertId();
        
        // Insert items and calculate totals
        $stmtItem = $pdo->prepare("INSERT INTO invoice_items (invoice_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $prod_id = $item['id'];
            $qty = $item['quantity'];
            
            // Get product details
            $stmtProd = $pdo->prepare("SELECT price, gst_rate FROM products WHERE id = ?");
            $stmtProd->execute([$prod_id]);
            $prod = $stmtProd->fetch();
            
            $unit_price = $prod['price'];
            $item_total = $unit_price * $qty;
            
            $stmtItem->execute([$invoice_id, $prod_id, $qty, $unit_price, $item_total]);
            
            // Deduct product stock
            $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?")->execute([$qty, $prod_id]);
            
            $subtotal += $item_total;
            $gst_amount = ($item_total * $prod['gst_rate']) / 100;
            
            if ($is_igst) {
                $total_igst += $gst_amount;
            } else {
                $total_cgst += $gst_amount / 2;
                $total_sgst += $gst_amount / 2;
            }
        }
        
        $grand_total = round($subtotal + $total_cgst + $total_sgst + $total_igst, 2);
        
        // Update invoice with totals
        $stmtUpdate = $pdo->prepare("UPDATE invoices SET subtotal = ?, cgst = ?, sgst = ?, igst = ?, grand_total = ? WHERE id = ?");
        $stmtUpdate->execute([$subtotal, $total_cgst, $total_sgst, $total_igst, $grand_total, $invoice_id]);

        // Auto-post Customer Debit Ledger entry
        $stmtLedger = $pdo->prepare("INSERT INTO ledgers (entity_type, entity_id, transaction_date, type, amount, description, created_by) VALUES ('Customer', ?, ?, 'Debit', ?, ?, ?)");
        $stmtLedger->execute([$customer_id, $date, $grand_total, 'Sales Invoice #' . $invoice_number, $createdBy]);
        
        logActivity($pdo, 'CREATE', 'Invoices', "Created Sales Invoice #$invoice_number for Customer '$customer_name' (Total: ₹" . number_format($grand_total, 2) . ")");

        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Invoice created successfully', 'invoice_id' => $invoice_id]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data payload']);
}
?>
