<?php
// Test Database Connection Script

function loadEnv($path) {
    if (!file_exists($path)) {
        echo "❌ .env file not found at: $path\n";
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
    return true;
}

echo "=== ZANACO LOAN - Database Connection Test ===\n\n";

// Load environment variables
echo "1️⃣  Loading .env file...\n";
// Try multiple locations
$envPaths = [
    __DIR__ . '/.env',
    __DIR__ . '/backend/handlers/.env',
];

$envLoaded = false;
foreach ($envPaths as $path) {
    if (file_exists($path)) {
        if (loadEnv($path)) {
            echo "   ✅ Loaded from: $path\n\n";
            $envLoaded = true;
            break;
        }
    }
}

if (!$envLoaded) {
    die("❌ Could not find or load .env file\n");
}

// Display DB config (censored password)
echo "2️⃣  Environment variables loaded:\n";
echo "   DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "   DB_PORT: " . ($_ENV['DB_PORT'] ?? 'NOT SET') . "\n";
echo "   DB_USER: " . ($_ENV['DB_USER'] ?? 'NOT SET') . "\n";
echo "   DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? '***' : 'NOT SET') . "\n";
echo "   DB_NAME: " . ($_ENV['DB_NAME'] ?? 'NOT SET') . "\n\n";

// Test connection
echo "3️⃣  Testing database connection...\n";
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $db   = $_ENV['DB_NAME'] ?? 'loan_app';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASSWORD'] ?? '';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Database connection successful!\n\n";
    
    // Check tables
    echo "4️⃣  Checking tables...\n";
    $stmt = $pdo->query("SHOW TABLES;");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✅ Tables found:\n";
        foreach ($tables as $table) {
            echo "   - $table\n";
        }
    } else {
        echo "⚠️  No tables found in database\n";
    }
    
    // Check loan_applications table structure
    echo "\n5️⃣  Checking loan_applications table structure...\n";
    if (in_array('loan_applications', $tables)) {
        $stmt = $pdo->query("DESCRIBE loan_applications;");
        $columns = $stmt->fetchAll();
        echo "✅ Table columns:\n";
        foreach ($columns as $col) {
            echo "   - {$col['Field']} ({$col['Type']})\n";
        }
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM loan_applications;");
        $result = $stmt->fetch();
        echo "\n📊 Total records: " . $result['count'] . "\n";
    } else {
        echo "❌ loan_applications table not found\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== ✅ All checks passed! Database is connected and ready. ===\n";
?>
