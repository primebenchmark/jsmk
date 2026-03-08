<?php
require_once __DIR__ . '/config.php';

$settings   = getSettings();
$siteName   = $settings['site_name']    ?? 'JFT Mock Test';
$defaultTheme = $settings['default_theme'] ?? 'light';
$themeColor   = $settings['theme_color']   ?? 'purple';

// If already authenticated via PHP session, redirect to the main site
if (!empty($_SESSION['device_verified']) && $_SESSION['device_verified'] === true) {
    header('Location: index.php');
    exit;
}

$expired = isset($_GET['expired']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Login — <?= e($siteName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .login-wrap {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
            padding: 1.5rem;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 2.5rem 2rem;
            text-align: center;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(108, 92, 231, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .login-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.6rem;
        }

        .login-subtitle {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .site-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.8rem;
            color: var(--text-muted);
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .brand-logo {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: contain;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.9rem 1.5rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(108, 92, 231, 0.35);
        }

        .btn-login:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .btn-login .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        .btn-login.loading .spinner { display: inline-block; }
        .btn-login.loading .btn-icon { display: none; }
        .btn-login.loading .btn-text { opacity: 0.8; }

        @keyframes spin { to { transform: rotate(360deg); } }

        .notice-box {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            background: rgba(108, 92, 231, 0.06);
            border: 1px solid rgba(108, 92, 231, 0.2);
            border-radius: var(--radius-sm);
            padding: 0.85rem 1rem;
            margin-bottom: 1.5rem;
            text-align: left;
            font-size: 0.82rem;
            color: var(--text-secondary);
            line-height: 1.55;
        }

        .notice-box.warning {
            background: rgba(253, 203, 110, 0.1);
            border-color: rgba(253, 203, 110, 0.4);
        }

        .notice-box.error {
            background: rgba(225, 112, 85, 0.08);
            border-color: rgba(225, 112, 85, 0.3);
        }

        .notice-icon { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

        .login-footer {
            margin-top: 1.5rem;
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(0, 184, 148, 0.1);
            border: 1px solid rgba(0, 184, 148, 0.3);
            color: #00b894;
            border-radius: 99px;
            padding: 0.3rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        /* Toast container */
        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .toast {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.75rem 1.1rem;
            border-radius: var(--radius-sm);
            background: var(--bg-card);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            font-size: 0.875rem;
            color: var(--text-primary);
            animation: toastIn 0.3s ease-out;
            max-width: 320px;
        }

        .toast.removing { animation: toastOut 0.3s ease-in forwards; }

        @keyframes toastIn  { from { opacity: 0; transform: translateX(20px); } }
        @keyframes toastOut { to   { opacity: 0; transform: translateX(20px); } }

        .toast-success { border-left: 3px solid var(--correct); }
        .toast-error   { border-left: 3px solid var(--incorrect); }
        .toast-warning { border-left: 3px solid var(--warning); }

        @media (max-width: 480px) {
            .login-wrap { padding: 1rem; }
            .login-card { padding: 2rem 1.25rem; }
        }
    </style>
    <script>
        (function() {
            var saved = localStorage.getItem('mcq_theme');
            var dflt = '<?= e($defaultTheme) ?>';
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

    <div class="login-wrap">
        <div class="login-card" id="loginCard">

            <!-- Brand -->
            <div class="site-brand">
                <img src="logo.png" alt="" class="brand-logo" onerror="this.style.display='none'">
                <?= e($siteName) ?>
            </div>

            <!-- Lock icon -->
            <div class="login-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="34" height="34">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    <circle cx="12" cy="16" r="1" fill="var(--primary)" stroke="none"/>
                </svg>
            </div>

            <h1 class="login-title">Device Verification</h1>
            <p class="login-subtitle">
                Your device must be verified before accessing the mock tests.
                New devices require one-time admin approval.
            </p>

            <?php if ($expired): ?>
            <div class="notice-box error">
                <span class="notice-icon">⏰</span>
                <span>Your device access has expired. Please contact an admin for reactivation.</span>
            </div>
            <?php else: ?>
            <div class="notice-box" id="noticeBox">
                <span class="notice-icon">🔐</span>
                <span>Checking your device… please wait.</span>
            </div>
            <?php endif; ?>

            <!-- Login button (hidden while auto-checking) -->
            <button class="btn-login" id="loginBtn" onclick="handleLogin()" style="display:none">
                <span class="btn-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                </span>
                <span class="spinner"></span>
                <span class="btn-text">Identify My Device</span>
            </button>

            <div class="login-footer">🔒 Secured by Device Authentication</div>
        </div>
    </div>

    <script>
        const DEFAULT_THEME = '<?= e($defaultTheme) ?>';
        const DEFAULT_COLOR = '<?= e($themeColor) ?>';
    </script>
    <script src="assets/js/theme.js"></script>
    <script>
        const noticeBox = document.getElementById('noticeBox');
        const loginBtn  = document.getElementById('loginBtn');

        function showToast(message, type = 'info', duration = 4000) {
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('removing');
                toast.addEventListener('animationend', () => toast.remove());
            }, duration);
        }

        function setNotice(html, type = '') {
            if (!noticeBox) return;
            noticeBox.className = 'notice-box' + (type ? ' ' + type : '');
            noticeBox.innerHTML = html;
            noticeBox.style.display = 'flex';
        }

        function showLoginBtn() {
            if (loginBtn) loginBtn.style.display = 'flex';
        }

        function setBtnLoading(loading) {
            if (!loginBtn) return;
            loginBtn.disabled = loading;
            loginBtn.classList.toggle('loading', loading);
        }

        async function getOrCreateDeviceToken() {
            let token = localStorage.getItem('device_token');
            if (!token) {
                const res = await fetch('api/device/generate-token.php');
                if (!res.ok) throw new Error('Token generation failed');
                const data = await res.json();
                token = data.device_token;
                localStorage.setItem('device_token', token);
            }
            return token;
        }

        async function checkAutoLogin() {
            const token = localStorage.getItem('device_token');
            if (!token) {
                setNotice('<span class="notice-icon">🔐</span><span>Press the button below to identify your device.</span>');
                showLoginBtn();
                return;
            }

            setNotice('<span class="notice-icon">⏳</span><span>Verifying your device…</span>');

            try {
                const res = await fetch('api/device/verify.php', {
                    headers: { 'X-Device-Token': token }
                });

                if (res.ok) {
                    setNotice('<span class="notice-icon">✅</span><span>Device verified! Redirecting…</span>');
                    setTimeout(() => { window.location.href = 'index.php'; }, 600);
                } else if (res.status === 403) {
                    setNotice(
                        '<span class="notice-icon">⏳</span><span>Your device is pending admin approval. Please check back later.</span>',
                        'warning'
                    );
                } else if (res.status === 401) {
                    // Device exists but not logged in — auto-login silently
                    setNotice('<span class="notice-icon">⏳</span><span>Restoring your session…</span>');
                    const loginRes = await fetch('api/device/login.php', {
                        headers: { 'X-Device-Token': token }
                    });
                    if (loginRes.ok) {
                        setNotice('<span class="notice-icon">✅</span><span>Welcome back! Redirecting…</span>');
                        setTimeout(() => { window.location.href = 'index.php'; }, 600);
                    } else {
                        setNotice('<span class="notice-icon">🔐</span><span>Your device is recognized. Press the button to log in.</span>');
                        showLoginBtn();
                    }
                } else {
                    // Token not recognized — clear it and offer fresh registration
                    localStorage.removeItem('device_token');
                    setNotice('<span class="notice-icon">🔐</span><span>Press the button below to identify your device.</span>');
                    showLoginBtn();
                }
            } catch (err) {
                setNotice('<span class="notice-icon">⚠️</span><span>Connection error. Please refresh the page.</span>', 'error');
            }
        }

        async function handleLogin() {
            setBtnLoading(true);

            try {
                const token = await getOrCreateDeviceToken();

                const res = await fetch('api/device/login.php', {
                    headers: { 'X-Device-Token': token }
                });

                if (res.ok) {
                    setNotice('<span class="notice-icon">✅</span><span>Device verified! Redirecting…</span>');
                    loginBtn.style.display = 'none';
                    setTimeout(() => { window.location.href = 'index.php'; }, 600);
                } else if (res.status === 403) {
                    setNotice(
                        '<span class="notice-icon">⏳</span><span>Your device is registered and awaiting admin approval. <a href="device-auth/public/admin/login.php" style="color:var(--primary);text-decoration:underline;">Go to admin panel</a> to approve it.</span>',
                        'warning'
                    );
                    setBtnLoading(false);
                } else if (res.status === 404) {
                    localStorage.removeItem('device_token');
                    showToast('Device not recognized. Please try again.', 'error');
                    setBtnLoading(false);
                } else if (res.status === 429) {
                    showToast('Too many attempts. Please wait a moment.', 'warning');
                    setBtnLoading(false);
                } else {
                    showToast('Server error. Please try again.', 'error');
                    setBtnLoading(false);
                }
            } catch (err) {
                showToast('Connection error. Please try again.', 'error');
                setBtnLoading(false);
            }
        }

        <?php if (!$expired): ?>
        checkAutoLogin();
        <?php else: ?>
        if (noticeBox) noticeBox.style.display = 'flex';
        <?php endif; ?>
    </script>
</body>
</html>
