<?php

// Load environment variables from .env file if present
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Try loading from multiple locations (local .env fallbacks)
loadEnv(__DIR__ . '/../../.env');
loadEnv(__DIR__ . '/../handlers/.env');

// Read from environment variables first, then getenv(), then defaults
$host = $_ENV['DB_HOST'] ?? (getenv('DB_HOST') ?: 'localhost');
$db   = $_ENV['DB_NAME'] ?? (getenv('DB_NAME') ?: 'loan_app');
$user = $_ENV['DB_USER'] ?? (getenv('DB_USER') ?: 'root');
$pass = $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? (getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '');
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed");
}
