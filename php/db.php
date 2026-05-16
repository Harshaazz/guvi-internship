<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// ==================== MySQL Connection ====================
try {
    $host = $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST');
    $port = $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? 3306;
    $user = $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER');
    $pass = $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD');
    $db   = $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE');

    if (empty($host) || empty($user) || empty($pass) || empty($db)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing Database Environment Variables'
        ]);
        exit;
    }

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'MySQL Connection Failed: ' . $e->getMessage()
    ]);
    exit;
}

// Redis (optional)
$redis = null;

?>