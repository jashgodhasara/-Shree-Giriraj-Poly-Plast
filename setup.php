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
