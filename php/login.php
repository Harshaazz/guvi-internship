<?php
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

$stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $token = bin2hex(random_bytes(32));
    
    $sessionData = json_encode([
        'user_id' => $user['id'],
        'username' => $user['username'],
        'email' => $email
    ]);

    if ($redis) {
        $redis->setex("session:$token", 86400 * 7, $sessionData); // 7 days
    }

    echo json_encode([
        'status' => 'success',
        'token' => $token,
        'user' => ['username' => $user['username'], 'email' => $email]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
}
?>