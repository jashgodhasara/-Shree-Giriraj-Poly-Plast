<?php
require_once '../config/db.php';
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $gstin = $_POST['gstin'] ?? '';
    $address = $_POST['address'] ?? '';

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Supplier name is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, phone, email, gstin, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $gstin, $address]);
        echo json_encode(['success' => true, 'message' => 'Supplier added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
