<?php
require_once 'db.php';
header('Content-Type: application/json');

if ($mongoCollection === null) {
    echo json_encode([
        'status' => 'error',
        'message' => 'MongoDB connection failed'
    ]);
    exit;
}

$result = $mongoCollection->insertOne([
    'test' => 'hello',
    'created_at' => new MongoDB\BSON\UTCDateTime()
]);

echo json_encode([
    'status' => 'success',
    'inserted_id' => (string)$result->getInsertedId()
]);
?>