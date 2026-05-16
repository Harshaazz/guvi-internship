<?php
header('Content-Type: application/json');
require_once 'db.php';

// Read JSON data from AJAX
$data = json_decode(file_get_contents('php://input'), true);

// Get token and action
$token = $data['token'] ?? '';
$action = $data['action'] ?? '';

// Validate session token
if (empty($token) || !$redis) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please login again.'
    ]);
    exit;
}

$sessionData = $redis->get("session:$token");

if (!$sessionData) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please login again.'
    ]);
    exit;
}

// Decode user data from Redis
$user = json_decode($sessionData, true);

/*
|--------------------------------------------------------------------------
| GET PROFILE
|--------------------------------------------------------------------------
*/
if ($action === 'get') {
    // Find profile in MongoDB using email as unique key
    $profile = null;

    if ($mongoCollection) {
        $profile = $mongoCollection->findOne([
            'user_id' => $user['email']
        ]);
    }

    // Return user data + profile data
    echo json_encode([
        'status' => 'success',
        'user' => [
            'username' => $user['username'] ?? '',
            'email' => $user['email'] ?? ''
        ],
        'profile' => $profile ? [
            'age' => $profile['age'] ?? '',
            'dob' => $profile['dob'] ?? '',
            'contact' => $profile['contact'] ?? '',
            'address' => $profile['address'] ?? ''
        ] : null
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE PROFILE
|--------------------------------------------------------------------------
*/
if ($action === 'update') {

    // Read submitted fields
    $age = trim($data['age'] ?? '');
    $dob = trim($data['dob'] ?? '');
    $contact = trim($data['contact'] ?? '');
    $address = trim($data['address'] ?? '');

    // Validate MongoDB connection
    if (!$mongoCollection) {
        echo json_encode([
            'status' => 'error',
            'message' => 'MongoDB connection is not available.'
        ]);
        exit;
    }

    // Save profile in MongoDB
    $updateData = [
        'user_id'    => $user['email'], // Use email as unique key
        'age'        => ($age === '') ? null : (int)$age,
        'dob'        => $dob,
        'contact'    => $contact,
        'address'    => $address,
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ];

    $mongoCollection->updateOne(
        ['user_id' => $user['email']],
        ['$set' => $updateData],
        ['upsert' => true]
    );

    // Update username/email in Redis if changed in frontend
    $newUsername = trim($data['username'] ?? $user['username']);
    $newEmail = trim($data['email'] ?? $user['email']);

    $updatedUser = [
        'id' => $user['id'] ?? null,
        'username' => $newUsername,
        'email' => $newEmail
    ];

    // Save updated session back to Redis
    $redis->setex(
        "session:$token",
        86400,
        json_encode($updatedUser)
    );

    // Return success
    echo json_encode([
        'status' => 'success',
        'message' => 'Profile updated successfully.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| INVALID ACTION
|--------------------------------------------------------------------------
*/
echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action.'
]);
?>