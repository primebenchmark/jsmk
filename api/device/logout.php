<?php
require_once __DIR__ . '/../../device-auth/src/Database.php';
require_once __DIR__ . '/../../device-auth/src/Security.php';

setSecurityHeaders();
header('Content-Type: application/json');

$rawToken = $_SERVER['HTTP_X_DEVICE_TOKEN'] ?? '';

if (empty($rawToken)) {
    jsonError(400, 'Device token required');
}

try {
    $pdo = getDbConnection();
    $hashedToken = hash('sha256', $rawToken);

    $stmt = $pdo->prepare("UPDATE device_tokens SET logged_in = 0 WHERE token = ?");
    $stmt->execute([$hashedToken]);

    // Destroy the PHP session
    initSecureSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    jsonError(500, 'An error occurred. Please try again.');
}
