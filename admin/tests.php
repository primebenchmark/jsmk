<?php
require_once __DIR__ . '/../config.php';
requireAdmin();

$settings = getSettings();

// Handle test deletion
if (isset($_GET['delete'])) {
    $testId = basename($_GET['delete']);
    $file = TESTS_PATH . '/' . $testId . '.json';
    if (file_exists($file)) {
        unlink($file);
        if (isset($settings['tests'][$testId])) {
            unset($settings['tests'][$testId]);
            saveSettings($settings);
        }
    }
    header('Location: tests.php?msg=deleted');
    exit;
}

$tests = getTests();
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Management — Admin</title>
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

    <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()" title="Toggle Theme">
        <span class="theme-toggle-icon" id="themeIcon">🌙</span>
    </button>

    <script>
        const DEFAULT_THEME = '<?= e($settings['default_theme'] ?? 'light') ?>';
        const DEFAULT_COLOR = '<?= e($settings['theme_color'] ?? 'purple') ?>';
    </script>

    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>


        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <main class="admin-main">
            <div class="mobile-header">
                <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">☰</button>
                <h2>Tests</h2>
            </div>

            <div class="admin-header">
                <h1>Test Management</h1>
                <p>Add, edit, delete, and preview tests</p>
            </div>

            <?php if ($msg === 'deleted'): ?>
                <div class="alert alert-success">Test has been deleted.</div>
            <?php endif; ?>

            <div class="admin-section">
                <div class="section-header">
                    <h2>All Tests</h2>
                    <a href="upload.php" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.85rem;">+ Upload JSON</a>
                </div>

                <?php if (empty($tests)): ?>
                    <div class="empty-state"><p>📝 No tests found</p></div>
                <?php else: ?>
                    <div class="test-management-grid">
                        <?php foreach ($tests as $test): ?>
                        <div class="admin-test-card" style="--card-color: <?= e($test['color']) ?>">
                            <div class="admin-test-header">
                                <div class="color-dot" style="background: <?= e($test['color']) ?>; width: 12px; height: 12px;"></div>
                                <h3><?= e($test['name']) ?></h3>
                                <?php if (!empty($test['category'])): ?>
                                    <span style="font-size: 0.72rem; color: var(--text-muted); background: var(--glass); padding: 0.15rem 0.5rem; border-radius: 6px;"><?= e($test['category']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($test['description'])): ?>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;"><?= e($test['description']) ?></p>
                            <?php endif; ?>
                            <div class="admin-test-meta">
                                <span>📊 <?= $test['questions'] ?> Questions</span>
                                <span>📋 <?= $test['sections'] ?> Sections</span>
                                <span>🎧 <?= $test['audio_limit'] ?>x plays</span>
                            </div>
                            <div class="admin-test-sections">
                                <?php foreach ($test['section_names'] as $num => $name): ?>
                                    <span class="section-badge"><?= e($name) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="admin-test-actions">
                                <a href="edit.php?id=<?= urlencode($test['id']) ?>" class="btn btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.8rem;">✏️ Edit</a>
                                <a href="../quiz.php?id=<?= urlencode($test['id']) ?>&preview=1" class="btn btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.8rem;" target="_blank">👁️ Preview</a>
                                <a href="upload.php?download=<?= urlencode($test['id']) ?>" class="btn btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.8rem;">📥 Download</a>
                                <a href="settings.php" class="btn btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.8rem;">⚙️ Settings</a>
                                <a href="tests.php?delete=<?= urlencode($test['id']) ?>" class="btn btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.8rem; border-color: var(--incorrect);" onclick="return confirm('Delete &quot;<?= e($test['name']) ?>&quot;?')">🗑️ Delete</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
