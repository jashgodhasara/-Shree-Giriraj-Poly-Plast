<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$results = [
    'php_version' => PHP_VERSION,
    'sqlite_extension' => extension_loaded('pdo_sqlite'),
    'sqlite_file_exists' => file_exists('/var/www/html/database/database.sqlite'),
    'sqlite_file_writable' => is_writable('/var/www/html/database/database.sqlite'),
    'database_dir_writable' => is_writable('/var/www/html/database'),
    'storage_dir_writable' => is_writable('/var/www/html/storage'),
    'sessions_dir_writable' => is_writable('/var/www/html/storage/framework/sessions'),
];

try {
    $pdo = new PDO('sqlite:/var/www/html/database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    $results['sqlite_connected'] = true;
    $results['tables'] = $tables;
} catch (\Throwable $e) {
    $results['sqlite_connected'] = false;
    $results['sqlite_error'] = $e->getMessage();
}

echo json_encode($results, JSON_PRETTY_PRINT);
