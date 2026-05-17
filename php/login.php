<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once 'db.php';

// Read JSON input
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    $data = [];
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email and password are required.'
    ]);
    exit;
}

// Check MySQL connection
if (!$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ]);
    exit;
}

// Fetch user from MySQL
$stmt = $conn->prepare("
    SELECT username, email, password
    FROM users
    WHERE email = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to prepare query.'
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not found.'
    ]);
    exit;
}

// Verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Incorrect password.'
    ]);
    exit;
}

// Generate token
$token = bin2hex(random_bytes(32));

// Save PHP session
$_SESSION['token'] = $token;
$_SESSION['user'] = [
    'username' => $user['username'],
    'email'    => $user['email']
];

// Save to Redis (optional)
if ($redis) {
    try {
        $redis->setex(
            "session:$token",
            86400,
            json_encode([
                'username' => $user['username'],
                'email'    => $user['email']
            ])
        );
    } catch (Throwable $e) {
        // Ignore Redis errors
    }
}

// Return success
echo json_encode([
    'status' => 'success',
    'message' => 'Login successful.',
    'token' => $token,
    'user' => [
        'username' => $user['username'],
        'email'    => $user['email']
    ]
]);
exit;
?>