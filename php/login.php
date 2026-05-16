<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Read JSON request body
$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if ($email === '' || $password === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password are required."
    ]);
    exit;
}

// MySQL connection
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT') ?: 3306;
$database = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE');
$username = getenv('MYSQLUSER');
$dbPassword = getenv('MYSQLPASSWORD');

$conn = new mysqli($host, $username, $dbPassword, $database, (int)$port);

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed."
    ]);
    exit;
}

// Prepared statement
$stmt = $conn->prepare(
    "SELECT id, username, password FROM users WHERE email = ?"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email or password."
    ]);
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email or password."
    ]);
    exit;
}

// Generate session token
$token = bin2hex(random_bytes(32));

// Redis connection
$redis = new Redis();
$redis->connect(getenv('REDISHOST'), (int)getenv('REDISPORT'));
if (getenv('REDISPASSWORD')) {
    $redis->auth(getenv('REDISPASSWORD'));
}

// Store token for 24 hours
$redis->setex(
    "session:$token",
    86400,
    json_encode([
        "id" => $user['id'],
        "username" => $user['username'],
        "email" => $email
    ])
);

// Success response
echo json_encode([
    "status" => "success",
    "message" => "Login successful.",
    "token" => $token,
    "username" => $user['username'],
    "email" => $email
]);

$stmt->close();
$conn->close();
?>