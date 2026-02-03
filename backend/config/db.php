<?php
/**
 * Database connection (MySQL)
 * Safe for includes (NO output, NO headers, NO exit)
 * Works locally and on Render
 */

declare(strict_types=1);

/* ---------------- ENV LOADER (LOCAL ONLY) ---------------- */
function loadEnvFile(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

/* Load local .env (Render ignores these) */
loadEnvFile(__DIR__ . '/../../.env');
loadEnvFile(__DIR__ . '/../.env');

/* ---------------- CONFIG ---------------- */
$host    = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? '127.0.0.1';
$db      = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'loan_app';
$user    = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root';
$pass    = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? '';
$charset = 'utf8mb4';

/* ---------------- PDO SETUP ---------------- */
$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // IMPORTANT: do not echo here
    throw new RuntimeException('Database connection failed');
}
