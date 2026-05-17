<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once 'db.php';

if (!$mongoCollection) {
    echo json_encode([
        "status" => "error",
        "message" => "Mongo collection is null"
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $result = $mongoCollection->updateOne(
        ['email' => 'hello@gmail.com'],
        [
            '$set' => [
                'username'   => 'hello',
                'age'        => '45',
                'dob'        => '2026-04-30',
                'contact'    => '09444949748',
                'address'    => 'hello',
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ],
        ['upsert' => true]
    );

    echo json_encode([
        "status" => "success",
        "matched" => $result->getMatchedCount(),
        "modified" => $result->getModifiedCount(),
        "upserted_id" => $result->getUpsertedId()
            ? (string)$result->getUpsertedId()
            : null
    ], JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>