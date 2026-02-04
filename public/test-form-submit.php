<?php
// Manual form submission test

require_once __DIR__ . '/backend/config/db.php';

echo "=== ZANACO LOAN - Form Submission Test ===\n\n";

// Simulate POST data
$_POST = [
    'full_name' => 'John Doe',
    'phone' => '0712345678',
    'email' => 'john@example.com',
    'national_id' => '123456789',
    'dob_year' => '1990',
    'dob_month' => '01',
    'dob_day' => '15',
    'employment_status' => 'Employed',
    'loan_amount' => 'ZMW 30000',
    'repayment' => '6 months'
];

echo "1️⃣  Test Data:\n";
foreach ($_POST as $key => $value) {
    echo "   $key: $value\n";
}

echo "\n2️⃣  Processing...\n";

try {
    // Sanitize input
    $full_name = trim($_POST["full_name"] ?? '');
    $phone = trim($_POST["phone"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $national_id = trim($_POST["national_id"] ?? '');

    $dob_year  = $_POST["dob_year"] ?? '';
    $dob_month = $_POST["dob_month"] ?? '';
    $dob_day   = $_POST["dob_day"] ?? '';

    $employment_status = $_POST["employment_status"] ?? '';
    
    $loan_amount = preg_replace("/[^0-9.]/", "", $_POST["loan_amount"] ?? "0");
    $repayment_months = (int) filter_var($_POST["repayment"] ?? "0", FILTER_SANITIZE_NUMBER_INT);

    $date_of_birth = "$dob_year-$dob_month-$dob_day";

    echo "   Sanitized data:\n";
    echo "   - Full Name: $full_name\n";
    echo "   - Phone: $phone\n";
    echo "   - Email: $email\n";
    echo "   - National ID: $national_id\n";
    echo "   - DOB: $date_of_birth\n";
    echo "   - Employment: $employment_status\n";
    echo "   - Loan Amount: $loan_amount\n";
    echo "   - Repayment Months: $repayment_months\n";

    // Validate
    if (!$full_name || !$phone || !$national_id || !$date_of_birth || !$employment_status || !$loan_amount) {
        throw new Exception('Missing required fields');
    }

    echo "\n3️⃣  Inserting into database...\n";

    $sql = "
        INSERT INTO loan_applications
        (full_name, phone, email, national_id, date_of_birth,
         employment_status, loan_amount, repayment_months)
        VALUES
        (:full_name, :phone, :email, :national_id, :date_of_birth,
         :employment_status, :loan_amount, :repayment_months)
    ";

    $stmt = $pdo->prepare($sql);
    
    $params = [
        ":full_name" => $full_name,
        ":phone" => $phone,
        ":email" => $email,
        ":national_id" => $national_id,
        ":date_of_birth" => $date_of_birth,
        ":employment_status" => $employment_status,
        ":loan_amount" => $loan_amount,
        ":repayment_months" => $repayment_months
    ];

    echo "   SQL Parameters:\n";
    foreach ($params as $key => $value) {
        echo "   $key: $value\n";
    }

    $result = $stmt->execute($params);

    if ($result) {
        echo "\n✅ SUCCESS! Data inserted.\n";
        
        // Verify
        $check = $pdo->query("SELECT * FROM loan_applications WHERE phone = '$phone' LIMIT 1");
        $row = $check->fetch();
        
        if ($row) {
            echo "\n4️⃣  Verification - Data retrieved:\n";
            foreach ($row as $key => $value) {
                echo "   $key: $value\n";
            }
        }
    } else {
        echo "\n❌ FAILED! Execute returned false\n";
        print_r($stmt->errorInfo());
    }

} catch (PDOException $e) {
    echo "\n❌ Database Error:\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   Message: " . $e->getMessage() . "\n";
    print_r($e->errorInfo());
} catch (Exception $e) {
    echo "\n❌ Error:\n";
    echo "   " . $e->getMessage() . "\n";
}

echo "\n";
?>
