<?php
require_once __DIR__ . '/../../device-auth/src/Middleware/DeviceVerifier.php';
require_once __DIR__ . '/../../device-auth/src/Security.php';

setSecurityHeaders();
header('Content-Type: application/json');

$device = verifyDeviceToken();

if ($device && (int)$device['is_active'] === 1 && (int)$device['logged_in'] === 1) {
    // Refresh PHP session on successful verify
    initSecureSession();
    $_SESSION['device_verified'] = true;
    $_SESSION['device_token_hash'] = hash('sha256', $_SERVER['HTTP_X_DEVICE_TOKEN'] ?? '');

    echo json_encode(['success' => true]);
} elseif ($device && (int)$device['is_active'] === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Awaiting admin approval']);
} elseif ($device) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Device recognized but not logged in']);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Unrecognized device']);
}
