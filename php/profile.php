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

// If no JSON was sent (e.g., browser opens the URL directly), use empty array
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
        'status' => 'error',
        'message' => 'Session token missing.'
    ]);
    exit;
}

// Use token from localStorage instead of PHP sessions
$user = [
    'username' => 'hello',
    'email' => 'hello@gmail.com'
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
        $profile = $mongoCollection->findOne([
            'user_id' => $user['email']
        ]);

        if ($profile) {
            $profile = (array)$profile;
            unset($profile['_id']);
        } else {
            $profile = null;
        }

        echo json_encode([
            'status'  => 'success',
            'user'    => $user,
            'profile' => $profile
        ]);
    } catch (Throwable $e) {
        // Ignore MongoDB errors and still load page
        echo json_encode([
            'status'  => 'success',
            'user'    => $user,
            'profile' => null
        ]);
    }

    exit;
}

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
        // Fetch profile using user's email as user_id
        $profile = $mongoCollection->findOne([
            'user_id' => $user['email']
        ]);

        // Convert MongoDB document to array
        if ($profile) {
            $profile = (array)$profile;

            // Remove MongoDB internal ID
            unset($profile['_id']);

            // Ensure all expected fields exist
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
| Invalid Action
|--------------------------------------------------------------------------
*/
echo json_encode([
    'status'  => 'error',
    'message' => 'Invalid action.'
]);
exit;
?>