<?php
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$username = trim($data['username'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

try {
    $stmt->execute([$username, $email, $hashed]);
    echo json_encode(['status' => 'success', 'message' => 'Registration successful! Please login.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists']);
}
?>