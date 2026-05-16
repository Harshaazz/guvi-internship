<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';

// ====================== MySQL ======================
$mysqli = new mysqli("localhost", "root", "", "guvi_intern");

if ($mysqli->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'MySQL Connection Failed: ' . $mysqli->connect_error
    ]));
}

// ====================== MongoDB ======================
try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $mongoDb = $client->guvi_intern;
} catch (Exception $e) {
    die(json_encode([
        'status' => 'error',
        'message' => 'MongoDB Connection Failed: ' . $e->getMessage()
    ]));
}

// ====================== Redis ======================
try {
    $redis = new Predis\Client([
        'scheme' => 'tcp',
        'host'   => '127.0.0.1',
        'port'   => 6379,
    ]);

    // Test Redis connection
    $redis->ping();

} catch (Exception $e) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Redis Connection Failed: ' . $e->getMessage()
    ]));
}
?>