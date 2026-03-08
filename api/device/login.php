<?php
require_once __DIR__ . '/../../device-auth/src/Database.php';
require_once __DIR__ . '/../../device-auth/src/Security.php';

setSecurityHeaders();
header('Content-Type: application/json');

$rawToken = $_SERVER['HTTP_X_DEVICE_TOKEN'] ?? '';

if (empty($rawToken)) {
    jsonError(400, 'Device token required');
}

$rateLimitKey = 'device_login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateLimitKey, 10, 60)) {
    jsonError(429, 'Too many attempts. Please try again later.');
}
recordAttempt($rateLimitKey);

try {
    $pdo = getDbConnection();
    $hashedToken = hash('sha256', $rawToken);

    $stmt = $pdo->prepare("SELECT * FROM device_tokens WHERE token = ?");
    $stmt->execute([$hashedToken]);
    $device = $stmt->fetch();

    if (!$device) {
        http_response_code(404);
        echo json_encode(['error' => 'Unrecognized device']);
        exit;
    }

    if ((int)$device['is_active'] === 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Awaiting admin approval']);
        exit;
    }

    // Mark device as logged in
    $stmt = $pdo->prepare("UPDATE device_tokens SET logged_in = 1, last_seen = DATETIME('now') WHERE token = ?");
    $stmt->execute([$hashedToken]);

    // Establish PHP session so server-side guard can verify without needing the token again
    initSecureSession();
    $_SESSION['device_verified'] = true;
    $_SESSION['device_token_hash'] = $hashedToken;

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    jsonError(500, 'An error occurred. Please try again.');
}
