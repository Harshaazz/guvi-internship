<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Read JSON sent from jQuery AJAX
$data = json_decode(file_get_contents("php://input"), true);

$user = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$pass = trim($data['password'] ?? '');

// Validate input
if ($user === '' || $email === '' || $pass === '') {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required."
    ]);
    exit;
}

// Database connection
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT') ?: 3306;
$database = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE');
$username = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');

$conn = new mysqli($host, $username, $password, $database, (int)$port);

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Hash password
$hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

// Prepared statement
$stmt = $conn->prepare(
    "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Prepare failed: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("sss", $user, $email, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Registration successful!"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Insert failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>