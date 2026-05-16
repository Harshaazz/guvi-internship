<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';

// ====================== MySQL ======================
$mysql_host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$mysql_user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$mysql_pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';
$mysql_db   = getenv('MYSQLDATABASE') ?: getenv('DB_DATABASE') ?: 'guvi_intern';

$mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);

if ($mysqli->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'MySQL Connection Failed: ' . $mysqli->connect_error
    ]));
}

$mysqli->set_charset("utf8mb4");

// ====================== MongoDB ======================
try {
    $mongo_uri = getenv('MONGO_URL') ?: getenv('MONGODB_URL') ?: "mongodb://localhost:27017";
    $client = new MongoDB\Client($mongo_uri);
    $mongoDb = $client->guvi_intern;   // Change database name if needed
} catch (Exception $e) {
    die(json_encode([
        'status' => 'error',
        'message' => 'MongoDB Connection Failed: ' . $e->getMessage()
    ]));
}

// ====================== Redis ======================
try {
    $redis_host = getenv('REDISHOST') ?: getenv('REDIS_HOST') ?: '127.0.0.1';
    $redis_port = getenv('REDISPORT') ?: getenv('REDIS_PORT') ?: 6379;
    
    $redis = new Predis\Client([
        'scheme' => 'tcp',
        'host'   => $redis_host,
        'port'   => $redis_port,
    ]);

    $redis->ping(); // Test connection
} catch (Exception $e) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Redis Connection Failed: ' . $e->getMessage()
    ]));
}

// If all connections successful
// echo json_encode(['status' => 'success', 'message' => 'All DBs Connected']);
?>