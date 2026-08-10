<?php
$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'shreegiriraj_billing';

try {
    // Connect to MySQL server first (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color:green;'>Database '$dbname' created or already exists.</p>";

    // Connect to the new database
    $pdo->exec("USE `$dbname`");

    // Read the SQL file
    $sql = file_get_contents('database.sql');
    
    // Execute the SQL statements
    $pdo->exec($sql);
    
    // Upgrade ERP tables
    $erp_sql = file_get_contents('erp_upgrade.sql');
    $pdo->exec($erp_sql);
    
    echo "<p style='color:green;'>Database and ERP tables created successfully!</p>";
    
    echo "<p><a href='index.php'>Go to Dashboard</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
