<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// ==================== MySQL Connection ====================
try {
    $dsn = "mysql:host=" . $_ENV['MYSQLHOST'] . 
           ";port=" . ($_ENV['MYSQLPORT'] ?? 3306) . 
           ";dbname=" . $_ENV['MYSQLDATABASE'] . 
           ";charset=utf8mb4";

    $pdo = new PDO($dsn, $_ENV['MYSQLUSER'], $_ENV['MYSQLPASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'MySQL Connection Failed: ' . $e->getMessage()
    ]);
    exit;
}

// ==================== Redis Connection (Optional) ====================
$redis = null;

?>