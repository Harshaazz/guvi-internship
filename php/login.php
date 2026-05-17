<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once 'db.php';

/*
|--------------------------------------------------------------------------
| Read JSON Input Safely
|--------------------------------------------------------------------------
*/
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// If no JSON was sent (e.g., opening login.php directly), use empty array
if (!is_array($data)) {
    $data = [];
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Only require email/password for actual login requests
if ($email === '' || $password === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email and password are required.'
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
| Store session using PHP Session
|--------------------------------------------------------------------------
*/

$_SESSION['user'] = [
    'username' => $user['username'],
    'email'    => $user['email']
];

// Optional: also store token if your frontend still uses it
$_SESSION['token'] = $token;

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