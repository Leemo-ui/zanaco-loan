<?php
// backend/config/db.php

header('Content-Type: application/json; charset=utf-8');

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: 3306;
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$charset = 'utf8mb4';

if (!$host || !$db || !$user) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database environment variables missing'
    ]);
    exit;
}

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}
