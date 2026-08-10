<?php
require_once '../config/db.php';
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $vehicle_no = $_POST['vehicle_no'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Transporter name is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO transporters (name, vehicle_no, phone) VALUES (?, ?, ?)");
        $stmt->execute([$name, $vehicle_no, $phone]);
        echo json_encode(['success' => true, 'message' => 'Transporter added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
