<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

// Get raw JSON data
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $customer_id = intval($data['customer_id'] ?? 0);
    $date = $data['date'] ?? date('Y-m-d');
    $po_number = trim($data['po_number'] ?? '');
    $delivery_at = trim($data['delivery_at'] ?? '');
    $vehicle_number = trim($data['vehicle_number'] ?? '');
    $challan_number = trim($data['challan_number'] ?? '');
    $payment_terms = trim($data['payment_terms'] ?? '');
    $notes = trim($data['notes'] ?? '');
    $items = $data['items'] ?? [];
    
    if (empty($customer_id) || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Customer and Items are required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        $pdo->beginTransaction();
        
        // 1. Generate Standard Bill Number format (e.g. SGPP/2026-27/0001 or INV-YYYYMM-XXXX)
        $year = (int)date('Y', strtotime($date));
        $month = (int)date('m', strtotime($date));
        $fyStart = ($month >= 4) ? $year : $year - 1;
        $fyEnd = substr((string)($fyStart + 1), -2);
        $prefix = "SGPP/{$fyStart}-{$fyEnd}/";

        // Query max sequence for current financial year prefix
        $stmtMax = $pdo->prepare("SELECT invoice_number FROM invoices WHERE invoice_number LIKE ? ORDER BY id DESC LIMIT 1");
        $stmtMax->execute([$prefix . '%']);
        $lastRow = $stmtMax->fetch();

        if ($lastRow && !empty($lastRow['invoice_number'])) {
            $lastSeq = (int)substr($lastRow['invoice_number'], strlen($prefix));
            $nextSeq = $lastSeq + 1;
        } else {
            // Check fallback for other formats
            $stmtCount = $pdo->query("SELECT COUNT(*) FROM invoices");
            $nextSeq = (int)$stmtCount->fetchColumn() + 1;
        }

        $invoice_number = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        
        // Safety loop to guarantee uniqueness
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE invoice_number = ?");
        while (true) {
            $stmtCheck->execute([$invoice_number]);
            if ($stmtCheck->fetchColumn() == 0) {
                break;
            }
            $nextSeq++;
            $invoice_number = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        }
        
        // 2. Determine GST Regime (Gujarat = Intra vs Outside = Inter)
        $stmtCust = $pdo->prepare("SELECT name, state, gstin, address FROM customers WHERE id = ?");
        $stmtCust->execute([$customer_id]);
        $customer = $stmtCust->fetch();
        $customer_name = $customer['name'] ?? 'Customer #' . $customer_id;
        
        $customer_state = strtolower(trim($customer['state'] ?? 'gujarat'));
        $is_igst = (!empty($customer_state) && $customer_state !== 'gujarat' && $customer_state !== '24');
        
        // 3. Insert base invoice
        $stmtInv = $pdo->prepare("
            INSERT INTO invoices (invoice_number, customer_id, invoice_date, subtotal, cgst, sgst, igst, round_off, grand_total, lr_number, challan_number, po_number, payment_terms, delivery_at, notes, created_by) 
            VALUES (?, ?, ?, 0, 0, 0, 0, 0, 0, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtInv->execute([$invoice_number, $customer_id, $date, $vehicle_number, $challan_number, $po_number, $payment_terms, $delivery_at, $notes, $createdBy]);
        $invoice_id = $pdo->lastInsertId();
        
        $subtotal = 0;
        $total_cgst = 0;
        $total_sgst = 0;
        $total_igst = 0;

        // 4. Insert items and update product stock
        $stmtItem = $pdo->prepare("
            INSERT INTO invoice_items (invoice_id, product_id, unit, quantity, rate_per_kg, unit_price, gst_rate, hsn_code, total_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($items as $item) {
            $prod_id = intval($item['id']);
            $qty = floatval($item['quantity']);
            $unit = trim($item['unit'] ?? 'PCS');
            $rate_per_kg = floatval($item['rate_per_kg'] ?? 0);
            $unit_price = floatval($item['unit_price'] ?? 0);
            $gst_rate = floatval($item['gst_rate'] ?? 18);
            
            // Get product details
            $stmtProd = $pdo->prepare("SELECT price, gst_rate, hsn_code FROM products WHERE id = ?");
            $stmtProd->execute([$prod_id]);
            $prod = $stmtProd->fetch();
            
            if ($unit_price <= 0) {
                $unit_price = floatval($prod['price'] ?? 0);
            }
            $hsn_code = $prod['hsn_code'] ?? '392690';
            
            $item_taxable = round($qty * $unit_price, 2);
            $gst_amount = round(($item_taxable * $gst_rate) / 100, 2);
            $item_total = $item_taxable + $gst_amount;
            
            $stmtItem->execute([$invoice_id, $prod_id, $unit, $qty, $rate_per_kg, $unit_price, $gst_rate, $hsn_code, $item_total]);
            
            // Deduct product stock quantity
            $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?")->execute([$qty, $prod_id]);
            
            $subtotal += $item_taxable;
            
            if ($is_igst) {
                $total_igst += $gst_amount;
            } else {
                $total_cgst += round($gst_amount / 2, 2);
                $total_sgst += round($gst_amount / 2, 2);
            }
        }
        
        $exact_total = $subtotal + $total_cgst + $total_sgst + $total_igst;
        $grand_total = round($exact_total);
        $round_off = round($grand_total - $exact_total, 2);
        
        // 5. Update invoice with calculated totals
        $stmtUpdate = $pdo->prepare("
            UPDATE invoices 
            SET subtotal = ?, cgst = ?, sgst = ?, igst = ?, round_off = ?, grand_total = ? 
            WHERE id = ?
        ");
        $stmtUpdate->execute([$subtotal, $total_cgst, $total_sgst, $total_igst, $round_off, $grand_total, $invoice_id]);

        // 6. Auto-post Customer Debit Ledger entry
        $stmtLedger = $pdo->prepare("
            INSERT INTO ledgers (entity_type, entity_id, transaction_date, type, amount, description, created_by) 
            VALUES ('Customer', ?, ?, 'Debit', ?, ?, ?)
        ");
        $stmtLedger->execute([$customer_id, $date, $grand_total, 'Sales Invoice #' . $invoice_number, $createdBy]);
        
        logActivity($pdo, 'CREATE', 'Invoices', "Created Sales Invoice #$invoice_number for Customer '$customer_name' (Total: ₹" . number_format($grand_total, 2) . ")");

        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Invoice created successfully', 
            'invoice_id' => $invoice_id,
            'invoice_number' => $invoice_number
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data payload']);
}
?>
