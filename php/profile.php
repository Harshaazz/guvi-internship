<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Start session (add this near the top of profile.php if not already present)
session_start();

/*
|--------------------------------------------------------------------------
| Validate Session Using PHP Session
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please login again.'
    ]);
    exit;
}

$user = $_SESSION['user'];

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