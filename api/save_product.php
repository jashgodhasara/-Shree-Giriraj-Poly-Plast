<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category_name = trim($_POST['category_name'] ?? 'Finished Goods');
    $unit = trim($_POST['unit'] ?? 'PCS');
    $price = floatval($_POST['price'] ?? 0);
    $rate_per_kg = floatval($_POST['rate_per_kg'] ?? 0);
    $weight_per_piece = floatval($_POST['weight_per_piece'] ?? 0);
    $hsn_code = trim($_POST['hsn_code'] ?? '392690');
    $gst_rate = floatval($_POST['gst_rate'] ?? 18);
    $stock_quantity = isset($_POST['stock_quantity']) ? floatval($_POST['stock_quantity']) : null;
    $description = trim($_POST['description'] ?? '');
    $imagePath = null;

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Product name is required']);
        exit;
    }

    // Auto-calculate price if rate_per_kg and weight_per_piece provided and price is 0
    if ($price <= 0 && $rate_per_kg > 0 && $weight_per_piece > 0) {
        $price = round($rate_per_kg * ($weight_per_piece / 1000), 2);
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

        if ($id > 0) {
            // Update existing product
            $sql = "UPDATE products SET name = ?, category_name = ?, unit = ?, price = ?, rate_per_kg = ?, weight_per_piece = ?, hsn_code = ?, gst_rate = ?, description = ?";
            $params = [$name, $category_name, $unit, $price, $rate_per_kg, $weight_per_piece, $hsn_code, $gst_rate, $description];
            
            if ($imagePath) {
                $sql .= ", image = ?";
                $params[] = $imagePath;
            }
            if ($stock_quantity !== null) {
                $sql .= ", stock_quantity = ?";
                $params[] = $stock_quantity;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            logActivity($pdo, 'UPDATE', 'Products', "Updated Product #$id: '$name' (Price: ₹" . number_format($price, 2) . ", Rate/KG: ₹" . number_format($rate_per_kg, 2) . ", Unit: $unit)");

            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            // Insert new product
            $stock = $stock_quantity !== null ? $stock_quantity : 0;
            $stmt = $pdo->prepare("
                INSERT INTO products (name, category_name, unit, price, rate_per_kg, weight_per_piece, hsn_code, gst_rate, stock_quantity, description, image, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $category_name, $unit, $price, $rate_per_kg, $weight_per_piece, $hsn_code, $gst_rate, $stock, $description, $imagePath, $createdBy]);
            $newId = $pdo->lastInsertId();

            logActivity($pdo, 'CREATE', 'Products', "Added Product: '$name' (Price: ₹" . number_format($price, 2) . ", Rate/KG: ₹" . number_format($rate_per_kg, 2) . ", Unit: $unit, GST: $gst_rate%)");

            echo json_encode(['success' => true, 'message' => 'Product added successfully', 'product_id' => $newId]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
