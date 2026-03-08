<?php
/**
 * Device Authentication Guard
 * Include this after config.php in pages that require device authentication.
 * Checks PHP session + verifies token against device-auth database.
 */

define('DEVICE_AUTH_SRC', __DIR__ . '/device-auth/src/');

function requireDeviceAuth(): void {
    // Session must already be started by config.php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['device_verified']) || $_SESSION['device_verified'] !== true) {
        header('Location: login.php');
        exit;
    }

    // Server-side double-verification: re-check token against DB on every request
    if (!empty($_SESSION['device_token_hash'])) {
        try {
            require_once DEVICE_AUTH_SRC . 'Database.php';
            $pdo = getDbConnection();
            $stmt = $pdo->prepare(
                "SELECT is_active, logged_in, valid_days, created_at
                 FROM device_tokens WHERE token = ? LIMIT 1"
            );
            $stmt->execute([$_SESSION['device_token_hash']]);
            $device = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$device || (int)$device['is_active'] !== 1 || (int)$device['logged_in'] !== 1) {
                _clearDeviceSession();
                header('Location: login.php');
                exit;
            }

            // Check token expiry
            if ($device['valid_days'] !== null) {
                $diff = (int)(new DateTime())->diff(new DateTime($device['created_at']))->days;
                if ($diff >= (int)$device['valid_days']) {
                    _clearDeviceSession();
                    header('Location: login.php?expired=1');
                    exit;
                }
            }
        } catch (Exception $e) {
            // Fail open on DB error to avoid locking out users
        }
    }
}

function _clearDeviceSession(): void {
    $_SESSION['device_verified'] = false;
    unset($_SESSION['device_token_hash']);
}
