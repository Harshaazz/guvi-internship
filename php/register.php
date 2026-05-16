<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

header('Content-Type: application/json');

echo json_encode([
    'status' => 'debug',
    'message' => 'register.php is running',
    'env_mysql' => !empty($_ENV['MYSQLHOST']) ? 'Yes' : 'No'
]);
exit;   // ← Stop here for testing
?>