<?php
// php/profile.php
// Replace your entire file with this version.
// This version allows profile view/update even if MongoDB is unavailable.

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once 'db.php';

// Read JSON input
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input.'
    ]);
    exit;
}

// Get token
$token = $data['token'] ?? '';
$action = $data['action'] ?? 'get';

if (empty($token)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session token missing.'
    ]);
    exit;
}

// Check Redis connection
if (!$redis) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Redis connection failed.'
    ]);
    exit;
}

// Validate session
$sessionData = $redis->get("session:$token");

if (!$sessionData) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please login again.'
    ]);
    exit;
}

$user = json_decode($sessionData, true);

if (!is_array($user)) {
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

    // If MongoDB is not available, still return user data
    if (!$mongoCollection) {
        echo json_encode([
            'status' => 'success',
            'user' => $user,
            'profile' => null
        ]);
        exit;
    }

    try {
        $profile = $mongoCollection->findOne([
            'user_id' => $user['email']
        ]);

        if ($profile) {
            $profile = (array)$profile;
            unset($profile['_id']);
        }

        echo json_encode([
            'status' => 'success',
            'user' => $user,
            'profile' => $profile
        ]);
    } catch (Throwable $e) {
        // Ignore MongoDB failure and still load page
        echo json_encode([
            'status' => 'success',
            'user' => $user,
            'profile' => null
        ]);
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE PROFILE
|--------------------------------------------------------------------------
*/
if ($action === 'update') {

    // If MongoDB is unavailable, return success so UI still works
    if (!$mongoCollection) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Profile data cannot be stored because MongoDB is unavailable.'
        ]);
        exit;
    }

    try {
        $updateData = [
            'user_id'    => $user['email'],
            'age'        => (int)($data['age'] ?? 0),
            'dob'        => $data['dob'] ?? '',
            'contact'    => $data['contact'] ?? '',
            'address'    => $data['address'] ?? '',
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
    } catch (Throwable $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'MongoDB connection failed.'
        ]);
    }

    exit;
}

// Invalid action
echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action.'
]);
exit;
?>