<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $type = trim($_POST['type'] ?? 'Raw Material');
    $name = trim($_POST['name'] ?? '');
    $unit = trim($_POST['unit'] ?? 'Kg');
    $rate_per_kg = floatval($_POST['rate_per_kg'] ?? 0);
    $price_per_unit = floatval($_POST['price_per_unit'] ?? $rate_per_kg);
    $hsn_code = trim($_POST['hsn_code'] ?? '390110');
    $grade_variation = trim($_POST['grade_variation'] ?? '');
    $temp = trim($_POST['temp'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $stock_quantity = isset($_POST['stock_quantity']) ? floatval($_POST['stock_quantity']) : 0;

    if (empty($name) || empty($type)) {
        echo json_encode(['success' => false, 'message' => 'Material name and type are required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE materials 
                SET type = ?, name = ?, unit = ?, rate_per_kg = ?, price_per_unit = ?, hsn_code = ?, grade_variation = ?, temp = ?, size = ?, stock_quantity = ? 
                WHERE id = ?
            ");
            $stmt->execute([$type, $name, $unit, $rate_per_kg, $price_per_unit, $hsn_code, $grade_variation, $temp, $size, $stock_quantity, $id]);

            logActivity($pdo, 'UPDATE', 'Materials', "Updated $type #$id: '$name' (Rate/KG: ₹" . number_format($rate_per_kg, 2) . ", Stock: $stock_quantity $unit)");

            echo json_encode(['success' => true, 'message' => 'Material updated successfully']);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO materials (type, name, unit, rate_per_kg, price_per_unit, hsn_code, grade_variation, temp, size, stock_quantity, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$type, $name, $unit, $rate_per_kg, $price_per_unit, $hsn_code, $grade_variation, $temp, $size, $stock_quantity, $createdBy]);
            $newId = $pdo->lastInsertId();

            logActivity($pdo, 'CREATE', 'Materials', "Added $type: '$name' (Rate/KG: ₹" . number_format($rate_per_kg, 2) . ", Initial Stock: $stock_quantity $unit)");

            echo json_encode(['success' => true, 'message' => 'Material added successfully', 'material_id' => $newId]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
