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

/*
|--------------------------------------------------------------------------
| MongoDB Connection
|--------------------------------------------------------------------------
*/
$mongoCollection = null;

try {
    if (class_exists('MongoDB\\Client') &&
        class_exists('MongoDB\\Driver\\Manager')) {

        // Use Railway MongoDB connection string
        $mongoUri = getenv('MONGO_PUBLIC_URL') ?: getenv('MONGO_URL');

        if (!$mongoUri) {
            throw new Exception('MongoDB URI not found.');
        }

        // Create client with retryWrites disabled (important for Railway)
        $mongoClient = new MongoDB\Client($mongoUri, [
            'retryWrites' => false
        ]);

        // Always use guvi database
        $mongoDb = $mongoClient->selectDatabase('guvi');

        // Use profiles collection
        $mongoCollection = $mongoDb->selectCollection('profiles');

        // Test connection
        $mongoCollection->countDocuments([], ['limit' => 1]);
    }
} catch (Throwable $e) {
    error_log('MongoDB Error: ' . $e->getMessage());
    $mongoCollection = null;
}
/*
|--------------------------------------------------------------------------
| Redis Connection
|--------------------------------------------------------------------------
*/
$redis = null;

try {
    // Use Railway public/internal variables
    $redisHost = getenv('REDISHOST') ?: getenv('REDIS_HOST');
    $redisPort = getenv('REDISPORT') ?: getenv('REDIS_PORT') ?: 6379;
    $redisPassword = getenv('REDISPASSWORD') ?: getenv('REDIS_PASSWORD');

    if ($redisHost) {
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
    }
} catch (Throwable $e) {
    error_log('Redis Error: ' . $e->getMessage());
    $redis = null;
}
