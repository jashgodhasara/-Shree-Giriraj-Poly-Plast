<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d');
    $raw_material_id = intval($_POST['raw_material_id'] ?? 0);
    $raw_material_used_kg = floatval($_POST['raw_material_used_kg'] ?? 0);
    $additive_id = !empty($_POST['additive_id']) ? intval($_POST['additive_id']) : null;
    $additive_used_kg = floatval($_POST['additive_used_kg'] ?? 0);
    $final_product_id = intval($_POST['final_product_id'] ?? 0);
    $final_product_qty_pcs = intval($_POST['final_product_qty_pcs'] ?? 0);
    $salvage_qty_kg = floatval($_POST['salvage_qty_kg'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    // Check raw material stock
    $stmtStock = $pdo->prepare("SELECT name, stock_quantity, unit FROM materials WHERE id = ?");
    $stmtStock->execute([$raw_material_id]);
    $mat = $stmtStock->fetch();

    if ($mat && $mat['stock_quantity'] < $raw_material_used_kg) {
        echo json_encode([
            'success' => false,
            'message' => "Insufficient stock for '{$mat['name']}'. Available: {$mat['stock_quantity']} {$mat['unit']}, Requested: {$raw_material_used_kg} {$mat['unit']}"
        ]);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        $pdo->beginTransaction();

        // 1. Insert Production Log
        $stmt = $pdo->prepare("INSERT INTO production_logs (date, raw_material_id, raw_material_used_kg, additive_id, additive_used_kg, final_product_id, final_product_qty_pcs, salvage_qty_kg, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$date, $raw_material_id, $raw_material_used_kg, $additive_id, $additive_used_kg, $final_product_id, $final_product_qty_pcs, $salvage_qty_kg, $notes, $createdBy]);

        // 2. Deduct Raw Material Stock (Kg)
        $pdo->prepare("UPDATE materials SET stock_quantity = stock_quantity - ? WHERE id = ?")->execute([$raw_material_used_kg, $raw_material_id]);

        // 3. Deduct Additive Stock (Kg)
        if ($additive_id && $additive_used_kg > 0) {
            $pdo->prepare("UPDATE materials SET stock_quantity = stock_quantity - ? WHERE id = ?")->execute([$additive_used_kg, $additive_id]);
        }

        // 4. Increase Final Product Stock (Pcs)
        $pdo->prepare("UPDATE materials SET stock_quantity = stock_quantity + ? WHERE id = ?")->execute([$final_product_qty_pcs, $final_product_id]);

        logActivity($pdo, 'CREATE', 'Production', "Logged Production Entry: $raw_material_used_kg Kg Raw Material -> $final_product_qty_pcs Pcs Final Product");

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Production log saved & inventory updated successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
