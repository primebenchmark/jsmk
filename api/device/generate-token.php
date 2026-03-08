<?php
require_once __DIR__ . '/../../device-auth/src/Database.php';
require_once __DIR__ . '/../../device-auth/src/Security.php';

setSecurityHeaders();

$rateLimitKey = 'gen_token_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateLimitKey, 5, 60)) {
    jsonError(429, 'Too many attempts. Please try again later.');
}
recordAttempt($rateLimitKey);

try {
    $pdo = getDbConnection();
    $rawToken = bin2hex(random_bytes(32));
    $hashedToken = hash('sha256', $rawToken);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $stmt = $pdo->prepare("INSERT INTO device_tokens (token, created_at, ip_address) VALUES (?, DATETIME('now'), ?)");
    $stmt->execute([$hashedToken, $ip]);

    header('Content-Type: application/json');
    echo json_encode(['device_token' => $rawToken]);
} catch (Exception $e) {
    jsonError(500, 'An error occurred. Please try again.');
}
