<?php
header('Content-Type: application/json');

// Simple health-check endpoint to verify DB connectivity
// Usage: GET /backend/handlers/health.php

require_once __DIR__ . '/../config/db.php';

try {
    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

    // quick sanity query
    $stmt = $pdo->query('SELECT 1 as ok');
    $result = $stmt->fetch();

    echo json_encode([
        'status' => 'ok',
        'db' => [
            'connected' => true,
            'server' => $host,
            'database' => $db,
            'ping' => (int)($result['ok'] ?? 0)
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[health.php] DB health check failed: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'DB connection failed',
        'error' => $e->getMessage()
    ]);
}

// EOF
