<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $vehicle_no = trim($_POST['vehicle_no'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Transporter name is required']);
        exit;
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO transporters (name, vehicle_no, phone, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $vehicle_no, $phone, $createdBy]);

        logActivity($pdo, 'CREATE', 'Transporters', "Added Transporter: '$name' (Vehicle: " . ($vehicle_no ?: 'N/A') . ")");

        echo json_encode(['success' => true, 'message' => 'Transporter added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
