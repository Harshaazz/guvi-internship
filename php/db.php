<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';

// ====================== MySQL ======================
$mysql_host = getenv('MYSQLHOST') ?: 'localhost';
$mysql_user = getenv('MYSQLUSER') ?: 'root';
$mysql_pass = getenv('MYSQLPASSWORD') ?: '';
$mysql_db   = getenv('MYSQLDATABASE') ?: 'guvi_intern';

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
    $mongo_url = getenv('MONGO_URL') ?: getenv('MONGODB_URL') ?: "mongodb://localhost:27017";
    $client = new MongoDB\Client($mongo_url);
    $mongoDb = $client->guvi_intern;
} catch (Exception $e) {
    die(json_encode(['status' => 'error', 'message' => 'MongoDB Failed']));
}

// ====================== Redis ======================
try {
    $redis_host = getenv('REDISHOST') ?: '127.0.0.1';
    $redis_port = getenv('REDISPORT') ?: 6379;
    $redis = new Predis\Client(['host' => $redis_host, 'port' => $redis_port]);
    $redis->ping();
} catch (Exception $e) {
    die(json_encode(['status' => 'error', 'message' => 'Redis Failed']));
}
?>