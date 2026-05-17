<?php
header('Content-Type: application/json');

echo json_encode([
    'MONGO_PUBLIC_URL' => getenv('MONGO_PUBLIC_URL') ? 'FOUND' : 'NOT FOUND',
    'MONGO_URL'        => getenv('MONGO_URL') ? 'FOUND' : 'NOT FOUND',
    'MONGOHOST'        => getenv('MONGOHOST') ? getenv('MONGOHOST') : 'NOT FOUND',
    'MONGOPORT'        => getenv('MONGOPORT') ? getenv('MONGOPORT') : 'NOT FOUND',
    'MONGOUSER'        => getenv('MONGOUSER') ? 'FOUND' : 'NOT FOUND',
    'MONGOPASSWORD'    => getenv('MONGOPASSWORD') ? 'FOUND' : 'NOT FOUND'
], JSON_PRETTY_PRINT);
?>