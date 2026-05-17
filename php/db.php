<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Predis\Client as PredisClient;

/*
|--------------------------------------------------------------------------
| MySQL Connection
|--------------------------------------------------------------------------
*/
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT') ?: 3306;
$database = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE');
$username = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');

$conn = null;

if ($host && $database && $username) {
    $conn = new mysqli($host, $username, $password, $database, (int)$port);

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
    $mongoHost = getenv('MONGOHOST') ?: 'mongodb';
    $mongoPort = getenv('MONGOPORT') ?: 27017;
    $mongoDatabase = getenv('MONGODB_DATABASE') ?: 'guvi';
    $mongoUri = "mongodb://{$mongoHost}:{$mongoPort}";

    $mongoClient = new MongoDB\Client($mongoUri);
    $mongoDb = $mongoClient->$mongoDatabase;
    $mongoCollection = $mongoDb->profiles;
} catch (Exception $e) {
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

    $params = [
        'scheme' => 'tcp',
        'host'   => $redisHost,
        'port'   => (int)$redisPort,
    ];

    if (!empty($redisPassword)) {
        $params['password'] = $redisPassword;
    }

    $redis = new PredisClient($params);

    // Test connection
    $redis->ping();
} catch (Exception $e) {
    $redis = null;
}
?>