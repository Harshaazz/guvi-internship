<?php
header('Content-Type: application/json');
require 'db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($id, $hashed);
$stmt->fetch();

if ($id && password_verify($password, $hashed)) {
    $token = bin2hex(random_bytes(32));
    
    // Store in Redis
    $redis->setex("session:$token", 3600, $id); // 1 hour expiry
    
    echo json_encode(['status' => 'success', 'token' => $token]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
}
?>
