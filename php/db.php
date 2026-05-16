<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

echo json_encode([
    'status' => 'debug',
    'message' => 'Debug Info',
    'MYSQLHOST' => !empty($_ENV['MYSQLHOST']) ? 'Present' : 'Missing',
    'MYSQLUSER' => !empty($_ENV['MYSQLUSER']) ? 'Present' : 'Missing',
    'MYSQLDATABASE' => !empty($_ENV['MYSQLDATABASE']) ? 'Present' : 'Missing',
    'All_ENV_Keys' => array_keys($_ENV)
], JSON_PRETTY_PRINT);

exit;
?>