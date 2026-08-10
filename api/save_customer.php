<?php
error_reporting(0);
require_once '../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $gstin = $_POST['gstin'] ?? '';
    $state = $_POST['state'] ?? '';

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Customer name is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, address, gstin, state) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $address, $gstin, $state]);
        echo json_encode(['success' => true, 'message' => 'Customer added successfully', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
