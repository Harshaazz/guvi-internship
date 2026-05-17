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
    // Only attempt MongoDB connection if extension is installed
    if (class_exists('MongoDB\\Client') &&
        class_exists('MongoDB\\Driver\\Manager')) {

        $mongoHost = getenv('MONGOHOST') ?: 'mongodb';
        $mongoPort = getenv('MONGOPORT') ?: 27017;
        $mongoDatabase = getenv('MONGODB_DATABASE') ?: 'guvi';

        $mongoUri = "mongodb://{$mongoHost}:{$mongoPort}";

        $mongoClient = new MongoDB\Client($mongoUri);
        $mongoDb = $mongoClient->$mongoDatabase;
        $mongoCollection = $mongoDb->profiles;
    }
} catch (Throwable $e) {
    // Ignore MongoDB errors so login still works
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