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
    $gstin = trim($_POST['gstin'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Supplier name is required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO suppliers (name, phone, email, gstin, address, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $gstin, $address, $createdBy]);

        logActivity($pdo, 'CREATE', 'Suppliers', "Added Supplier: '$name' (Phone: " . ($phone ?: 'N/A') . ")");

        echo json_encode(['success' => true, 'message' => 'Supplier added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
