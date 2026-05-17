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

        // Get Railway MongoDB URI
        $mongoUri = getenv('MONGO_PUBLIC_URL') ?: getenv('MONGO_URL');

        if (!$mongoUri) {
            throw new Exception('MONGO_PUBLIC_URL or MONGO_URL not found.');
        }

        // Force database name to "guvi"
        $mongoClient = new MongoDB\Client($mongoUri);

        $mongoDb = $mongoClient->selectDatabase('guvi');
        $mongoCollection = $mongoDb->selectCollection('profiles');

        // Test connection
        $mongoCollection->countDocuments([], ['limit' => 1]);
    }
} catch (Throwable $e) {
    // Disable logging to file (permission issue on Railway)
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