<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once 'db.php';

// ----------------------------------------
// Read JSON Input
// ----------------------------------------
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input.'
    ]);
    exit;
}

// ----------------------------------------
// Validate Token
// ----------------------------------------
$token = trim($data['token'] ?? '');
$action = trim($data['action'] ?? '');

if ($token === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Token is missing.'
    ]);
    exit;
}

if (!$redis) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Redis connection failed.'
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

$user = json_decode($sessionData, true);

if (!$user || empty($user['email'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid session data.'
    ]);
    exit;
}

// ----------------------------------------
// GET PROFILE
// ----------------------------------------
if ($action === 'get') {

    $profile = null;

    if ($mongoCollection) {
        $profile = $mongoCollection->findOne([
            'user_id' => $user['email']
        ]);
    }

    if ($profile) {
        $profile = [
            'age'     => $profile['age'] ?? '',
            'dob'     => $profile['dob'] ?? '',
            'contact' => $profile['contact'] ?? '',
            'address' => $profile['address'] ?? ''
        ];
    }

    echo json_encode([
        'status' => 'success',
        'user' => [
            'username' => $user['username'] ?? '',
            'email'    => $user['email'] ?? ''
        ],
        'profile' => $profile
    ]);
    exit;
}

// ----------------------------------------
// UPDATE PROFILE
// ----------------------------------------
if ($action === 'update') {

    if (!$mongoCollection) {
        echo json_encode([
            'status' => 'error',
            'message' => 'MongoDB connection failed.'
        ]);
        exit;
    }

    $age = isset($data['age']) && $data['age'] !== ''
        ? (int)$data['age']
        : null;

    $dob = trim($data['dob'] ?? '');
    $contact = trim($data['contact'] ?? '');
    $address = trim($data['address'] ?? '');

    $updateData = [
        'user_id'    => $user['email'],
        'username'   => $user['username'] ?? '',
        'email'      => $user['email'],
        'age'        => $age,
        'dob'        => $dob,
        'contact'    => $contact,
        'address'    => $address,
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ];

    try {
        $mongoCollection->updateOne(
            ['user_id' => $user['email']],
            ['$set' => $updateData],
            ['upsert' => true]
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'Profile updated successfully.'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'MongoDB error: ' . $e->getMessage()
        ]);
    }

    exit;
}

// ----------------------------------------
// INVALID ACTION
// ----------------------------------------
echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action.'
]);
exit;
?>