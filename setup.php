<?php
$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'shreegiriraj_billing';

function executeSqlStatements($pdo, $sqlContent) {
    $lines = explode("\n", $sqlContent);
    $cleanSql = '';
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (substr($trimmed, 0, 2) === '--' || substr($trimmed, 0, 2) === '/*') {
            continue;
        }
        $cleanSql .= $line . "\n";
    }
    
    $statements = array_filter(array_map('trim', explode(';', $cleanSql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                // Ignore table exists error or duplicate column error
            }
        }
    }
}

try {
    // Connect to MySQL server first
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color:green;'>Database '$dbname' created or ready.</p>";

    // Connect to database
    $pdo->exec("USE `$dbname`");

    // 1. Ensure users table exists with requisite fields
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NULL,
            email VARCHAR(255) NULL,
            password_hash VARCHAR(255) NULL,
            password VARCHAR(255) NULL,
            full_name VARCHAR(100) NULL,
            name VARCHAR(255) NULL,
            role VARCHAR(20) DEFAULT 'partner',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Ensure missing columns exist in users table
    $userCols = [
        'username' => "VARCHAR(50) NULL",
        'password_hash' => "VARCHAR(255) NULL",
        'full_name' => "VARCHAR(100) NULL",
        'role' => "VARCHAR(20) DEFAULT 'partner'",
        'status' => "ENUM('active', 'inactive') DEFAULT 'active'"
    ];

    foreach ($userCols as $colName => $colDef) {
        $c = $pdo->query("SHOW COLUMNS FROM users LIKE '$colName'")->fetchAll();
        if (empty($c)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN `$colName` $colDef");
        }
    }

    // 2. Create activity_logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NULL,
            username VARCHAR(50) NULL,
            full_name VARCHAR(100) NULL,
            action_type ENUM('LOGIN', 'LOGOUT', 'CREATE', 'UPDATE', 'DELETE') NOT NULL,
            module VARCHAR(50) NOT NULL,
            details TEXT NOT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Execute database.sql
    if (file_exists('database.sql')) {
        executeSqlStatements($pdo, file_get_contents('database.sql'));
    }
    
    // 4. Execute erp_upgrade.sql
    if (file_exists('erp_upgrade.sql')) {
        executeSqlStatements($pdo, file_get_contents('erp_upgrade.sql'));
    }
    
    // 5. Migrations: Add created_by column if missing
    $tables = ['customers', 'products', 'invoices', 'suppliers', 'materials', 'production_logs', 'transporters', 'ledgers'];
    foreach ($tables as $t) {
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM `$t` LIKE 'created_by'")->fetchAll();
            if (empty($cols)) {
                $pdo->exec("ALTER TABLE `$t` ADD COLUMN `created_by` BIGINT(20) UNSIGNED NULL");
                echo "<p style='color:blue;'>Added created_by column to table '$t'.</p>";
            }
        } catch (Exception $ex) {
            // Ignore
        }
    }

    // 5.1 Create purchases and purchase_items tables for direct purchase entry
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS purchases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            purchase_number VARCHAR(50) UNIQUE NOT NULL,
            bill_number VARCHAR(50) NULL,
            supplier_id INT NOT NULL,
            purchase_date DATE NOT NULL,
            subtotal DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            cgst DECIMAL(12, 2) DEFAULT 0.00,
            sgst DECIMAL(12, 2) DEFAULT 0.00,
            igst DECIMAL(12, 2) DEFAULT 0.00,
            round_off DECIMAL(10, 2) DEFAULT 0.00,
            grand_total DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            paid_amount DECIMAL(12, 2) DEFAULT 0.00,
            status VARCHAR(30) DEFAULT 'Completed',
            payment_terms VARCHAR(100) NULL,
            vehicle_number VARCHAR(50) NULL,
            notes TEXT NULL,
            created_by BIGINT(20) UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_purchases_supplier (supplier_id),
            INDEX idx_purchases_date (purchase_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS purchase_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            purchase_id INT NOT NULL,
            item_type ENUM('material', 'product') DEFAULT 'material',
            item_id INT NOT NULL,
            item_name VARCHAR(255) NULL,
            hsn_code VARCHAR(50) NULL,
            unit VARCHAR(30) DEFAULT 'KG',
            quantity DECIMAL(12, 4) NOT NULL DEFAULT 1.0000,
            rate_per_kg DECIMAL(12, 2) DEFAULT 0.00,
            unit_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            gst_rate DECIMAL(5, 2) DEFAULT 18.00,
            taxable_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            total_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_purchase_items_purchase (purchase_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 5.2 Ensure columns in products and materials
    $productCols = [
        'rate_per_kg' => "DECIMAL(10, 2) DEFAULT 0.00",
        'unit' => "VARCHAR(50) DEFAULT 'PCS'",
        'category_name' => "VARCHAR(150) DEFAULT 'Finished Goods'",
        'hsn_code' => "VARCHAR(50) DEFAULT '392690'",
        'weight_per_piece' => "DECIMAL(10, 4) DEFAULT 0.0000"
    ];
    foreach ($productCols as $cName => $cDef) {
        $c = $pdo->query("SHOW COLUMNS FROM products LIKE '$cName'")->fetchAll();
        if (empty($c)) {
            $pdo->exec("ALTER TABLE products ADD COLUMN `$cName` $cDef");
        }
    }

    $materialCols = [
        'rate_per_kg' => "DECIMAL(10, 2) DEFAULT 0.00",
        'hsn_code' => "VARCHAR(50) DEFAULT '390110'",
        'category' => "VARCHAR(100) DEFAULT 'Raw Material'",
        'price_per_unit' => "DECIMAL(10, 2) DEFAULT 0.00"
    ];
    foreach ($materialCols as $cName => $cDef) {
        $c = $pdo->query("SHOW COLUMNS FROM materials LIKE '$cName'")->fetchAll();
        if (empty($c)) {
            $pdo->exec("ALTER TABLE materials ADD COLUMN `$cName` $cDef");
        }
    }

    $supplierCols = [
        'state' => "VARCHAR(50) DEFAULT 'Gujarat'",
        'city' => "VARCHAR(100) NULL",
        'pincode' => "VARCHAR(20) NULL",
        'country' => "VARCHAR(100) DEFAULT 'India'",
        'tax_type' => "VARCHAR(50) DEFAULT 'Regular'"
    ];
    foreach ($supplierCols as $cName => $cDef) {
        $c = $pdo->query("SHOW COLUMNS FROM suppliers LIKE '$cName'")->fetchAll();
        if (empty($c)) {
            $pdo->exec("ALTER TABLE suppliers ADD COLUMN `$cName` $cDef");
        }
    }

    $customerCols = [
        'state' => "VARCHAR(50) DEFAULT 'Gujarat'",
        'city' => "VARCHAR(100) NULL",
        'pincode' => "VARCHAR(20) NULL",
        'country' => "VARCHAR(100) DEFAULT 'India'",
        'tax_type' => "VARCHAR(50) DEFAULT 'Regular'"
    ];
    foreach ($customerCols as $cName => $cDef) {
        $c = $pdo->query("SHOW COLUMNS FROM customers LIKE '$cName'")->fetchAll();
        if (empty($c)) {
            $pdo->exec("ALTER TABLE customers ADD COLUMN `$cName` $cDef");
        }
    }

    $invoiceItemCols = [
        'unit' => "VARCHAR(50) DEFAULT 'PCS'",
        'rate_per_kg' => "DECIMAL(10, 2) DEFAULT 0.00",
        'gst_rate' => "DECIMAL(5, 2) DEFAULT 18.00",
        'hsn_code' => "VARCHAR(50) NULL"
    ];
    foreach ($invoiceItemCols as $cName => $cDef) {
        $c = $pdo->query("SHOW COLUMNS FROM invoice_items LIKE '$cName'")->fetchAll();
        if (empty($c)) {
            $pdo->exec("ALTER TABLE invoice_items ADD COLUMN `$cName` $cDef");
        }
    }

    $invoiceCols = [
        'round_off' => "DECIMAL(10, 2) DEFAULT 0.00",
        'payment_terms' => "VARCHAR(100) NULL",
        'challan_number' => "VARCHAR(50) NULL",
        'po_number' => "VARCHAR(50) NULL",
        'po_date' => "VARCHAR(50) NULL",
        'delivery_at' => "VARCHAR(255) NULL"
    ];
    foreach ($invoiceCols as $cName => $cDef) {
        $c = $pdo->query("SHOW COLUMNS FROM invoices LIKE '$cName'")->fetchAll();
        if (empty($c)) {
            $pdo->exec("ALTER TABLE invoices ADD COLUMN `$cName` $cDef");
        }
    }

    // Seed units & product categories if available
    try {
        $pdo->exec("
            INSERT IGNORE INTO units (name, code, symbol, is_active) VALUES 
            ('Pieces', 'PCS', 'Pcs', 1),
            ('Kilograms', 'KG', 'Kg', 1),
            ('Meters', 'MTR', 'Mtr', 1),
            ('Rolls', 'ROL', 'Roll', 1),
            ('Bags', 'BAG', 'Bag', 1),
            ('Bundles', 'BDL', 'Bdl', 1);
        ");
        $pdo->exec("
            INSERT IGNORE INTO product_categories (name, code, description, status) VALUES 
            ('Finished Goods', 'FG', 'Final finished plastic products', 'active'),
            ('Meter Category', 'MTR_CAT', 'Extruded / Length-based items measured in Meters', 'active'),
            ('Moulded Products', 'MOULD', 'Injection / Blow moulded items', 'active'),
            ('Raw Materials', 'RM', 'Polymers, Granules and Resins', 'active'),
            ('Additives & Masterbatch', 'MB', 'Colors and processing additives', 'active');
        ");
    } catch (Exception $e) {
        // Ignore if tables don't exist yet
    }

    // 6. Seed default users if missing
    $defaultPassword = password_hash('password123', PASSWORD_DEFAULT);
    $seedUsers = [
        ['admin', 'admin@giriraj.com', $defaultPassword, $defaultPassword, 'Administrator', 'Administrator', 'admin'],
        ['fua', 'fua@giriraj.com', $defaultPassword, $defaultPassword, 'Fua (Partner 1)', 'Fua (Partner 1)', 'partner'],
        ['partner', 'partner@giriraj.com', $defaultPassword, $defaultPassword, 'Partner 2', 'Partner 2', 'partner']
    ];

    foreach ($seedUsers as $u) {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$u[0], $u[1]]);
        if ($checkStmt->fetchColumn() == 0) {
            $seedStmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, password, full_name, name, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
            $seedStmt->execute($u);
            echo "<p style='color:green;'>Seeded user account: <strong>{$u[0]}</strong> ({$u[4]})</p>";
        }
    }

    echo "<p style='color:green;'>Database, User tables, and Audit logs system initialized successfully!</p>";
    echo "<p><a href='login.php' style='display:inline-block; padding:10px 20px; background:#4f46e5; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;'>Go to Login Page</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
