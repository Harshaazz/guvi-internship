<?php
header('Content-Type: application/json');

echo json_encode([
    'client_exists' => class_exists('MongoDB\\Client'),
    'driver_exists' => class_exists('MongoDB\\Driver\\Manager'),
    'extension_loaded' => extension_loaded('mongodb')
], JSON_PRETTY_PRINT);
?>