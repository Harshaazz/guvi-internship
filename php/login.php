<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input.'
    ]);
    exit;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required.'
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT username, email, password FROM users WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email or password.'
    ]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email or password.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Generate ONE token only
|--------------------------------------------------------------------------
*/
$token = bin2hex(random_bytes(32));

/*
|--------------------------------------------------------------------------
| Store session in Redis
|--------------------------------------------------------------------------
*/
if ($redis) {
    $redis->setex(
        "session:$token",
        86400,
        json_encode([
            'username' => $user['username'],
            'email' => $user['email']
        ])
    );
}

echo json_encode([
    'status' => 'success',
    'message' => 'Login successful.',
    'token' => $token,
    'user' => [
        'username' => $user['username'],
        'email' => $user['email']
    ]
]);
?>