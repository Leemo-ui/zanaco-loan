<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/db.php';

try {
    // sanity query
    $stmt = $pdo->query("SELECT 1");
    $result = $stmt->fetchColumn();

    echo json_encode([
        "status" => "ok",
        "db" => [
            "connected" => true,
            "ping" => (int) $result
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[health.php] DB check failed: ' . $e->getMessage());

    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
}
exit;
