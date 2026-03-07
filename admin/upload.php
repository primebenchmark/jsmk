<?php
require_once __DIR__ . '/../config.php';
requireAdmin();

$settings = getSettings();

// Handle download
if (isset($_GET['download'])) {
    $testId = basename($_GET['download']);
    $file = TESTS_PATH . '/' . $testId . '.json';
    if (file_exists($file)) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $testId . '.json"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

$msg = '';
$msgType = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['json_file'])) {
    $file = $_FILES['json_file'];
    $testName = trim($_POST['test_name'] ?? '');
    $testColor = $_POST['test_color'] ?? '#6C5CE7';
    $testDescription = trim($_POST['test_description'] ?? '');
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $content = file_get_contents($file['tmp_name']);
        $data = json_decode($content, true);
        
        if ($data === null) {
            $msg = 'Invalid JSON format.';
            $msgType = 'error';
        } else {
            // Generate test ID from filename
            $testId = pathinfo($file['name'], PATHINFO_FILENAME);
            $testId = preg_replace('/[^a-zA-Z0-9_-]/', '', $testId);
            if (empty($testId)) $testId = 'test_' . time();
            
            // Save JSON file
            saveTestData($testId, $data);
            
            // Save test settings
            if (empty($testName)) $testName = ucfirst($testId);
            
            // Detect sections
            $sectionNums = [];
            foreach ($data as $q) {
                $sectionNums[$q['section']] = true;
            }
            $sectionNames = [];
            foreach (array_keys($sectionNums) as $num) {
                $sectionNames[$num] = "Section $num";
            }
            
            $settings['tests'][$testId] = [
                'name' => $testName,
                'description' => $testDescription,
                'color' => $testColor,
                'sections' => $sectionNames,
                'audio_limit' => $settings['default_audio_limit'] ?? 2,
            ];
            saveSettings($settings);
            
            $msg = "Test \"{$testName}\" uploaded successfully. (" . count($data) . " questions)";
            $msgType = 'success';
        }
    } else {
        $msg = 'File upload failed.';
        $msgType = 'error';
    }
}

$tests = getTests();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload — Admin</title>
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


        <!-- Mobile sidebar overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <main class="admin-main">
            <!-- Mobile header -->
            <div class="mobile-header">
                <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">☰</button>
                <h2>Upload</h2>
            </div>

            <div class="admin-header">
                <h1>Upload Test</h1>
                <p>Upload a JSON file to create a new test</p>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType ?>"><?= e($msg) ?></div>
            <?php endif; ?>

            <!-- Upload Form -->
            <div class="admin-section">
                <h2>Upload JSON File</h2>
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="test_name">Test Name</label>
                            <input type="text" id="test_name" name="test_name" 
                                   placeholder="e.g. Simulation JFT-2">
                        </div>
                        <div class="form-group" style="max-width: 150px;">
                            <label for="test_color">Theme Color</label>
                            <input type="color" id="test_color" name="test_color" 
                                   value="#6C5CE7" class="color-input">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="test_description">Description</label>
                        <textarea id="test_description" name="test_description" rows="3" 
                                  placeholder="Enter a description for this mock test..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="json_file">JSON File</label>
                        <div class="file-upload-area" id="dropZone">
                            <input type="file" id="json_file" name="json_file" accept=".json" required>
                            <div class="file-upload-content">
                                <span class="upload-icon">📁</span>
                                <p>Drag & drop a file, or click to select</p>
                                <p class="file-hint">.json files only</p>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        📤 Upload
                    </button>
                </form>
            </div>

            <!-- Download Section -->
            <div class="admin-section">
                <h2>Download Tests</h2>
                <?php if (empty($tests)): ?>
                    <div class="empty-state"><p>No tests to download.</p></div>
                <?php else: ?>
                    <div class="download-list">
                        <?php foreach ($tests as $test): ?>
                        <div class="download-item">
                            <div class="download-info">
                                <div class="color-dot" style="background: <?= e($test['color']) ?>;"></div>
                                <strong><?= e($test['name']) ?></strong>
                                <span class="download-meta"><?= $test['questions'] ?> questions</span>
                            </div>
                            <a href="upload.php?download=<?= urlencode($test['id']) ?>" class="btn btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.8rem;">
                                📥 Download
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        // Drag & drop
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('json_file');
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
            }
        });
    </script>
</body>
</html>
