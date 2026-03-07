<?php
require_once __DIR__ . '/../config.php';
requireAdmin();

$testId = $_GET['id'] ?? '';
$testData = getTestData($testId);
if (!$testData) {
    header('Location: tests.php');
    exit;
}

$settings = getSettings();
$testConfig = $settings['tests'][$testId] ?? [];
$testName = $testConfig['name'] ?? ucfirst($testId);
$msg = '';

// Handle saves
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_visual') {
        // Rebuild questions from POST
        $newData = [];
        $ids = $_POST['q_id'] ?? [];
        $sections = $_POST['q_section'] ?? [];
        $texts = $_POST['q_text'] ?? [];
        $keyids = $_POST['q_keyid'] ?? [];
        $images = $_POST['q_image'] ?? [];
        $sounds = $_POST['q_sound'] ?? [];
        $feedbacks = $_POST['q_feedback'] ?? [];
        
        for ($i = 0; $i < count($ids); $i++) {
            $answers = [];
            for ($a = 0; $a < 4; $a++) {
                $ansKey = "q_answer_{$i}_{$a}";
                $answers[] = [
                    'id' => $a,
                    'text' => $_POST[$ansKey] ?? '',
                ];
            }
            
            $newData[] = [
                'id' => intval($ids[$i]),
                'section' => intval($sections[$i]),
                'text' => $texts[$i] ?? '',
                'answers' => $answers,
                'keyid' => intval($keyids[$i]),
                'image' => $images[$i] ?? '',
                'sound' => $sounds[$i] ?? '',
                'feedback' => $feedbacks[$i] ?? '',
                'marks' => isset($_POST['q_marks'][$i]) ? floatval($_POST['q_marks'][$i]) : 1,
            ];
        }
        
        saveTestData($testId, $newData);
        $testData = $newData;
        $msg = 'Changes saved. (' . count($newData) . ' questions)';
    }
    
    if ($action === 'save_json') {
        $rawJson = $_POST['raw_json'] ?? '';
        $parsed = json_decode($rawJson, true);
        if ($parsed !== null) {
            saveTestData($testId, $parsed);
            $testData = $parsed;
            $msg = 'JSON saved. (' . count($parsed) . ' questions)';
        } else {
            $msg = 'Invalid JSON format.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit: <?= e($testName) ?> — Admin</title>
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
                <h2>Edit Test</h2>
            </div>

            <div class="admin-header">
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="tests.php" class="btn btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">← Back</a>
                    <div>
                        <h1>📝 Edit: <?= e($testName) ?></h1>
                        <p><?= count($testData) ?> questions</p>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap;">
                    <button onclick="showTab('visual')" class="btn btn-primary tab-btn active" id="tab-visual" style="padding: 0.5rem 1.25rem; font-size: 0.85rem;">Visual Editor</button>
                    <button onclick="showTab('json')" class="btn btn-secondary tab-btn" id="tab-json" style="padding: 0.5rem 1.25rem; font-size: 0.85rem;">JSON Editor</button>
                    <a href="../quiz.php?id=<?= urlencode($testId) ?>&preview=1" class="btn btn-secondary" style="padding: 0.5rem 1.25rem; font-size: 0.85rem;" target="_blank">👁️ Preview</a>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-success"><?= e($msg) ?></div>
            <?php endif; ?>

            <!-- Visual Editor -->
            <div id="visual-editor" class="editor-panel">
                <form method="POST" id="visualForm">
                    <input type="hidden" name="action" value="save_visual">
                    
                    <?php foreach ($testData as $idx => $q): ?>
                    <div class="edit-question-card" id="edit-q-<?= $idx ?>">
                        <div class="edit-q-header">
                            <span class="question-number"><?= $q['id'] ?></span>
                            <span class="edit-section-badge">Section <?= $q['section'] ?></span>
                            <button type="button" class="collapse-btn" onclick="toggleQuestion(<?= $idx ?>)">▼</button>
                        </div>
                        
                        <div class="edit-q-body" id="edit-q-body-<?= $idx ?>">
                            <input type="hidden" name="q_id[]" value="<?= $q['id'] ?>">
                            
                            <div class="form-row">
                                <div class="form-group" style="max-width: 100px;">
                                    <label>Section</label>
                                    <input type="number" name="q_section[]" value="<?= $q['section'] ?>" min="1">
                                </div>
                                <div class="form-group" style="max-width: 100px;">
                                    <label>Marks</label>
                                    <input type="number" name="q_marks[]" value="<?= isset($q['marks']) ? $q['marks'] : 1 ?>" step="0.5" min="0">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <label>Image Path</label>
                                    <input type="text" name="q_image[]" value="<?= e($q['image']) ?>" placeholder="e.g. Simulation JFT-1/1.png">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <label>Audio Path</label>
                                    <input type="text" name="q_sound[]" value="<?= e($q['sound']) ?>" placeholder="e.g. Simulation JFT-1/31.mp3">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Question Text (HTML allowed)</label>
                                <textarea name="q_text[]" rows="3" class="code-textarea"><?= e($q['text']) ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Answer Options (select ✓ for correct answer)</label>
                                <div class="edit-answers">
                                    <?php foreach ($q['answers'] as $a): ?>
                                    <div class="edit-answer-row">
                                        <input type="radio" 
                                               name="q_keyid_radio_<?= $idx ?>" 
                                               value="<?= $a['id'] ?>"
                                               <?= $a['id'] === $q['keyid'] ? 'checked' : '' ?>>
                                        <span class="answer-letter"><?= chr(65 + $a['id']) ?></span>
                                        <input type="text" name="q_answer_<?= $idx ?>_<?= $a['id'] ?>" value="<?= e($a['text']) ?>">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="q_keyid[]" value="<?= $q['keyid'] ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Feedback</label>
                                <textarea name="q_feedback[]" rows="2"><?= e($q['feedback']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="edit-actions">
                        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- JSON Editor -->
            <div id="json-editor" class="editor-panel" style="display: none;">
                <form method="POST">
                    <input type="hidden" name="action" value="save_json">
                    <div class="form-group">
                        <label>Raw JSON</label>
                        <textarea name="raw_json" id="rawJsonEditor" rows="30" class="code-textarea" spellcheck="false"><?= e(json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></textarea>
                    </div>
                    <div class="edit-actions">
                        <button type="button" class="btn btn-secondary" onclick="formatJson()">🔧 Format</button>
                        <button type="submit" class="btn btn-primary">💾 Save JSON</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        function showTab(tab) {
            document.getElementById('visual-editor').style.display = tab === 'visual' ? 'block' : 'none';
            document.getElementById('json-editor').style.display = tab === 'json' ? 'block' : 'none';
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            document.getElementById('tab-' + tab).className = 'btn btn-primary tab-btn active';
            const other = tab === 'visual' ? 'json' : 'visual';
            document.getElementById('tab-' + other).className = 'btn btn-secondary tab-btn';
        }
        
        function toggleQuestion(idx) {
            const body = document.getElementById('edit-q-body-' + idx);
            const btn = body.previousElementSibling.querySelector('.collapse-btn');
            if (body.style.display === 'none') {
                body.style.display = 'block';
                btn.textContent = '▼';
            } else {
                body.style.display = 'none';
                btn.textContent = '▶';
            }
        }
        
        function formatJson() {
            const editor = document.getElementById('rawJsonEditor');
            try {
                const parsed = JSON.parse(editor.value);
                editor.value = JSON.stringify(parsed, null, 2);
            } catch (e) {
                alert('Invalid JSON format: ' + e.message);
            }
        }
        
        // Fix radio button to hidden field sync
        document.querySelectorAll('[name^="q_keyid_radio_"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const idx = this.name.replace('q_keyid_radio_', '');
                const hiddens = document.querySelectorAll('[name="q_keyid[]"]');
                if (hiddens[idx]) {
                    hiddens[idx].value = this.value;
                }
            });
        });
    </script>
</body>
</html>
