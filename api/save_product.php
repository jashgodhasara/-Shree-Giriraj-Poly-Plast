<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $hsn_code = trim($_POST['hsn_code'] ?? '');
    $gst_rate = floatval($_POST['gst_rate'] ?? 18);
    $imagePath = null;

    if (empty($name) || empty($price)) {
        echo json_encode(['success' => false, 'message' => 'Product name and price are required']);
        exit;
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = time() . '_' . uniqid() . '.' . $ext;
            $targetDir = __DIR__ . '/../uploads/products/';
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $filename)) {
                $imagePath = 'uploads/products/' . $filename;
            }
        }
    }

    try {
        $currentUser = getCurrentUser();
        $createdBy = $currentUser['id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO products (name, description, image, price, hsn_code, gst_rate, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $imagePath, $price, $hsn_code, $gst_rate, $createdBy]);

        logActivity($pdo, 'CREATE', 'Products', "Added Product: '$name' (Price: ₹" . number_format($price, 2) . ", GST: $gst_rate%)");

        echo json_encode(['success' => true, 'message' => 'Product added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
