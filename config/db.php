<?php
$host = 'localhost';
$dbname = 'shreegiriraj_billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        if (php_sapi_name() !== 'cli' && basename($_SERVER['PHP_SELF']) !== 'setup.php') {
            header("Location: setup.php");
            exit;
        }
    }
    die("Database Connection failed: " . $e->getMessage());
}
?>
