<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gstin = trim($_POST['gstin'] ?? '');
    $state = trim($_POST['state'] ?? 'Gujarat');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Supplier name is required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE suppliers SET name = ?, phone = ?, email = ?, gstin = ?, state = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $email, $gstin, $state, $address, $id]);

            logActivity($pdo, 'UPDATE', 'Suppliers', "Updated Supplier #$id: '$name' (State: $state, Phone: " . ($phone ?: 'N/A') . ")");

            echo json_encode(['success' => true, 'message' => 'Supplier updated successfully']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO suppliers (name, phone, email, gstin, state, address, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $email, $gstin, $state, $address, $createdBy]);
            $newId = $pdo->lastInsertId();

            logActivity($pdo, 'CREATE', 'Suppliers', "Added Supplier: '$name' (State: $state, Phone: " . ($phone ?: 'N/A') . ")");

            echo json_encode(['success' => true, 'message' => 'Supplier added successfully', 'id' => $newId]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
