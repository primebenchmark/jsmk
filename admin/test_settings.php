<?php
require_once __DIR__ . '/../config.php';
requireAdmin();

$settings = getSettings();
$msg = $_GET['msg'] ?? '';
$msgType = $_GET['msgType'] ?? 'success';

// Handle test settings save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_test_settings') {
    $testId = $_POST['test_id'] ?? '';
    if ($testId && isset($settings['tests'][$testId])) {
        $settings['tests'][$testId]['name'] = trim($_POST['test_name'] ?? '');
        $settings['tests'][$testId]['description'] = trim($_POST['test_description'] ?? '');
        $settings['tests'][$testId]['color'] = trim($_POST['test_color'] ?? '#6C5CE7');
        $settings['tests'][$testId]['audio_limit'] = intval($_POST['test_audio_limit'] ?? 2);
        if (isset($_POST['section_names']) && is_array($_POST['section_names'])) {
            foreach ($_POST['section_names'] as $secNum => $secName) {
                $settings['tests'][$testId]['sections'][$secNum] = trim($secName);
            }
        }
        $settings['tests'][$testId]['category'] = trim($_POST['test_category'] ?? '');
        $settings['tests'][$testId]['pass_mark'] = intval($_POST['test_pass_mark'] ?? 200);
        $settings['tests'][$testId]['compulsory'] = $_POST['test_compulsory'] ?? 'global';
        
        saveSettings($settings);
        $msg = 'Settings for ' . $settings['tests'][$testId]['name'] . ' saved!';
        $msgType = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Settings — Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link id="googleFontLink" href="<?= getGoogleFontsLink($settings['font_family'] ?? '') ?>" rel="stylesheet">
    <style>
        :root { --floating-btn-size: <?= e(intval($settings['floating_btn_size'] ?? 40)) ?>px; }
        <?php if (!empty($settings['font_family'])): ?>
        body { font-family: <?= htmlspecialchars($settings['font_family'], ENT_COMPAT) ?> !important; }
        <?php endif; ?>
    </style>
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
                <h2>Test Settings</h2>
            </div>
            <div class="admin-header">
                <h1>Test Settings</h1>
                <p>Update names, descriptions, colors, and specific limits for isolated tests.</p>
            </div>
            <?php if ($msg): ?>
                <div class="alert alert-<?= e($msgType) ?>"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if (!empty($settings['tests'])): ?>
            <div class="admin-section">
                <?php foreach ($settings['tests'] as $testId => $testConfig): ?>
                <form method="POST" class="test-settings-card" style="margin-bottom: 1.5rem;">
                    <input type="hidden" name="action" value="save_test_settings">
                    <input type="hidden" name="test_id" value="<?= e($testId) ?>">
                    
                    <div class="edit-q-header" style="cursor: pointer;" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'">
                        <div class="color-dot" style="background: <?= e($testConfig['color'] ?? '#6C5CE7') ?>; width: 12px; height: 12px;"></div>
                        <strong><?= e($testConfig['name'] ?? $testId) ?></strong>
                        <span class="collapse-btn">▼</span>
                    </div>
                    
                    <div class="edit-q-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Test Name</label>
                                <input type="text" name="test_name" value="<?= e($testConfig['name'] ?? '') ?>">
                            </div>
                            <div class="form-group" style="max-width: 120px;">
                                <label>Color</label>
                                <input type="color" name="test_color" value="<?= e($testConfig['color'] ?? '#6C5CE7') ?>" class="color-input">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="test_audio_limit_<?= e($testId) ?>">
                                    Audio Limit: 
                                    <strong id="testAudioLimitVal_<?= e($testId) ?>"><?= intval($testConfig['audio_limit'] ?? 2) ?></strong>
                                </label>
                                <input type="range" id="test_audio_limit_<?= e($testId) ?>" name="test_audio_limit"
                                       min="1" max="10" step="1"
                                       value="<?= intval($testConfig['audio_limit'] ?? 2) ?>"
                                       oninput="document.getElementById('testAudioLimitVal_<?= e($testId) ?>').textContent = this.value"
                                       style="width:100%; accent-color: var(--primary);">
                                <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">
                                    <span>1</span><span>10</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="test_category">
                                    <option value="">Uncategorized</option>
                                    <?php 
                                    $catList = array_map('trim', explode(',', $settings['categories'] ?? 'JFT-Basic, Skill'));
                                    foreach ($catList as $cat): if(!empty($cat)): 
                                    ?>
                                    <option value="<?= e($cat) ?>" <?= ($testConfig['category'] ?? '') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                                    <?php endif; endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Pass Mark</label>
                                <input type="number" name="test_pass_mark" value="<?= intval($testConfig['pass_mark'] ?? 200) ?>" min="0">
                            </div>
                            <div class="form-group">
                                <label>Compulsory Questions</label>
                                <select name="test_compulsory">
                                    <option value="global" <?= ($testConfig['compulsory'] ?? 'global') === 'global' ? 'selected' : '' ?>>Use Global Setting</option>
                                    <option value="yes" <?= ($testConfig['compulsory'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes, make compulsory</option>
                                    <option value="no" <?= ($testConfig['compulsory'] ?? '') === 'no' ? 'selected' : '' ?>>No, make optional</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="test_description" rows="3" placeholder="Enter a description for this mock test..."><?= e($testConfig['description'] ?? '') ?></textarea>
                        </div>
                        
                        <?php if (!empty($testConfig['sections'])): ?>
                        <div class="form-group">
                            <label>Section Names</label>
                            <?php foreach ($testConfig['sections'] as $secNum => $secName): ?>
                            <div class="form-row" style="margin-bottom: 0.5rem;">
                                <div class="form-group" style="max-width: 80px; margin-bottom: 0;">
                                    <input type="text" value="Section <?= $secNum ?>" disabled style="text-align: center; font-size: 0.8rem;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <input type="text" name="section_names[<?= $secNum ?>]" value="<?= e($secName) ?>">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.85rem;">
                            �� Update Test
                        </button>
                    </div>
                </form>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="alert alert-info">No tests available. Please upload a test first.</div>
            <?php endif; ?>
        </main>
    </div>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
