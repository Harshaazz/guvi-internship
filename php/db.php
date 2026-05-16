<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Read Railway environment variables
    $host = getenv('MYSQLHOST');
    $port = getenv('MYSQLPORT') ?: 3306;
    $database = getenv('MYSQLDATABASE');
    $username = getenv('MYSQLUSER');
    $password = getenv('MYSQLPASSWORD');

    // Check if required variables exist
    if (!$host || !$database || !$username) {
        echo json_encode([
            'status' => 'error',
            'message' => 'MySQL environment variables are missing.',
            'MYSQLHOST' => $host ? 'Present' : 'Missing',
            'MYSQLPORT' => $port ? 'Present' : 'Missing',
            'MYSQLDATABASE' => $database ? 'Present' : 'Missing',
            'MYSQLUSER' => $username ? 'Present' : 'Missing',
            'MYSQLPASSWORD' => $password ? 'Present' : 'Missing'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Create MySQL connection
    $conn = new mysqli($host, $username, $password, $database, $port);

    // Check connection
    if ($conn->connect_error) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed.',
            'error' => $conn->connect_error
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Success
    echo json_encode([
        'status' => 'success',
        'message' => 'Database connected successfully!',
        'host' => $host,
        'database' => $database
    ], JSON_PRETTY_PRINT);

    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Exception occurred.',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>