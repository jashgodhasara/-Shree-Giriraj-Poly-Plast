<?php
error_reporting(0);
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

header('Content-Type: application/json');

$currentUser = getCurrentUser();
$role = strtolower($currentUser['role'] ?? '');

if ($role !== 'admin' && $role !== 'partner' && $role !== 'owner') {
    echo json_encode(['success' => false, 'message' => 'Access Denied: Only Admin can import data.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$importType = $_POST['import_type'] ?? '';
$mode = $_POST['mode'] ?? 'insert_or_update'; // 'insert_or_update' or 'skip_existing'

if (empty($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid file to import.']);
    exit;
}

$fileTmp = $_FILES['import_file']['tmp_name'];
$fileName = $_FILES['import_file']['name'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

try {
    if ($importType === 'full_sql') {
        if ($fileExt !== 'sql') {
            echo json_encode(['success' => false, 'message' => 'Please upload a valid .sql backup file.']);
            exit;
        }

        $sqlContent = file_get_contents($fileTmp);
        if (empty($sqlContent)) {
            echo json_encode(['success' => false, 'message' => 'The uploaded SQL file is empty.']);
            exit;
        }

        // Execute SQL batch statements
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec($sqlContent);
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

        logActivity($pdo, 'IMPORT', 'Database', "Restored full database from SQL backup file: '$fileName'");

        echo json_encode([
            'success' => true,
            'message' => 'Full database restored successfully from SQL backup!'
        ]);
        exit;

    } elseif ($importType === 'full_json') {
        if ($fileExt !== 'json') {
            echo json_encode(['success' => false, 'message' => 'Please upload a valid .json backup file.']);
            exit;
        }

        $jsonStr = file_get_contents($fileTmp);
        $data = json_decode($jsonStr, true);

        if (!$data || !isset($data['tables'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON backup format.']);
            exit;
        }

        $pdo->beginTransaction();
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

        $totalRestored = 0;
        foreach ($data['tables'] as $table => $rows) {
            $check = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
            if (!$check || empty($rows)) continue;

            $pdo->exec("DELETE FROM `$table`");
            foreach ($rows as $row) {
                $cols = array_map(function($c) { return "`$c`"; }, array_keys($row));
                $placeholders = array_fill(0, count($row), '?');
                $sql = "INSERT INTO `$table` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($row));
                $totalRestored++;
            }
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        $pdo->commit();

        logActivity($pdo, 'IMPORT', 'Database', "Restored database from JSON backup file: '$fileName' ($totalRestored rows)");

        echo json_encode([
            'success' => true,
            'message' => "Full database restored successfully! $totalRestored records imported."
        ]);
        exit;

    } elseif (in_array($importType, ['products', 'materials', 'customers', 'suppliers'])) {
        if (!in_array($fileExt, ['csv', 'txt'])) {
            echo json_encode(['success' => false, 'message' => 'Please upload a valid .csv file.']);
            exit;
        }

        $handle = fopen($fileTmp, 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Could not read CSV file.']);
            exit;
        }

        // Detect and skip UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            echo json_encode(['success' => false, 'message' => 'CSV file is empty or missing headers.']);
            exit;
        }

        // Normalize header keys
        $headerMap = [];
        foreach ($header as $idx => $colName) {
            $cleanKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', $colName)));
            $headerMap[$cleanKey] = $idx;
        }

        $pdo->beginTransaction();
        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $rowNum = 1;

        if ($importType === 'products') {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (empty(array_filter($row))) continue;

                $name = trim($row[$headerMap['product_name'] ?? $headerMap['name'] ?? 0] ?? '');
                if (empty($name)) continue;

                $category = trim($row[$headerMap['category_name'] ?? $headerMap['category'] ?? 1] ?? 'Finished Goods');
                $unit = trim($row[$headerMap['unit'] ?? 2] ?? 'PCS');
                $price = floatval($row[$headerMap['price'] ?? $headerMap['price____'] ?? 3] ?? 0);
                $rate_kg = floatval($row[$headerMap['rate_per_kg'] ?? $headerMap['rate_per_kg____'] ?? 4] ?? 0);
                $weight = floatval($row[$headerMap['weight_per_piece'] ?? $headerMap['weight_per_piece__g_'] ?? 5] ?? 0);
                $hsn = trim($row[$headerMap['hsn_code'] ?? $headerMap['hsn'] ?? 6] ?? '392690');
                $gst_rate = floatval($row[$headerMap['gst_rate'] ?? $headerMap['gst_rate____'] ?? 7] ?? 18);
                $stock = floatval($row[$headerMap['stock_quantity'] ?? $headerMap['stock'] ?? 8] ?? 0);

                // Check existing product by name
                $stmtCheck = $pdo->prepare("SELECT id FROM products WHERE name = ?");
                $stmtCheck->execute([$name]);
                $existing = $stmtCheck->fetch();

                if ($existing) {
                    if ($mode === 'skip_existing') {
                        $skippedCount++;
                        continue;
                    }
                    $stmtUpd = $pdo->prepare("
                        UPDATE products 
                        SET category_name = ?, unit = ?, price = ?, rate_per_kg = ?, weight_per_piece = ?, hsn_code = ?, gst_rate = ?, stock_quantity = ?
                        WHERE id = ?
                    ");
                    $stmtUpd->execute([$category, $unit, $price, $rate_kg, $weight, $hsn, $gst_rate, $stock, $existing['id']]);
                    $updatedCount++;
                } else {
                    $stmtIns = $pdo->prepare("
                        INSERT INTO products (name, category_name, unit, price, rate_per_kg, weight_per_piece, hsn_code, gst_rate, stock_quantity, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmtIns->execute([$name, $category, $unit, $price, $rate_kg, $weight, $hsn, $gst_rate, $stock, $currentUser['id']]);
                    $importedCount++;
                }
            }

        } elseif ($importType === 'materials') {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (empty(array_filter($row))) continue;

                $name = trim($row[$headerMap['material_name'] ?? $headerMap['name'] ?? 0] ?? '');
                if (empty($name)) continue;

                $category = trim($row[$headerMap['category'] ?? $headerMap['category___type'] ?? 1] ?? 'Raw Material');
                $unit = trim($row[$headerMap['unit'] ?? 2] ?? 'KG');
                $rate_kg = floatval($row[$headerMap['rate_per_kg'] ?? $headerMap['rate_per_kg____'] ?? 3] ?? 0);
                $price_unit = floatval($row[$headerMap['price_per_unit'] ?? $headerMap['price_per_unit____'] ?? 4] ?? $rate_kg);
                $hsn = trim($row[$headerMap['hsn_code'] ?? 5] ?? '390110');
                $stock = floatval($row[$headerMap['stock_quantity'] ?? $headerMap['stock'] ?? 6] ?? 0);

                $stmtCheck = $pdo->prepare("SELECT id FROM materials WHERE name = ?");
                $stmtCheck->execute([$name]);
                $existing = $stmtCheck->fetch();

                if ($existing) {
                    if ($mode === 'skip_existing') {
                        $skippedCount++;
                        continue;
                    }
                    $stmtUpd = $pdo->prepare("UPDATE materials SET category = ?, unit = ?, rate_per_kg = ?, price_per_unit = ?, hsn_code = ?, stock_quantity = ? WHERE id = ?");
                    $stmtUpd->execute([$category, $unit, $rate_kg, $price_unit, $hsn, $stock, $existing['id']]);
                    $updatedCount++;
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO materials (name, category, unit, rate_per_kg, price_per_unit, hsn_code, stock_quantity, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtIns->execute([$name, $category, $unit, $rate_kg, $price_unit, $hsn, $stock, $currentUser['id']]);
                    $importedCount++;
                }
            }

        } elseif ($importType === 'customers') {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (empty(array_filter($row))) continue;

                $name = trim($row[$headerMap['customer_name'] ?? $headerMap['name'] ?? 0] ?? '');
                if (empty($name)) continue;

                $phone = trim($row[$headerMap['phone'] ?? 1] ?? '');
                $email = trim($row[$headerMap['email'] ?? 2] ?? '');
                $state = trim($row[$headerMap['state'] ?? 3] ?? 'Gujarat');
                $gstin = trim($row[$headerMap['gstin'] ?? 4] ?? '');
                $address = trim($row[$headerMap['address'] ?? 5] ?? '');

                $stmtCheck = $pdo->prepare("SELECT id FROM customers WHERE name = ? OR (gstin != '' AND gstin = ?)");
                $stmtCheck->execute([$name, $gstin]);
                $existing = $stmtCheck->fetch();

                if ($existing) {
                    if ($mode === 'skip_existing') {
                        $skippedCount++;
                        continue;
                    }
                    $stmtUpd = $pdo->prepare("UPDATE customers SET phone = ?, email = ?, state = ?, gstin = ?, address = ? WHERE id = ?");
                    $stmtUpd->execute([$phone, $email, $state, $gstin, $address, $existing['id']]);
                    $updatedCount++;
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO customers (name, phone, email, state, gstin, address, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmtIns->execute([$name, $phone, $email, $state, $gstin, $address, $currentUser['id']]);
                    $importedCount++;
                }
            }

        } elseif ($importType === 'suppliers') {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (empty(array_filter($row))) continue;

                $name = trim($row[$headerMap['supplier_name'] ?? $headerMap['name'] ?? 0] ?? '');
                if (empty($name)) continue;

                $phone = trim($row[$headerMap['phone'] ?? 1] ?? '');
                $email = trim($row[$headerMap['email'] ?? 2] ?? '');
                $state = trim($row[$headerMap['state'] ?? 3] ?? 'Gujarat');
                $gstin = trim($row[$headerMap['gstin'] ?? 4] ?? '');
                $address = trim($row[$headerMap['address'] ?? 5] ?? '');

                $stmtCheck = $pdo->prepare("SELECT id FROM suppliers WHERE name = ? OR (gstin != '' AND gstin = ?)");
                $stmtCheck->execute([$name, $gstin]);
                $existing = $stmtCheck->fetch();

                if ($existing) {
                    if ($mode === 'skip_existing') {
                        $skippedCount++;
                        continue;
                    }
                    $stmtUpd = $pdo->prepare("UPDATE suppliers SET phone = ?, email = ?, state = ?, gstin = ?, address = ? WHERE id = ?");
                    $stmtUpd->execute([$phone, $email, $state, $gstin, $address, $existing['id']]);
                    $updatedCount++;
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO suppliers (name, phone, email, state, gstin, address, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmtIns->execute([$name, $phone, $email, $state, $gstin, $address, $currentUser['id']]);
                    $importedCount++;
                }
            }
        }

        fclose($handle);
        $pdo->commit();

        logActivity($pdo, 'IMPORT', ucfirst($importType), "Imported CSV data: $importedCount inserted, $updatedCount updated, $skippedCount skipped from '$fileName'");

        echo json_encode([
            'success' => true,
            'message' => "Import complete! $importedCount new added, $updatedCount updated, $skippedCount skipped.",
            'details' => [
                'inserted' => $importedCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount
            ]
        ]);
        exit;

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid import type selected.']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Import Error: ' . $e->getMessage()]);
}
