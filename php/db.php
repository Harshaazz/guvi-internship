<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| MySQL Connection
|--------------------------------------------------------------------------
| This file should ONLY create the database connection.
| It must NOT echo or print anything.
*/

$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT') ?: 3306;
$database = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE');
$username = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');

/*
|--------------------------------------------------------------------------
| Validate Environment Variables
|--------------------------------------------------------------------------
*/
if (!$host || !$database || !$username) {
    die('MySQL environment variables are missing.');
}

/*
|--------------------------------------------------------------------------
| Create MySQL Connection
|--------------------------------------------------------------------------
*/
$conn = new mysqli(
    $host,
    $username,
    $password,
    $database,
    (int)$port
);

/*
|--------------------------------------------------------------------------
| Check Connection
|--------------------------------------------------------------------------
*/
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

/*
|--------------------------------------------------------------------------
| Set Charset
|--------------------------------------------------------------------------
*/
$conn->set_charset('utf8mb4');

/*
|--------------------------------------------------------------------------
| MongoDB Connection
|--------------------------------------------------------------------------
*/
$mongoCollection = null;

try {
    if (class_exists('MongoDB\Client')) {
        $mongoUri = getenv('MONGO_URL')
            ?: getenv('MONGODB_URI')
            ?: getenv('MONGO_URI');

        if ($mongoUri) {
            require_once __DIR__ . '/../vendor/autoload.php';

            $mongoClient = new MongoDB\Client($mongoUri);

            // Database name from Railway variable or default
            $mongoDbName = getenv('MONGO_DATABASE') ?: 'guvi';

            // Collection used for user profiles
            $mongoCollection = $mongoClient
                ->selectDatabase($mongoDbName)
                ->selectCollection('profiles');
        }
    }
} catch (Exception $e) {
    // Keep application running even if MongoDB is unavailable
    $mongoCollection = null;
}

/*
|--------------------------------------------------------------------------
| Redis Connection
|--------------------------------------------------------------------------
*/
$redis = null;

try {
    if (class_exists('Predis\Client')) {
        require_once __DIR__ . '/../vendor/autoload.php';

        $redisUrl = getenv('REDIS_URL');

        if ($redisUrl) {
            $redis = new Predis\Client($redisUrl);
            $redis->connect();
        }
    }
} catch (Exception $e) {
    // Keep application running even if Redis is unavailable
    $redis = null;
}
?>