<?php
// backend/config/db.php

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: 3306;
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$charset = 'utf8mb4';

if (!$host || !$db || !$user) {
    throw new Exception('Database environment variables missing');
}

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
