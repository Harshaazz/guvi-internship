<?php
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? '';
$action = $data['action'] ?? '';

if (empty($token) || !$redis || !($sessionData = $redis->get("session:$token"))) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please login again.']);
    exit;
}

$user = json_decode($sessionData, true);

if ($action === 'get') {
    $profile = $mongoCollection ? $mongoCollection->findOne(['user_id' => $user['email']]) : null;
    echo json_encode([
        'status' => 'success',
        'user' => $user,
        'profile' => $profile ? (array)$profile : null
    ]);
} 
elseif ($action === 'update') {
    if ($mongoCollection) {
        $updateData = [
            'age' => (int)($data['age'] ?? 0),
            'dob' => $data['dob'] ?? '',
            'contact' => $data['contact'] ?? '',
            'address' => $data['address'] ?? '',
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];
        $mongoCollection->updateOne(
            ['user_id' => $user['email']],
            ['$set' => $updateData],
            ['upsert' => true]
        );
    }
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
}
?>