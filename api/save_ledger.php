<?php
require_once '../config/db.php';
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entity_type = $_POST['entity_type'] ?? '';
    $entity_id = $_POST['entity_id'] ?? 0;
    $transaction_date = $_POST['transaction_date'] ?? date('Y-m-d');
    $type = $_POST['type'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $hsn_code = $_POST['hsn_code'] ?? '';
    $csm_code = $_POST['csm_code'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($entity_type) || empty($type) || empty($amount)) {
        echo json_encode(['success' => false, 'message' => 'Entity, Type, and Amount are required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO ledgers (entity_type, entity_id, transaction_date, type, amount, hsn_code, csm_code, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$entity_type, $entity_id, $transaction_date, $type, $amount, $hsn_code, $csm_code, $description]);
        echo json_encode(['success' => true, 'message' => 'Ledger entry saved successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
