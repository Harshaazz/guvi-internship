<?php
header('Content-Type: application/json');

// MySQL Connection (Required)
try {
    $pdo = new PDO(
        "mysql:host=" . $_ENV['MYSQLHOST'] . ";port=" . ($_ENV['MYSQLPORT'] ?? 3306) . ";dbname=" . $_ENV['MYSQLDATABASE'],
        $_ENV['MYSQLUSER'],
        $_ENV['MYSQLPASSWORD']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'MySQL Connection Failed']);
    exit;
}

// Redis Connection
$redis = null;
try {
    $redis = new Redis();
    $redis->connect($_ENV['REDISHOST'], $_ENV['REDISPORT'] ?? 6379, 2); // 2 sec timeout
    if (!empty($_ENV['REDISPASSWORD'])) {
        $redis->auth($_ENV['REDISPASSWORD']);
    }
} catch (Exception $e) {
    // Redis is important but we continue
}

// MongoDB - Using Library (more reliable)
$mongoCollection = null;
if (!empty($_ENV['MONGODB_URL'])) {
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $mongoClient = new MongoDB\Client($_ENV['MONGODB_URL']);
        $mongoCollection = $mongoClient->guvi_internship->profiles;
    } catch (Exception $e) {
        // MongoDB optional fallback
    }
}
?>