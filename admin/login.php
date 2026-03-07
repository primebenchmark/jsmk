<?php
require_once __DIR__ . '/../config.php';

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$settings = getSettings();

// Rate limiting
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_last_attempt'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['login_attempts'] >= 5 && time() - $_SESSION['login_last_attempt'] < 300) {
        $error = 'Too many failed attempts. Please try again in 5 minutes.';
    } else {
        $password = $_POST['password'] ?? '';
        $settings = getSettings();
        $adminPassword = $settings['admin_password'] ?? 'admin123';
        
        $isValidObject = false;
        if (password_verify($password, $adminPassword)) {
            $isValidObject = true;
        } elseif ($password === $adminPassword && strlen($adminPassword) < 60) {
            // Auto-hash plain text password on first login
            $settings['admin_password'] = password_hash($password, PASSWORD_BCRYPT);
            saveSettings($settings);
            $isValidObject = true;
        }
        
        if ($isValidObject) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['login_attempts'] = 0;
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['login_last_attempt'] = time();
            $error = 'Incorrect password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — JFT Mock Test</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>:root { --floating-btn-size: <?= e(intval($settings['floating_btn_size'] ?? 40)) ?>px; }</style>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
    <script>
        (function(){
            var saved = localStorage.getItem('mcq_theme');
            var dflt = '<?= e($settings['default_theme'] ?? 'light') ?>';
            document.documentElement.setAttribute('data-theme', saved || dflt);
        })();
    </script>
</head>
<body>
    <div class="bg-gradient"></div>
    
    <!-- Theme Toggle -->
    <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()" title="Toggle Theme">
        <span class="theme-toggle-icon" id="themeIcon">🌙</span>
    </button>

    <div class="login-container">
        <div class="login-card">
            <div class="login-icon">🔐</div>
            <h1>Admin Login</h1>
            <p class="login-subtitle">Enter your password to continue</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Enter admin password" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Login →
                </button>
            </form>
            
            <a href="../index.php" class="login-back">← Back to Tests</a>
        </div>
    </div>

    <script src="../assets/js/theme.js"></script>
</body>
</html>
