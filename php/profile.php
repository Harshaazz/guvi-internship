]<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once 'db.php';

/*
|--------------------------------------------------------------------------
| Read JSON Input
|--------------------------------------------------------------------------
*/
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON data.'
    ]);
    exit;
}

$token  = trim($data['token'] ?? '');
$action = trim($data['action'] ?? '');

/*
|--------------------------------------------------------------------------
| Validate Session Token (Stored in Redis)
|--------------------------------------------------------------------------
*/
if ($token === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session token is missing.'
    ]);
    exit;
}

if (!$redis) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Redis connection is not available.'
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

    $profileData = null;

    if ($profile) {
        $profileData = [
            'age'     => $profile['age'] ?? '',
            'dob'     => $profile['dob'] ?? '',
            'contact' => $profile['contact'] ?? '',
            'address' => $profile['address'] ?? ''
        ];
    }

    echo json_encode([
        'status' => 'success',
        'user' => [
            'id'       => $user['id'] ?? null,
            'username' => $user['username'] ?? '',
            'email'    => $user['email'] ?? ''
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

    // Read submitted data
    $newUsername = trim($data['username'] ?? ($user['username'] ?? ''));
    $newEmail    = trim($data['email'] ?? ($user['email'] ?? ''));
    $age         = trim($data['age'] ?? '');
    $dob         = trim($data['dob'] ?? '');
    $contact     = trim($data['contact'] ?? '');
    $address     = trim($data['address'] ?? '');

    // Validate required fields
    if ($newUsername === '' || $newEmail === '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Username and email are required.'
        ]);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Update MongoDB Profile
    |--------------------------------------------------------------------------
    */
    if (!$mongoCollection) {
        echo json_encode([
            'status' => 'error',
            'message' => 'MongoDB connection is not available.'
        ]);
        exit;
    }

    $profileData = [
        'user_id'    => $newEmail,
        'age'        => ($age === '') ? null : (int)$age,
        'dob'        => $dob,
        'contact'    => $contact,
        'address'    => $address,
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ];

    $mongoCollection->updateOne(
        ['user_id' => $user['email']],   // Find existing profile by old email
        ['$set' => $profileData],
        ['upsert' => true]
    );

    /*
    |--------------------------------------------------------------------------
    | Update Session Data in Redis
    |--------------------------------------------------------------------------
    */
    $updatedUser = [
        'id'       => $user['id'] ?? null,
        'username' => $newUsername,
        'email'    => $newEmail
    ];

    // Save session back to Redis for another 24 hours
    $redis->setex(
        "session:$token",
        86400,
        json_encode($updatedUser)
    );

    /*
    |--------------------------------------------------------------------------
    | Success Response
    |--------------------------------------------------------------------------
    */
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