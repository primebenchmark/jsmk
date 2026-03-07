<?php
require_once __DIR__ . '/../config.php';
requireAdmin();

try {
    $db = getDb();
    $stmt = $db->query("SELECT * FROM analytics ORDER BY submitted_at DESC");
    $analytics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Per-test aggregates
    $byTest = [];
    foreach ($analytics as $row) {
        $tid = $row['test_id'];
        if (!isset($byTest[$tid])) {
            $byTest[$tid] = ['count' => 0, 'totalPct' => 0];
        }
        $byTest[$tid]['count']++;
        $byTest[$tid]['totalPct'] += $row['score_percent'];
    }
} catch (Exception $e) {
    $analytics = [];
    $byTest = [];
}

$tests = getTests();
$settings = getSettings();
$testNames = [];
$testColors = [];
foreach ($tests as $t) {
    $testNames[$t['id']] = $t['name'];
    $testColors[$t['id']] = $t['color'];
}

$totalSubmissions = count($analytics);
$avgScore = $totalSubmissions > 0
    ? round(array_sum(array_column($analytics, 'score_percent')) / $totalSubmissions)
    : 0;
$highScore = $totalSubmissions > 0 ? max(array_column($analytics, 'score_percent')) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics — Admin</title>
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
                <h2>Analytics</h2>
            </div>

            <div class="admin-header">
                <h1>📈 Analytics</h1>
                <p>Student mock test submissions and performance overview</p>
            </div>

            <!-- Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #6C5CE7, #A29BFE);">👥</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $totalSubmissions ?></div>
                        <div class="stat-label">Total Submissions</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00CEC9, #81ECEC);">📊</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $avgScore ?>%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #FD79A8, #FDCB6E);">🏆</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $highScore ?>%</div>
                        <div class="stat-label">Top Score</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00B894, #55EFC4);">📝</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= count($byTest) ?></div>
                        <div class="stat-label">Tests Attempted</div>
                    </div>
                </div>
            </div>

            <!-- Per-test Summary -->
            <?php if (!empty($byTest)): ?>
            <div class="admin-section">
                <h2>Per-Test Summary</h2>
                <div class="tests-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Test</th>
                                <th>Submissions</th>
                                <th>Avg Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byTest as $tid => $stats): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div class="color-dot" style="background: <?= e($testColors[$tid] ?? '#6C5CE7') ?>"></div>
                                        <strong><?= e($testNames[$tid] ?? $tid) ?></strong>
                                    </div>
                                </td>
                                <td><?= $stats['count'] ?></td>
                                <td>
                                    <?php $avg = round($stats['totalPct'] / $stats['count']); ?>
                                    <strong style="color: <?= $avg >= 60 ? 'var(--correct)' : 'var(--incorrect)' ?>"><?= $avg ?>%</strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Full Submissions Table -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>All Submissions</h2>
                </div>

                <?php if (empty($analytics)): ?>
                    <div class="empty-state">
                        <p>📭 No submissions yet.</p>
                        <p style="font-size: 0.85rem;">When students complete a test, their results will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="tests-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Device Info</th>
                                    <th>Test</th>
                                    <th>Score</th>
                                    <th>Date &amp; Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($analytics as $row): ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 0.8rem;">#<?= $row['id'] ?></td>
                                    <td style="font-size: 0.8rem; max-width: 200px; word-break: break-all;"><?= e($row['device_info'] ?? 'Unknown') ?></td>
                                    <td><?= e($testNames[$row['test_id']] ?? $row['test_id']) ?></td>
                                    <td>
                                        <strong style="color: <?= $row['score_percent'] >= 60 ? 'var(--correct)' : 'var(--incorrect)' ?>">
                                            <?= $row['score_percent'] ?>%
                                        </strong>
                                        <br>
                                        <small style="color: var(--text-muted)"><?= $row['correct_count'] ?> / <?= $row['total_questions'] ?> correct</small>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--text-secondary);"><?= date('M j, Y · H:i', strtotime($row['submitted_at'])) ?></td>
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
