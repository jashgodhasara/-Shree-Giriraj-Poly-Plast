<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gstin = trim($_POST['gstin'] ?? '');
    $state = trim($_POST['state'] ?? '');

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Customer name is required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, address, gstin, state, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $address, $gstin, $state, $createdBy]);
        $newId = $pdo->lastInsertId();

        logActivity($pdo, 'CREATE', 'Customers', "Added new customer: '$name' (Phone: " . ($phone ?: 'N/A') . ", State: " . ($state ?: 'N/A') . ")");

        echo json_encode(['success' => true, 'message' => 'Customer added successfully', 'id' => $newId]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
