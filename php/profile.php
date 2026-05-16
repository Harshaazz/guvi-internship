<?php
header('Content-Type: application/json');
require 'db.php';

// Get token
$token = $_POST['token'] ?? '';

// Validate token in Redis
$userId = $redis->get("session:$token");

if (!$userId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or expired session'
    ]);
    exit;
}

// Determine action
$action = $_POST['action'] ?? 'get';

// MongoDB collection
$collection = $mongoDb->profiles;

// ====================== GET PROFILE ======================
if ($action === 'get') {
    $profile = $collection->findOne(['user_id' => (int)$userId]);

    if ($profile) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'age' => $profile['age'] ?? '',
                'dob' => $profile['dob'] ?? '',
                'contact' => $profile['contact'] ?? '',
                'address' => $profile['address'] ?? ''
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'data' => null
        ]);
    }

    exit;
}

// ====================== UPDATE PROFILE ======================
if ($action === 'update') {
    $age = $_POST['age'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $address = $_POST['address'] ?? '';

    $collection->updateOne(
        ['user_id' => (int)$userId],
        [
            '$set' => [
                'user_id' => (int)$userId,
                'age' => $age,
                'dob' => $dob,
                'contact' => $contact,
                'address' => $address
            ]
        ],
        ['upsert' => true]
    );

    echo json_encode([
        'status' => 'success',
        'message' => 'Profile updated successfully'
    ]);

    exit;
}

// ====================== INVALID ACTION ======================
echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action'
]);
?>