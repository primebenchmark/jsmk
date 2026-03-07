<?php
require_once __DIR__ . '/../config.php';
requireAdmin();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$settings = getSettings();
$tests = getTests();
$totalQuestions = array_sum(array_column($tests, 'questions'));

// Get submission count from DB
try {
    $db = getDb();
    $count = $db->query("SELECT COUNT(*) FROM analytics")->fetchColumn();
} catch (Exception $e) {
    $count = '—';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — JFT Mock Test</title>
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
                <h2>Dashboard</h2>
            </div>

            <div class="admin-header">
                <h1>Dashboard</h1>
                <p>Admin panel overview</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #6C5CE7, #A29BFE);">📝</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= count($tests) ?></div>
                        <div class="stat-label">Tests</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00CEC9, #81ECEC);">❓</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $totalQuestions ?></div>
                        <div class="stat-label">Total Questions</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #FD79A8, #FDCB6E);">🎧</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $settings['default_audio_limit'] ?? 2 ?></div>
                        <div class="stat-label">Audio Limit</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00B894, #55EFC4);">📬</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $count ?></div>
                        <div class="stat-label">Submissions</div>
                    </div>
                </div>
            </div>

            <div class="admin-section">
                <div class="section-header">
                    <h2>Tests Overview</h2>
                    <a href="upload.php" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.85rem;">+ New Test</a>
                </div>

                <?php if (empty($tests)): ?>
                    <div class="empty-state">
                        <p>📝 No tests yet</p>
                        <p>Upload a JSON file to create a test.</p>
                    </div>
                <?php else: ?>
                    <div class="tests-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Color</th>
                                    <th>Test Name</th>
                                    <th>Questions</th>
                                    <th>Sections</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tests as $test): ?>
                                <tr>
                                    <td><div class="color-dot" style="background: <?= e($test['color']) ?>;"></div></td>
                                    <td><strong><?= e($test['name']) ?></strong></td>
                                    <td><?= $test['questions'] ?></td>
                                    <td><?= $test['sections'] ?></td>
                                    <td class="actions-cell">
                                        <a href="edit.php?id=<?= urlencode($test['id']) ?>" class="action-btn edit" title="Edit">✏️</a>
                                        <a href="../quiz.php?id=<?= urlencode($test['id']) ?>&preview=1" class="action-btn preview" title="Preview" target="_blank">👁️</a>
                                        <a href="upload.php?download=<?= urlencode($test['id']) ?>" class="action-btn download" title="Download">📥</a>
                                        <a href="tests.php?delete=<?= urlencode($test['id']) ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this test?')">🗑️</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
