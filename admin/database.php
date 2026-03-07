<?php
require_once __DIR__ . '/../config.php';
requireAdmin();

$settings = getSettings();
$msg = $_GET['msg'] ?? '';
$msgType = $_GET['msgType'] ?? 'success';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management — JFT Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>:root { --floating-btn-size: <?= e(intval($settings['floating_btn_size'] ?? 40)) ?>px; }</style>
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
    <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()" title="Toggle Theme">
        <span class="theme-toggle-icon" id="themeIcon">🌙</span>
    </button>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        <main class="admin-main">
            <div class="mobile-header">
                <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">☰</button>
                <h2>Import / Export Database</h2>
            </div>
            <div class="admin-header">
                <h1>Import / Export Database</h1>
                <p>Manage backups of your tests, settings, and student analytics.</p>
            </div>
            
            <?php if ($msg): ?>
                <div class="alert alert-<?= e($msgType) ?>"><?= e($msg) ?></div>
            <?php endif; ?>

            <div class="admin-section">
                <h2>Database Backup System</h2>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">
                    Download all your tests, settings, and student analytics in one package, or upload an existing backup to restore.
                </p>
                <div class="form-row" style="align-items: center;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <a href="backup.php?action=export" class="btn btn-primary" style="text-decoration: none; display: inline-block;">
                            📦 Export Full Backup
                        </a>
                        <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.5rem;">Downloads a .tar.gz containing all tests &amp; settings.</p>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <form method="POST" action="backup.php" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:0.5rem;">
                            <input type="hidden" name="action" value="import">
                            <div style="display:flex; gap:0.5rem; align-items:center;">
                                <input type="file" name="backup_zip" accept=".zip, .tar, .tar.gz, .gz" required style="font-size:0.85rem;">
                                <button type="submit" class="btn btn-secondary" style="padding: 0.4rem 1rem;">📥 Restore</button>
                            </div>
                            <p style="font-size:0.75rem; color:#e74c3c;">Warning: Importing a backup overwrites all current configuration and tests data!</p>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
