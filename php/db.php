<?php
// php/login.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once 'db.php';

// Ensure MySQL is connected
if (!$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ]);
    exit;
}

// Ensure Redis is connected
if (!$redis) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Redis connection failed.'
    ]);
    exit;
}

// Read JSON input
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Validate JSON
if (!is_array($data)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input.'
    ]);
    exit;
}

// Get values
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email and password are required.'
    ]);
    exit;
}

// Prepared statement (required by project criteria)
$stmt = $conn->prepare("
    SELECT id, username, email, password
    FROM users
    WHERE email = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Query preparation failed.',
        'error' => $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email or password.'
    ]);
    $stmt->close();
    exit;
}

$user = $result->fetch_assoc();

// Verify hashed password
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email or password.'
    ]);
    $stmt->close();
    exit;
}

// Remove password before storing session
unset($user['password']);

// Generate secure session token
$token = bin2hex(random_bytes(32));

// Store session in Redis for 24 hours
try {
    $redis->setex(
        "session:$token",
        86400, // 24 hours
        json_encode($user)
    );
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to store session.',
        'error' => $e->getMessage()
    ]);
    $stmt->close();
    exit;
}

$stmt->close();

// Success response
echo json_encode([
    'status' => 'success',
    'message' => 'Login successful.',
    'token' => $token,
    'user' => $user
]);
exit;
?>