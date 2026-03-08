<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_guard.php';
requireDeviceAuth();

$settings = getSettings();
$testId = $_GET['id'] ?? '';
$isPreview = isset($_GET['preview']) && isAdminLoggedIn();
$testData = getTestData($testId);

if (!$testData) {
    header('Location: index.php');
    exit;
}

$testConfig = $settings['tests'][$testId] ?? [];
$testName = $testConfig['name'] ?? ucfirst($testId);
$testDescription = $testConfig['description'] ?? '';
$testColor = $testConfig['color'] ?? '#6C5CE7';
$sectionNames = $testConfig['sections'] ?? [];
$audioLimit = $testConfig['audio_limit'] ?? ($settings['default_audio_limit'] ?? 2);
$contentProtection = $settings['content_protection'] ?? true;
$siteName = $settings['site_name'] ?? 'JFT Mock Test';

$globalCompulsory = $settings['compulsory_questions'] ?? false;
$testCompulsory = $testConfig['compulsory'] ?? 'global';
$isCompulsory = ($testCompulsory === 'yes') || ($testCompulsory === 'global' && $globalCompulsory);

$fontFamily = $settings['font_family'] ?? '';
$testFontFamily = $settings['test_font_family'] ?? '';
$fontSize = $settings['font_size'] ?? '';
$testHeaderSize = $settings['test_header_size'] ?? '';
$testDescSize = $settings['test_desc_size'] ?? '';
$testUiSize = $settings['test_ui_size'] ?? '';
$defaultTheme = $settings['default_theme'] ?? 'light';
$themeColor = $settings['theme_color'] ?? 'purple';

$showTestHeader = $settings['show_test_header'] ?? true;
$showFooter     = $settings['show_footer'] ?? true;

// Group questions by section
$sections = [];
foreach ($testData as $q) {
    $sec = $q['section'];
    if (!isset($sections[$sec])) {
        $sections[$sec] = [];
    }
    $sections[$sec][] = $q;
}
ksort($sections);

// Calculate max possible marks
$totalMarks = 0;
foreach ($testData as $q) {
    $totalMarks += isset($q['marks']) ? (float)$q['marks'] : 1;
}
$sectionKeys = array_keys($sections);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($testName) ?> — <?= e($siteName) ?>">
    <title><?= e($testName) ?> — <?= e($siteName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="<?= getGoogleFontsLink($fontFamily) ?>">
    <?php if ($testFontFamily && $testFontFamily !== $fontFamily): ?>
    <link rel="stylesheet" href="<?= getGoogleFontsLink($testFontFamily) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
    <style>
        :root {
            --test-color: <?= e($testColor) ?>;
            --bg-gradient-1: <?= e($testColor) ?>25;
            --bg-gradient-2: <?= e($testColor) ?>15;
            <?= $fontFamily ? '--font-family: ' . htmlspecialchars($fontFamily, ENT_COMPAT) . ';' : '' ?>
            <?= $testFontFamily ? '--test-font-family: ' . htmlspecialchars($testFontFamily, ENT_COMPAT) . ';' : '' ?>
            <?= $fontSize ? '--font-size: ' . e($fontSize) . ';' : '' ?>
            <?= $testHeaderSize ? '--test-header-size: ' . e($testHeaderSize) . ';' : '' ?>
            <?= $testDescSize ? '--test-desc-size: ' . e($testDescSize) . ';' : '' ?>
            <?= $testUiSize ? '--test-ui-size: ' . e($testUiSize) . ';' : '' ?>
            --floating-btn-size: <?= e(intval($settings['floating_btn_size'] ?? 40)) ?>px;
        }
        <?php if ($fontFamily || $fontSize): ?>
        body, .question-text, .answer-label, .site-header h1, .btn {
            <?= $fontFamily ? 'font-family: var(--font-family) !important;' : '' ?>
            <?= $fontSize ? 'font-size: var(--font-size) !important;' : '' ?>
        }
        <?php endif; ?>
        <?php if ($testFontFamily): ?>
        .question-text, .answer-label, .question-feedback {
            font-family: var(--test-font-family) !important;
        }
        <?php endif; ?>
        <?php if ($testHeaderSize): ?>
        .test-header h1 { font-size: var(--test-header-size) !important; }
        <?php endif; ?>
        <?php if ($testDescSize): ?>
        .test-header .test-description { font-size: var(--test-desc-size) !important; }
        <?php endif; ?>
        <?php if ($testUiSize): ?>
        .section-tab, .question-number, .test-header .btn-secondary, .btn, .answer-label { font-size: var(--test-ui-size) !important; }
        <?php endif; ?>

        /* Custom Modal */
        #customModal {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.2s;
        }
        #customModal.active { opacity: 1; pointer-events: auto; }
        .modal-box {
            background: var(--bg-card); color: var(--text-primary);
            padding: 1.5rem 2rem; border-radius: var(--radius-lg);
            max-width: 400px; width: 90%; text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            transform: translateY(20px); transition: transform 0.2s ease;
            border: 1px solid var(--border);
        }
        #customModal.active .modal-box { transform: translateY(0); }
        .modal-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.6rem; color: var(--text-primary); }
        .modal-body { font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 1.5rem; white-space: pre-wrap; line-height: 1.5; }
    </style>
    <script>
        (function(){
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

    <?php if ($isPreview): ?>
    <div class="preview-banner">
        ⚠️ Preview Mode — Admin Preview
        <a href="admin/tests.php" style="color: inherit; margin-left: 1rem; text-decoration: underline;">← Back to Admin</a>
    </div>
    <?php endif; ?>

    <div class="test-container fade-in">
        <!-- Test Header -->
        <?php if ($showTestHeader): ?>
        <div class="test-header">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <a href="index.php" class="btn-secondary" style="padding: 0.4rem 0.9rem; border-radius: 8px; text-decoration: none; font-size: 0.8rem; border: 1px solid var(--border); color: var(--text-secondary);">← Back</a>
            </div>
            <h1><?= e($testName) ?></h1>
            <?php if (!empty($testDescription)): ?>
                <div class="test-description"><?= nl2br(e($testDescription)) ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Score Summary (shown at top after submit) -->
        <div class="score-summary" id="scoreSummary">
            <span class="score-big" id="scoreSummaryText">0 / <?= $totalMarks ?></span>
            <span class="score-detail" id="scoreSummaryDetail">marks</span>
        </div>


        <!-- Section Tabs -->
        <div class="section-tabs" id="sectionTabs">
            <?php foreach ($sections as $secNum => $secQuestions): ?>
                <button class="section-tab <?= $secNum === $sectionKeys[0] ? 'active' : '' ?>" 
                        data-section="<?= $secNum ?>" 
                        id="sectionTab-<?= $secNum ?>"
                        onclick="navigateToSection(<?= $secNum ?>)">
                    <?= e($sectionNames[$secNum] ?? "Section $secNum") ?>
                    <span class="tab-count"><?= count($secQuestions) ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Questions -->
        <div id="questionsContainer">
            <?php foreach ($testData as $idx => $question): ?>
                <div class="question-card" 
                     data-section="<?= $question['section'] ?>" 
                     data-question-id="<?= $question['id'] ?>"
                     id="question-<?= $question['id'] ?>"
                     style="<?= $question['section'] != $sectionKeys[0] ? 'display: none;' : '' ?>">
                    
                    <div class="question-number"><?= $question['id'] ?></div>
                    
                    <div class="question-text"><?= $question['text'] ?></div>

                    <?php if (!empty($question['image'])): ?>
                        <div class="question-image-wrapper" id="img-wrap-<?= $question['id'] ?>">
                            <div class="image-loading" id="img-loading-<?= $question['id'] ?>">
                                <div class="spinner"></div>
                                <span>Loading image...</span>
                            </div>
                            <img 
                                alt="Question <?= $question['id'] ?>"
                                data-src="media/<?= e($question['image']) ?>"
                                data-retries="0"
                                data-max-retries="5"
                                style="display: none;"
                                id="img-<?= $question['id'] ?>"
                            >
                            <div class="image-error" id="img-error-<?= $question['id'] ?>" style="display: none;">
                                <span>⚠️ Failed to load image</span>
                                <button class="image-retry-btn" onclick="retryImage(<?= $question['id'] ?>)">Retry</button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($question['sound'])): ?>
                        <div class="audio-player" id="audio-player-<?= $question['id'] ?>">
                            <button class="audio-play-btn" 
                                    id="audio-btn-<?= $question['id'] ?>"
                                    onclick="toggleAudio(<?= $question['id'] ?>)"
                                    data-playing="false">
                                ▶
                            </button>
                            <div class="audio-info">
                                <div class="audio-progress">
                                    <div class="audio-progress-fill" id="audio-progress-<?= $question['id'] ?>"></div>
                                </div>
                                <div class="audio-meta">
                                    <span class="audio-time" id="audio-time-<?= $question['id'] ?>">0:00 / 0:00</span>
                                    <span class="plays-remaining" id="plays-left-<?= $question['id'] ?>">
                                        <?= $audioLimit ?> plays remaining
                                    </span>
                                </div>
                            </div>
                            <audio 
                                id="audio-<?= $question['id'] ?>"
                                data-src="media/<?= e($question['sound']) ?>"
                                data-plays="0"
                                data-max-plays="<?= $audioLimit ?>"
                                data-retries="0"
                                data-max-retries="5"
                                preload="none"
                            ></audio>
                        </div>
                    <?php endif; ?>

                    <div class="answers-list">
                        <?php foreach ($question['answers'] as $answer): ?>
                            <label class="answer-option" 
                                   id="answer-<?= $question['id'] ?>-<?= $answer['id'] ?>"
                                   onclick="selectAnswer(<?= $question['id'] ?>, <?= $answer['id'] ?>)">
                                <div class="answer-radio"></div>
                                <span class="answer-label"><?= e($answer['text']) ?></span>
                                <input type="radio" 
                                       name="q<?= $question['id'] ?>" 
                                       value="<?= $answer['id'] ?>">
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="question-feedback" id="feedback-<?= $question['id'] ?>">
                        <?= nl2br(e($question['feedback'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Section Navigation -->
        <div class="section-nav" id="sectionNav">
            <button class="btn btn-secondary" id="prevSectionBtn" onclick="prevSection()" style="display: none;">
                ← Previous Section
            </button>
            <button class="btn btn-primary" id="nextSectionBtn" onclick="nextSection()">
                Next Section →
            </button>
        </div>

        <!-- Results Panel -->
        <div class="results-panel" id="resultsPanel">
            <div class="results-score" id="scoreDisplay">0%</div>
            <div class="results-marks" id="marksDisplay">0 / <?= $totalMarks ?> marks</div>
            <div class="results-subtitle" id="scoreSubtitle">Test Results</div>
            <div class="score-breakdown">
                <div class="score-item correct-stat">
                    <div class="score-value" id="correctCount">0</div>
                    <div class="score-label">Correct</div>
                </div>
                <div class="score-item incorrect-stat">
                    <div class="score-value" id="incorrectCount">0</div>
                    <div class="score-label">Incorrect</div>
                </div>
                <div class="score-item">
                    <div class="score-value" id="unansweredCount">0</div>
                    <div class="score-label">Unanswered</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="test-actions" id="testActions">
            <button class="btn btn-primary" id="submitBtn" onclick="submitTest()">
                📝 Submit
            </button>
        </div>
    </div>

    <?php if ($showFooter): ?>
    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.</p>
    </footer>
    <?php endif; ?>

    <!-- Custom Modal -->
    <div id="customModal">
        <div class="modal-box">
            <div id="modalTitle" class="modal-title">Notice</div>
            <div id="modalMessage" class="modal-body"></div>
            <button class="btn btn-primary" onclick="closeModal()">OK</button>
        </div>
    </div>

    <!-- Pass data to JS -->
    <script>
        const TEST_DATA = <?= json_encode($testData, JSON_UNESCAPED_UNICODE) ?>;
        const AUDIO_LIMIT = <?= (int)$audioLimit ?>;
        const TEST_ID = '<?= e($testId) ?>';
        const SECTION_KEYS = <?= json_encode(array_map('intval', $sectionKeys)) ?>;
        const TOTAL_MARKS = <?= $totalMarks ?>;
        const PASS_MARK = <?= intval($testConfig['pass_mark'] ?? 200) ?>;
        const IS_COMPULSORY = <?= $isCompulsory ? 'true' : 'false' ?>;
        const DEFAULT_THEME = '<?= e($defaultTheme) ?>';
        const DEFAULT_COLOR = '<?= e($themeColor) ?>';
        
        function showModal(title, msg) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = msg;
            document.getElementById('customModal').classList.add('active');
        }
        function closeModal() {
            document.getElementById('customModal').classList.remove('active');
        }
    </script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/test.js"></script>

    <?php if ($contentProtection): ?>
    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('copy', e => e.preventDefault());
        document.addEventListener('cut', e => e.preventDefault());
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && ['c','x','u','s','p'].includes(e.key.toLowerCase())) {
                e.preventDefault();
            }
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && ['i','j','c'].includes(e.key.toLowerCase()))) {
                e.preventDefault();
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
