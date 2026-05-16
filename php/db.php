<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Read Railway environment variables using getenv()
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT') ?: 3306;
$database = getenv('MYSQLDATABASE');
$username = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');

// Check variables
if (!$host || !$database || !$username) {
    echo json_encode([
        'status' => 'error',
        'message' => 'MySQL environment variables are missing.',
        'MYSQLHOST' => $host ? 'Present' : 'Missing',
        'MYSQLPORT' => $port ? 'Present' : 'Missing',
        'MYSQLDATABASE' => $database ? 'Present' : 'Missing',
        'MYSQLUSER' => $username ? 'Present' : 'Missing',
        'MYSQLPASSWORD' => $password ? 'Present' : 'Missing'
    ]);
    exit;
}

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $database, (int)$port);

if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.',
        'error' => $conn->connect_error
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Database connected successfully!'
]);

$conn->close();
?>