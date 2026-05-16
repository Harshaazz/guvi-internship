<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Read JSON from AJAX request
$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if ($email === '' || $password === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password are required."
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| MySQL Environment Variables (Railway Compatible)
|--------------------------------------------------------------------------
*/
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT') ?: 3306;
$database = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE');
$username = getenv('MYSQLUSER');
$dbPassword = getenv('MYSQLPASSWORD');

if (!$host || !$database || !$username || !$dbPassword) {
    echo json_encode([
        "status" => "error",
        "message" => "MySQL environment variables are missing.",
        "MYSQLHOST" => $host ? "Present" : "Missing",
        "MYSQLDATABASE" => $database ? "Present" : "Missing",
        "MYSQLUSER" => $username ? "Present" : "Missing",
        "MYSQLPASSWORD" => $dbPassword ? "Present" : "Missing"
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
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Find User by Email
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare(
    "SELECT id, username, email, password FROM users WHERE email = ?"
);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Prepare failed: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found."
    ]);
    exit;
}

$user = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Verify Password
|--------------------------------------------------------------------------
*/
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid password."
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Connect to Redis
|--------------------------------------------------------------------------
*/
try {
    $redis = new Redis();
    $redis->connect(
        getenv('REDISHOST'),
        (int)getenv('REDISPORT')
    );

    if (getenv('REDISPASSWORD')) {
        $redis->auth(getenv('REDISPASSWORD'));
    }

    // Create token
    $token = bin2hex(random_bytes(32));

    // Store session for 24 hours
    $redis->setex(
        "session:$token",
        86400,
        json_encode([
            "id" => $user['id'],
            "username" => $user['username'],
            "email" => $user['email']
        ])
    );

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Redis error: " . $e->getMessage()
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Success Response
|--------------------------------------------------------------------------
*/
echo json_encode([
    "status" => "success",
    "message" => "Login successful.",
    "token" => $token,
    "username" => $user['username'],
    "email" => $user['email']
]);

$stmt->close();
$conn->close();
?>