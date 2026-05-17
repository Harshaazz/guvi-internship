<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once 'db.php';

/*
|--------------------------------------------------------------------------
| Read JSON Input Safely
|--------------------------------------------------------------------------
*/
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    $data = [];
}

/*
|--------------------------------------------------------------------------
| Get Token and Action
|--------------------------------------------------------------------------
*/
$token  = $data['token'] ?? ($_GET['token'] ?? '');
$action = $data['action'] ?? ($_GET['action'] ?? 'get');

if (empty($token)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Session token missing.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Get User From localStorage Data
|--------------------------------------------------------------------------
| login.js stores:
| localStorage.setItem('user', JSON.stringify(response.user));
|--------------------------------------------------------------------------
*/
$userData = $data['user'] ?? ($_GET['user'] ?? []);

// If user is sent as a JSON string, decode it
if (is_string($userData)) {
    $decoded = json_decode($userData, true);
    if (is_array($decoded)) {
        $userData = $decoded;
    }
}

// Ensure array
if (!is_array($userData)) {
    $userData = [];
}

// Extract username and email
$username = $userData['username'] ?? '';
$email    = $userData['email'] ?? '';

if (empty($email)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'User email missing.'
    ]);
    exit;
}

// Build user object
$user = [
    'username' => $username,
    'email'    => $email
];

/*
|--------------------------------------------------------------------------
| GET PROFILE
|--------------------------------------------------------------------------
*/
if ($action === 'get') {

    // If MongoDB is unavailable, still return user data
    if (!$mongoCollection) {
        echo json_encode([
            'status'  => 'success',
            'user'    => $user,
            'profile' => null
        ]);
        exit;
    }

    try {
        // Find profile by email
        $profile = $mongoCollection->findOne([
            'email' => $email
        ]);

        if ($profile) {
            $profile = (array)$profile;
            unset($profile['_id']);

            // Return only form fields
            $profile = [
                'age'     => $profile['age'] ?? '',
                'dob'     => $profile['dob'] ?? '',
                'contact' => $profile['contact'] ?? '',
                'address' => $profile['address'] ?? ''
            ];
        } else {
            $profile = null;
        }

        echo json_encode([
            'status'  => 'success',
            'user'    => $user,
            'profile' => $profile
        ]);
    } catch (Throwable $e) {
        echo json_encode([
            'status'  => 'error',
            'message' => $e->getMessage()
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

    // If MongoDB is unavailable
    if (!$mongoCollection) {
        echo json_encode([
            'status'  => 'success',
            'message' => 'Profile data cannot be stored because MongoDB is unavailable.'
        ]);
        exit;
    }

    try {
        $updateData = [
            'email'      => $email,
            'username'   => $username,
            'age'        => (int)($data['age'] ?? 0),
            'dob'        => $data['dob'] ?? '',
            'contact'    => $data['contact'] ?? '',
            'address'    => $data['address'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update existing profile or insert new one
        $result = $mongoCollection->updateOne(
            ['email' => $email],
            ['$set' => $updateData],
            ['upsert' => true]
        );

        echo json_encode([
            'status'   => 'success',
            'message'  => 'Profile updated successfully.',
            'matched'  => $result->getMatchedCount(),
            'modified' => $result->getModifiedCount()
        ]);
    } catch (Throwable $e) {
        echo json_encode([
            'status'  => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| Invalid Action
|--------------------------------------------------------------------------
*/
echo json_encode([
    'status'  => 'error',
    'message' => 'Invalid action.'
]);
exit;
?>