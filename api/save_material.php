<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $unit = trim($_POST['unit'] ?? 'Kg');
    $grade_variation = trim($_POST['grade_variation'] ?? '');
    $temp = trim($_POST['temp'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $stock_quantity = floatval($_POST['stock_quantity'] ?? 0);

    if (empty($name) || empty($type)) {
        echo json_encode(['success' => false, 'message' => 'Material name and type are required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO materials (type, name, unit, grade_variation, temp, size, stock_quantity, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type, $name, $unit, $grade_variation, $temp, $size, $stock_quantity, $createdBy]);

        logActivity($pdo, 'CREATE', 'Materials', "Added $type: '$name' (Initial Stock: $stock_quantity $unit)");

        echo json_encode(['success' => true, 'message' => 'Material added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
