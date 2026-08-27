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
    $address = trim($_POST['address'] ?? '');
    $gstin = trim($_POST['gstin'] ?? '');
    $state = trim($_POST['state'] ?? 'Gujarat');

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Customer name is required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, email = ?, address = ?, gstin = ?, state = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $email, $address, $gstin, $state, $id]);

            logActivity($pdo, 'UPDATE', 'Customers', "Updated customer #$id: '$name' (Phone: " . ($phone ?: 'N/A') . ", State: $state)");

            echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, address, gstin, state, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $email, $address, $gstin, $state, $createdBy]);
            $newId = $pdo->lastInsertId();

            logActivity($pdo, 'CREATE', 'Customers', "Added new customer: '$name' (Phone: " . ($phone ?: 'N/A') . ", State: $state)");

            echo json_encode(['success' => true, 'message' => 'Customer added successfully', 'id' => $newId]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
