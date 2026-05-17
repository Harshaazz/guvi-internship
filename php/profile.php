<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

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
    $data = [];
}

/*
|--------------------------------------------------------------------------
| Get Token and Action
|--------------------------------------------------------------------------
*/
$token  = $data['token'] ?? '';
$action = $data['action'] ?? 'get';

if (empty($token)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Session token missing.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Get User From localStorage (sent from JavaScript)
|--------------------------------------------------------------------------
*/
$storedUser = json_decode($data['user'] ?? '{}', true);

if (!is_array($storedUser)) {
    $storedUser = [];
}

$email = $storedUser['email'] ?? '';
$username = $storedUser['username'] ?? '';

if (empty($email)) {
    // Fallback to token-based placeholder if user data wasn't sent
    $email = 'hello@gmail.com';
    $username = 'hello';
}

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
            'email' => $email
        ]);

        if ($profile) {
            $profile = (array)$profile;
            unset($profile['_id']);

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
            'status'  => 'success',
            'user'    => $user,
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

        $mongoCollection->updateOne(
            ['email' => $email],
            ['$set' => $updateData],
            ['upsert' => true]
        );

        echo json_encode([
            'status'  => 'success',
            'message' => 'Profile updated successfully.'
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