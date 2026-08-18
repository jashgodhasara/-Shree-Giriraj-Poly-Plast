<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entity_type = trim($_POST['entity_type'] ?? '');
    $entity_id = intval($_POST['entity_id'] ?? 0);
    $transaction_date = $_POST['transaction_date'] ?? date('Y-m-d');
    $type = trim($_POST['type'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $hsn_code = trim($_POST['hsn_code'] ?? '');
    $csm_code = trim($_POST['csm_code'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($entity_type) || empty($type) || empty($amount)) {
        echo json_encode(['success' => false, 'message' => 'Entity, Type, and Amount are required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO ledgers (entity_type, entity_id, transaction_date, type, amount, hsn_code, csm_code, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$entity_type, $entity_id, $transaction_date, $type, $amount, $hsn_code, $csm_code, $description, $createdBy]);

        logActivity($pdo, 'CREATE', 'Ledger', "Added $type transaction of ₹" . number_format($amount, 2) . " for $entity_type ID $entity_id (" . ($description ?: 'No desc') . ")");

        echo json_encode(['success' => true, 'message' => 'Ledger entry saved successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
