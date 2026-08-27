<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $supplier_id = intval($data['supplier_id'] ?? 0);
    $purchase_date = $data['date'] ?? date('Y-m-d');
    $bill_number = trim($data['bill_number'] ?? '');
    $payment_terms = trim($data['payment_terms'] ?? 'Direct Purchase');
    $vehicle_number = trim($data['vehicle_number'] ?? '');
    $notes = trim($data['notes'] ?? '');
    $items = $data['items'] ?? [];

    if (empty($supplier_id) || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Supplier and at least one item are required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        $pdo->beginTransaction();

        // 1. Generate Purchase Number (e.g. PUR-YYYYMM-XXXX)
        $prefix = 'PUR-' . date('Ym') . '-';
        $stmtMax = $pdo->prepare("SELECT purchase_number FROM purchases WHERE purchase_number LIKE ? ORDER BY id DESC LIMIT 1");
        $stmtMax->execute([$prefix . '%']);
        $lastRow = $stmtMax->fetch();

        if ($lastRow) {
            $lastNum = (int) substr($lastRow['purchase_number'], strlen($prefix));
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        $purchase_number = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        // Safety check uniqueness
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE purchase_number = ?");
        while (true) {
            $stmtCheck->execute([$purchase_number]);
            if ($stmtCheck->fetchColumn() == 0) {
                break;
            }
            $nextNum++;
            $purchase_number = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        // 2. Fetch Supplier details to determine GST Regime
        $stmtSupp = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmtSupp->execute([$supplier_id]);
        $supplier = $stmtSupp->fetch();

        if (!$supplier) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Supplier not found']);
            exit;
        }

        $supplier_name = $supplier['name'];
        $supplier_state = strtolower(trim($supplier['state'] ?? 'gujarat'));
        $is_igst = (!empty($supplier_state) && $supplier_state !== 'gujarat' && $supplier_state !== '24');

        $subtotal = 0;
        $total_cgst = 0;
        $total_sgst = 0;
        $total_igst = 0;

        // 3. Insert initial purchase record
        $stmtPurch = $pdo->prepare("
            INSERT INTO purchases (purchase_number, bill_number, supplier_id, purchase_date, subtotal, cgst, sgst, igst, round_off, grand_total, payment_terms, vehicle_number, notes, created_by)
            VALUES (?, ?, ?, ?, 0, 0, 0, 0, 0, 0, ?, ?, ?, ?)
        ");
        $stmtPurch->execute([$purchase_number, $bill_number, $supplier_id, $purchase_date, $payment_terms, $vehicle_number, $notes, $createdBy]);
        $purchase_id = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("
            INSERT INTO purchase_items (purchase_id, item_type, item_id, item_name, hsn_code, unit, quantity, rate_per_kg, unit_price, gst_rate, taxable_amount, total_price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // 4. Process line items and stock increment
        foreach ($items as $item) {
            $item_type = $item['type'] ?? 'material'; // 'material' or 'product'
            $item_id = intval($item['id'] ?? 0);
            $qty = floatval($item['quantity'] ?? 0);
            $rate_per_kg = floatval($item['rate_per_kg'] ?? 0);
            $unit_price = floatval($item['unit_price'] ?? 0);
            $gst_rate = floatval($item['gst_rate'] ?? 18);
            $unit = trim($item['unit'] ?? 'KG');
            $hsn_code = trim($item['hsn_code'] ?? '');

            if ($unit_price <= 0 && $rate_per_kg > 0) {
                $unit_price = $rate_per_kg;
            }

            $line_taxable = round($qty * $unit_price, 2);
            $gst_amount = round(($line_taxable * $gst_rate) / 100, 2);
            $line_total = $line_taxable + $gst_amount;

            $item_name = '';
            if ($item_type === 'product') {
                $pStmt = $pdo->prepare("SELECT name, hsn_code FROM products WHERE id = ?");
                $pStmt->execute([$item_id]);
                $pRow = $pStmt->fetch();
                $item_name = $pRow['name'] ?? 'Product #' . $item_id;
                if (empty($hsn_code)) $hsn_code = $pRow['hsn_code'] ?? '392690';

                // Increment Product Stock
                $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?")->execute([$qty, $item_id]);
            } else {
                $mStmt = $pdo->prepare("SELECT name, hsn_code FROM materials WHERE id = ?");
                $mStmt->execute([$item_id]);
                $mRow = $mStmt->fetch();
                $item_name = $mRow['name'] ?? 'Material #' . $item_id;
                if (empty($hsn_code)) $hsn_code = $mRow['hsn_code'] ?? '390110';

                // Increment Material Stock
                $pdo->prepare("UPDATE materials SET stock_quantity = stock_quantity + ? WHERE id = ?")->execute([$qty, $item_id]);
            }

            $stmtItem->execute([
                $purchase_id, $item_type, $item_id, $item_name, $hsn_code, $unit, 
                $qty, $rate_per_kg, $unit_price, $gst_rate, $line_taxable, $line_total
            ]);

            $subtotal += $line_taxable;

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

        // 5. Update purchase header with totals
        $stmtUpd = $pdo->prepare("
            UPDATE purchases 
            SET subtotal = ?, cgst = ?, sgst = ?, igst = ?, round_off = ?, grand_total = ? 
            WHERE id = ?
        ");
        $stmtUpd->execute([$subtotal, $total_cgst, $total_sgst, $total_igst, $round_off, $grand_total, $purchase_id]);

        // 6. Post Supplier Credit in Ledgers
        $desc = "Purchase Bill #" . $purchase_number . ($bill_number ? " (Supplier Inv: $bill_number)" : "");
        $stmtLedger = $pdo->prepare("
            INSERT INTO ledgers (entity_type, entity_id, transaction_date, type, amount, description, created_by)
            VALUES ('Supplier', ?, ?, 'Credit', ?, ?, ?)
        ");
        $stmtLedger->execute([$supplier_id, $purchase_date, $grand_total, $desc, $createdBy]);

        // 7. Activity Log
        logActivity($pdo, 'CREATE', 'Purchases', "Created Direct Purchase Bill #$purchase_number from '$supplier_name' (Total: ₹" . number_format($grand_total, 2) . ")");

        $pdo->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Purchase Bill recorded and stock updated successfully!', 
            'purchase_id' => $purchase_id
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data payload']);
}
?>
