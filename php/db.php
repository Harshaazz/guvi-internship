<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';

// ====================== MySQL ======================
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'guvi_intern';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'MySQL Connection Failed: ' . $mysqli->connect_error
    ]));
}

$mysqli->set_charset("utf8mb4");

echo "<!-- DB Connected -->";
?>