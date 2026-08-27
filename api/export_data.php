<?php
require_once '../config/db.php';
require_once '../config/auth.php';
requireAuth();

$currentUser = getCurrentUser();
$role = strtolower($currentUser['role'] ?? '');

// Ensure user is authorized (Admin or Partner)
if ($role !== 'admin' && $role !== 'partner' && $role !== 'owner') {
    die("Access Denied: Only Admin can export data.");
}

$type = $_GET['type'] ?? 'products';
$filename = "export_" . $type . "_" . date('Y-m-d_H-i-s');

function outputCSV($headers, $rows, $filename) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Add UTF-8 BOM for Excel compatibility
    echo "\xEF\xBB\xBF";
    
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

if ($type === 'products') {
    $stmt = $pdo->query("SELECT id, name, category_name, unit, price, rate_per_kg, weight_per_piece, hsn_code, gst_rate, stock_quantity, created_at FROM products ORDER BY id ASC");
    $data = $stmt->fetchAll(PDO::FETCH_NUM);
    $headers = ['ID', 'Product Name', 'Category', 'Unit', 'Price (₹)', 'Rate Per KG (₹)', 'Weight Per Piece (g)', 'HSN Code', 'GST Rate (%)', 'Stock Quantity', 'Created At'];
    outputCSV($headers, $data, "products_export_" . date('Ymd'));

} elseif ($type === 'materials') {
    $stmt = $pdo->query("SELECT id, name, category, unit, rate_per_kg, price_per_unit, hsn_code, stock_quantity, created_at FROM materials ORDER BY id ASC");
    $data = $stmt->fetchAll(PDO::FETCH_NUM);
    $headers = ['ID', 'Material Name', 'Category / Type', 'Unit', 'Rate Per KG (₹)', 'Price Per Unit (₹)', 'HSN Code', 'Stock Quantity', 'Created At'];
    outputCSV($headers, $data, "materials_export_" . date('Ymd'));

} elseif ($type === 'customers') {
    $stmt = $pdo->query("SELECT id, name, phone, email, state, gstin, address, created_at FROM customers ORDER BY id ASC");
    $data = $stmt->fetchAll(PDO::FETCH_NUM);
    $headers = ['ID', 'Customer Name', 'Phone', 'Email', 'State', 'GSTIN', 'Address', 'Created At'];
    outputCSV($headers, $data, "customers_export_" . date('Ymd'));

} elseif ($type === 'suppliers') {
    $stmt = $pdo->query("SELECT id, name, phone, email, state, gstin, address, created_at FROM suppliers ORDER BY id ASC");
    $data = $stmt->fetchAll(PDO::FETCH_NUM);
    $headers = ['ID', 'Supplier Name', 'Phone', 'Email', 'State', 'GSTIN', 'Address', 'Created At'];
    outputCSV($headers, $data, "suppliers_export_" . date('Ymd'));

} elseif ($type === 'invoices') {
    $stmt = $pdo->query("
        SELECT i.id, i.invoice_number, i.invoice_date, c.name as customer_name, c.state as customer_state, 
               i.subtotal, i.cgst, i.sgst, i.igst, i.round_off, i.grand_total, i.challan_number, i.po_number, i.created_at
        FROM invoices i
        LEFT JOIN customers c ON i.customer_id = c.id
        ORDER BY i.id DESC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_NUM);
    $headers = ['ID', 'Invoice Number', 'Date', 'Customer Name', 'State', 'Taxable Subtotal (₹)', 'CGST (₹)', 'SGST (₹)', 'IGST (₹)', 'Round Off (₹)', 'Grand Total (₹)', 'Challan No', 'PO No', 'Created At'];
    outputCSV($headers, $data, "sales_invoices_export_" . date('Ymd'));

} elseif ($type === 'purchases') {
    $stmt = $pdo->query("
        SELECT p.id, p.purchase_number, p.purchase_date, s.name as supplier_name, s.state as supplier_state,
               p.subtotal, p.cgst, p.sgst, p.igst, p.round_off, p.grand_total, p.bill_number, p.created_at
        FROM purchases p
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        ORDER BY p.id DESC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_NUM);
    $headers = ['ID', 'Purchase Number', 'Date', 'Supplier Name', 'State', 'Taxable Subtotal (₹)', 'CGST (₹)', 'SGST (₹)', 'IGST (₹)', 'Round Off (₹)', 'Grand Total (₹)', 'Supplier Bill No', 'Created At'];
    outputCSV($headers, $data, "purchase_bills_export_" . date('Ymd'));

} elseif ($type === 'ledgers') {
    $stmt = $pdo->query("SELECT id, entity_type, entity_id, transaction_date, type, amount, description, created_at FROM ledgers ORDER BY id DESC");
    $data = $stmt->fetchAll(PDO::FETCH_NUM);
    $headers = ['ID', 'Entity Type', 'Entity ID', 'Date', 'Type (Debit/Credit)', 'Amount (₹)', 'Description', 'Created At'];
    outputCSV($headers, $data, "ledgers_export_" . date('Ymd'));

} elseif ($type === 'sample_products') {
    $headers = ['name', 'category_name', 'unit', 'price', 'rate_per_kg', 'weight_per_piece', 'hsn_code', 'gst_rate', 'stock_quantity'];
    $samples = [
        ['PVC Rigid Pipe 1 Inch', 'Meter Category', 'MTR', '150.00', '95.00', '1200.00', '391721', '18', '500'],
        ['HDPE Granules Bottle Cap', 'Finished Goods', 'PCS', '4.50', '88.00', '25.00', '392350', '18', '10000'],
        ['Polypropylene Container 500ml', 'Moulded Products', 'PCS', '12.00', '92.00', '65.00', '392690', '18', '2500']
    ];
    outputCSV($headers, $samples, "sample_products_template");

} elseif ($type === 'sample_materials') {
    $headers = ['name', 'category', 'unit', 'rate_per_kg', 'price_per_unit', 'hsn_code', 'stock_quantity'];
    $samples = [
        ['Virgin PP Granules High Flow', 'Raw Material', 'KG', '92.00', '92.00', '390110', '2500'],
        ['White Masterbatch 40%', 'Additive', 'KG', '140.00', '140.00', '320611', '350'],
        ['HDPE Blow Moulding Grade', 'Raw Material', 'KG', '88.00', '88.00', '390120', '4000']
    ];
    outputCSV($headers, $samples, "sample_materials_template");

} elseif ($type === 'sample_customers') {
    $headers = ['name', 'phone', 'email', 'state', 'gstin', 'address'];
    $samples = [
        ['Apex Plastics India Ltd', '9876543210', 'apex@example.com', 'Gujarat', '24AAACA1234A1Z5', 'Plot 45, GIDC Vatva, Ahmedabad, Gujarat 382445'],
        ['Maharashtra Polymer Traders', '9822114455', 'sales@mahapolymers.com', 'Maharashtra', '27AAACB5678B1Z6', 'Sector 18, Vashi, Navi Mumbai, MH 400703']
    ];
    outputCSV($headers, $samples, "sample_customers_template");

} elseif ($type === 'sample_suppliers') {
    $headers = ['name', 'phone', 'email', 'state', 'gstin', 'address'];
    $samples = [
        ['Reliance Petrochemicals Ltd', '9988776655', 'reliance_order@example.com', 'Gujarat', '24AAACR1234R1Z1', 'Hazira Complex, Surat, Gujarat'],
        ['Indian Masterbatch Corp', '9123456780', 'orders@imc.com', 'Gujarat', '24AAACI9876I1Z2', 'GIDC Naroda, Ahmedabad, Gujarat']
    ];
    outputCSV($headers, $samples, "sample_suppliers_template");

} elseif ($type === 'full_sql') {
    // Generate full database SQL dump
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="shreegiriraj_full_backup_' . date('Ymd_His') . '.sql"');
    
    echo "-- ========================================================\n";
    echo "-- Shree Giriraj Poly Plast ERP - Full Database Backup\n";
    echo "-- Generated At: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Database: shreegiriraj_billing\n";
    echo "-- ========================================================\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

    $tables = ['users', 'categories', 'units', 'products', 'materials', 'customers', 'suppliers', 'transporters', 'invoices', 'invoice_items', 'purchases', 'purchase_items', 'ledgers', 'production_logs', 'activity_logs'];

    foreach ($tables as $table) {
        $check = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        if (!$check) continue;

        $createRow = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        echo "-- Table structure for `$table`\n";
        echo "DROP TABLE IF EXISTS `$table`;\n";
        echo $createRow[1] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            echo "-- Dumping data for `$table`\n";
            foreach ($rows as $row) {
                $cols = array_map(function($c) { return "`$c`"; }, array_keys($row));
                $vals = array_map(function($v) use ($pdo) {
                    if ($v === null) return 'NULL';
                    return $pdo->quote($v);
                }, array_values($row));
                echo "INSERT INTO `$table` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
            }
            echo "\n";
        }
    }

    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    echo "-- Backup completed successfully.\n";
    exit;

} elseif ($type === 'full_json') {
    // Generate full database JSON backup
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="shreegiriraj_full_backup_' . date('Ymd_His') . '.json"');

    $backup = [
        'system' => 'Shree Giriraj Poly Plast ERP',
        'version' => '2.5.0',
        'generated_at' => date('c'),
        'tables' => []
    ];

    $tables = ['users', 'categories', 'units', 'products', 'materials', 'customers', 'suppliers', 'transporters', 'invoices', 'invoice_items', 'purchases', 'purchase_items', 'ledgers', 'production_logs', 'activity_logs'];

    foreach ($tables as $table) {
        $check = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        if (!$check) continue;
        $backup['tables'][$table] = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;

} else {
    die("Invalid export type specified.");
}
