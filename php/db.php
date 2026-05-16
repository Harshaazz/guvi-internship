<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// MySQL Connection
try {
    $dsn = "mysql:host=" . $_ENV['MYSQLHOST'] . ";port=" . ($_ENV['MYSQLPORT'] ?? 3306) . ";dbname=" . $_ENV['MYSQLDATABASE'];
    
    $pdo = new PDO($dsn, $_ENV['MYSQLUSER'], $_ENV['MYSQLPASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database Connection Failed: ' . $e->getMessage()
    ]);
    exit;
}

// Redis Connection (Optional for now)
$redis = null;

echo "DB Connection Successful"; // Temporary debug line (remove later)
?>