<?php
require_once '../config/db.php';
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $name = $_POST['name'] ?? '';
    $unit = $_POST['unit'] ?? 'Kg';
    $grade_variation = $_POST['grade_variation'] ?? '';
    $temp = $_POST['temp'] ?? '';
    $size = $_POST['size'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? 0;

    if (empty($name) || empty($type)) {
        echo json_encode(['success' => false, 'message' => 'Material name and type are required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO materials (type, name, unit, grade_variation, temp, size, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type, $name, $unit, $grade_variation, $temp, $size, $stock_quantity]);
        echo json_encode(['success' => true, 'message' => 'Material added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
