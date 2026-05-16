<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once 'db.php';

/*
|--------------------------------------------------------------------------
| Read JSON Input
|--------------------------------------------------------------------------
*/
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

/*
|--------------------------------------------------------------------------
| Validate JSON
|--------------------------------------------------------------------------
*/
if (!$data || !is_array($data)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input.',
        'raw_input' => $rawInput
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Token and Action
|--------------------------------------------------------------------------
*/
$token = $data['token'] ?? '';
$action = $data['action'] ?? '';

/*
|--------------------------------------------------------------------------
| Validate Session Using Redis
|--------------------------------------------------------------------------
*/
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
        'message' => 'Invalid session.'
    ]);
    exit;
}

$user = json_decode($sessionData, true);

if (!$user || !isset($user['email'])) {
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