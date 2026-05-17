<?php
// php/db.php
// Final fixed version with safe MongoDB handling.

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

use Predis\Client as PredisClient;

/*
|--------------------------------------------------------------------------
| MySQL Connection
|--------------------------------------------------------------------------
*/
$conn = null;

$mysqlHost = getenv('MYSQLHOST');
$mysqlPort = getenv('MYSQLPORT') ?: 3306;
$mysqlDatabase = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE');
$mysqlUser = getenv('MYSQLUSER');
$mysqlPassword = getenv('MYSQLPASSWORD');

if ($mysqlHost && $mysqlDatabase && $mysqlUser) {
    $conn = @new mysqli(
        $mysqlHost,
        $mysqlUser,
        $mysqlPassword,
        $mysqlDatabase,
        (int)$mysqlPort
    );

    if ($conn->connect_error) {
        $conn = null;
    }
}

// REPLACE ONLY THE MONGODB SECTION IN php/db.php WITH THIS EXACT CODE

/*
|--------------------------------------------------------------------------
| MongoDB Connection
|--------------------------------------------------------------------------
*/
$mongoCollection = null;

try {
    // Ensure MongoDB library and PHP extension are available
    if (class_exists('MongoDB\\Client') &&
        class_exists('MongoDB\\Driver\\Manager')) {

        // Use Railway's public URL first (most reliable)
        $mongoUri = getenv('MONGO_PUBLIC_URL');

        // Fallback to internal URL if public URL is not set
        if (!$mongoUri) {
            $mongoUri = getenv('MONGO_URL');
        }

        // Stop if no URI is found
        if (!$mongoUri) {
            throw new Exception('MongoDB URI not found in environment variables.');
        }

        // Extract database name from URI if possible
        $parsed = parse_url($mongoUri);
        $dbName = 'guvi';

        if (!empty($parsed['path']) && $parsed['path'] !== '/') {
            $dbName = ltrim($parsed['path'], '/');
        }

        // Create MongoDB client
        $mongoClient = new MongoDB\Client($mongoUri);

        // Select database and collection
        $mongoDb = $mongoClient->selectDatabase($dbName);
        $mongoCollection = $mongoDb->selectCollection('profiles');

        // Test connection
        $mongoCollection->countDocuments([], ['limit' => 1]);
    }
} catch (Throwable $e) {
    // Optional: save the error for debugging
    file_put_contents(
        __DIR__ . '/mongo_error.log',
        date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );

    $mongoCollection = null;
}
/*
|--------------------------------------------------------------------------
| Redis Connection
|--------------------------------------------------------------------------
*/
$redis = null;

try {
    $redisHost = getenv('REDISHOST') ?: 'redis';
    $redisPort = getenv('REDISPORT') ?: 6379;
    $redisPassword = getenv('REDISPASSWORD') ?: null;

    $config = [
        'scheme' => 'tcp',
        'host'   => $redisHost,
        'port'   => (int)$redisPort,
    ];

    if (!empty($redisPassword)) {
        $config['password'] = $redisPassword;
    }

    $redis = new PredisClient($config);

    // Test connection
    $redis->ping();
} catch (Throwable $e) {
    $redis = null;
}
?>