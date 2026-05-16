<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Read JSON input sent by jQuery AJAX
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if ($email === '' || $password === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email and password are required.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| MySQL Environment Variables (Railway)
|--------------------------------------------------------------------------
*/
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT') ?: 3306;
$database = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE');
$username = getenv('MYSQLUSER');
$dbPassword = getenv('MYSQLPASSWORD');

if (!$host || !$database || !$username || !$dbPassword) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database environment variables are missing.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Connect to MySQL
|--------------------------------------------------------------------------
*/
$conn = new mysqli($host, $username, $dbPassword, $database, (int)$port);

if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Find user by email
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare(
    "SELECT id, username, email, password FROM users WHERE email = ? LIMIT 1"
);

if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Prepare failed: ' . $conn->error
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not found.'
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Verify password
|--------------------------------------------------------------------------
*/
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid password.'
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

/*
|--------------------------------------------------------------------------
| Create session token
|--------------------------------------------------------------------------
*/
$token = bin2hex(random_bytes(32));

/*
|--------------------------------------------------------------------------
| Store session in Redis (if available)
|--------------------------------------------------------------------------
| If Redis extension or variables are unavailable, login still succeeds.
|--------------------------------------------------------------------------
*/
if (class_exists('Redis') && getenv('REDISHOST')) {
    try {
        $redis = new Redis();
        $redis->connect(
            getenv('REDISHOST'),
            (int)(getenv('REDISPORT') ?: 6379),
            5
        );

        if (getenv('REDISPASSWORD')) {
            $redis->auth(getenv('REDISPASSWORD'));
        }

        $sessionData = json_encode([
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ]);

        // Store for 24 hours
        $redis->setex("session:$token", 86400, $sessionData);
    } catch (Exception $e) {
        // Ignore Redis errors so login still works
    }
}

/*
|--------------------------------------------------------------------------
| Successful login response
|--------------------------------------------------------------------------
*/
echo json_encode([
    'status' => 'success',
    'message' => 'Login successful.',
    'token' => $token,
    'username' => $user['username'],
    'email' => $user['email']
]);

$stmt->close();
$conn->close();
?>