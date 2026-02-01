-- Active: 1742452790524@@127.0.0.1@3306@mysql
<?php
require_once "../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "../config/db.php";

function sendConfirmationEmail($toEmail, $fullName, $loanAmount, $repaymentMonths) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['MAIL_PORT'];

        $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = "Loan Application Received";

        $mail->Body = "
        <h3>Loan Application Submitted</h3>
        <p>Dear <strong>$fullName</strong>,</p>
        <p>Your loan application has been received with the following details:</p>
        <ul>
            <li><strong>Amount:</strong> ZMW $loanAmount</li>
            <li><strong>Repayment:</strong> $repaymentMonths months</li>
            <li><strong>Status:</strong> Pending Review</li>
        </ul>
        <p>We will contact you once a decision has been made.</p>
        <p><strong>Zanaco Loans</strong></p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    // 1. Collect & sanitize input
    $full_name = trim($_POST["full_name"] ?? '');
    $phone = trim($_POST["phone"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $national_id = trim($_POST["national_id"] ?? '');

    $dob_year  = $_POST["dob_year"] ?? '';
    $dob_month = $_POST["dob_month"] ?? '';
    $dob_day   = $_POST["dob_day"] ?? '';

    $employment_status = $_POST["employment_status"] ?? '';

    // Convert "ZMW 30000" → 30000
    $loan_amount = preg_replace("/[^0-9.]/", "", $_POST["loan_amount"] ?? "0");

    // Convert "6 months" → 6
    $repayment_months = (int) filter_var($_POST["repayment"] ?? "0", FILTER_SANITIZE_NUMBER_INT);

    // Build date
    $date_of_birth = "$dob_year-$dob_month-$dob_day";

    // Validate required fields
    if (!$full_name || !$phone || !$national_id || !$date_of_birth || !$employment_status || !$loan_amount) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    // 2. Insert into database
    $sql = "
        INSERT INTO loan_applications
        (full_name, phone, email, national_id, date_of_birth,
         employment_status, loan_amount, repayment_months)
        VALUES
        (:full_name, :phone, :email, :national_id, :date_of_birth,
         :employment_status, :loan_amount, :repayment_months)
    ";

    $stmt = $pdo->prepare($sql);

    $result = $stmt->execute([
        ":full_name" => $full_name,
        ":phone" => $phone,
        ":email" => $email,
        ":national_id" => $national_id,
        ":date_of_birth" => $date_of_birth,
        ":employment_status" => $employment_status,
        ":loan_amount" => $loan_amount,
        ":repayment_months" => $repayment_months
    ]);

    if ($result) {
        // Send confirmation email after database insert succeeds
        if (!empty($email)) {
            sendConfirmationEmail(
                $email,
                $full_name,
                $loan_amount,
                $repayment_months
            );
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Application submitted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to insert data']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}


