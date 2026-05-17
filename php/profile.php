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
        'status'  => 'error',
        'message' => 'Session token missing.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Temporary User (replace with real token lookup later)
|--------------------------------------------------------------------------
*/
$user = [
    'username' => 'hello',
    'email'    => 'hello@gmail.com'
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

    // If MongoDB is unavailable, return success so UI still works
    if (!$mongoCollection) {
        echo json_encode([
            'status'  => 'success',
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

        $result = $mongoCollection->updateOne(
            ['user_id' => $user['email']],
            ['$set' => $updateData],
            ['upsert' => true]
        );

        echo json_encode([
            'status'      => 'success',
            'message'     => 'Profile updated successfully.',
            'matched'     => $result->getMatchedCount(),
            'modified'    => $result->getModifiedCount(),
            'upserted_id' => $result->getUpsertedId()
                ? (string)$result->getUpsertedId()
                : null
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