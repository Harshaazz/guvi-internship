<?php
// php/profile.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once 'db.php';

/*
|--------------------------------------------------------------------------
| Read JSON Input
|--------------------------------------------------------------------------
*/
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Token and Action
|--------------------------------------------------------------------------
*/
$token = trim($data['token'] ?? '');
$action = trim($data['action'] ?? '');

if ($token === '' || $action === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please login again.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Validate Redis Connection
|--------------------------------------------------------------------------
*/
if (!$redis) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Redis connection failed.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Read Session from Redis
|--------------------------------------------------------------------------
*/
$sessionJson = $redis->get("session:$token");

if (!$sessionJson) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please login again.'
    ]);
    exit;
}

$user = json_decode($sessionJson, true);

if (
    !is_array($user) ||
    empty($user['email'])
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid session data.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| GET PROFILE
|--------------------------------------------------------------------------
*/
if ($action === 'get') {
    $profile = null;

    if ($mongoCollection) {
        $profile = $mongoCollection->findOne([
            'user_id' => $user['email']
        ]);
    }

    $profileData = [
        'age' => '',
        'dob' => '',
        'contact' => '',
        'address' => ''
    ];

    if ($profile) {
        $profileData['age'] = $profile['age'] ?? '';
        $profileData['dob'] = $profile['dob'] ?? '';
        $profileData['contact'] = $profile['contact'] ?? '';
        $profileData['address'] = $profile['address'] ?? '';
    }

    echo json_encode([
        'status' => 'success',
        'user' => [
            'username' => $user['username'] ?? '',
            'email' => $user['email'] ?? ''
        ],
        'profile' => $profileData
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE PROFILE
|--------------------------------------------------------------------------
*/
if ($action === 'update') {
    if (!$mongoCollection) {
        echo json_encode([
            'status' => 'error',
            'message' => 'MongoDB connection failed.'
        ]);
        exit;
    }

    $updateData = [
        'user_id' => $user['email'],
        'age' => (int)($data['age'] ?? 0),
        'dob' => trim($data['dob'] ?? ''),
        'contact' => trim($data['contact'] ?? ''),
        'address' => trim($data['address'] ?? ''),
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ];

    $mongoCollection->updateOne(
        ['user_id' => $user['email']],
        ['$set' => $updateData],
        ['upsert' => true]
    );

    echo json_encode([
        'status' => 'success',
        'message' => 'Profile updated successfully.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Invalid Action
|--------------------------------------------------------------------------
*/
echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action.'
]);
?>