<?php
session_start();

define('BASE_PATH', __DIR__);
define('DATA_PATH', BASE_PATH . '/data');
define('TESTS_PATH', DATA_PATH . '/tests');
define('SETTINGS_FILE', DATA_PATH . '/settings.json');
define('MEDIA_PATH', BASE_PATH . '/media');
define('DB_FILE', DATA_PATH . '/analytics.sqlite');

function getDb() {
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS analytics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        test_id TEXT NOT NULL,
        device_info TEXT,
        score_percent INTEGER,
        correct_count INTEGER,
        total_questions INTEGER,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    // Migrate: add device_info if missing (for existing DBs)
    try { $db->exec("ALTER TABLE analytics ADD COLUMN device_info TEXT"); } catch (Exception $e) {}
    return $db;
}

// Load settings
function getSettings() {
    if (!file_exists(SETTINGS_FILE)) {
        return [];
    }
    return json_decode(file_get_contents(SETTINGS_FILE), true) ?: [];
}

// Save settings
function saveSettings($settings) {
    file_put_contents(SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Get all available tests
function getTests() {
    $tests = [];
    $settings = getSettings();
    $files = glob(TESTS_PATH . '/*.json');
    foreach ($files as $file) {
        $testId = pathinfo($file, PATHINFO_FILENAME);
        $data = json_decode(file_get_contents($file), true);
        $sections = [];
        if ($data) {
            foreach ($data as $q) {
                $sections[$q['section']] = true;
            }
        }
        $testConfig = $settings['tests'][$testId] ?? [];
        $tests[] = [
            'id' => $testId,
            'name' => $testConfig['name'] ?? ucfirst($testId),
            'description' => $testConfig['description'] ?? '',
            'color' => $testConfig['color'] ?? '#6C5CE7',
            'category' => $testConfig['category'] ?? 'Uncategorized',
            'compulsory' => $testConfig['compulsory'] ?? 'global', // global, yes, no
            'questions' => $data ? count($data) : 0,
            'sections' => count($sections),
            'section_names' => $testConfig['sections'] ?? [],
            'audio_limit' => $testConfig['audio_limit'] ?? ($settings['default_audio_limit'] ?? 2),
            'pass_mark' => $testConfig['pass_mark'] ?? 200,
        ];
    }
    return $tests;
}

// Get test data by ID
function getTestData($testId) {
    $file = TESTS_PATH . '/' . basename($testId) . '.json';
    if (!file_exists($file)) {
        return null;
    }
    return json_decode(file_get_contents($file), true);
}

// Save test data
function saveTestData($testId, $data) {
    $file = TESTS_PATH . '/' . basename($testId) . '.json';
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Check admin login
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Require admin login
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Get test setting
function getTestSetting($testId, $key, $default = null) {
    $settings = getSettings();
    return $settings['tests'][$testId][$key] ?? $default;
}

// Sanitize output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Get Google Fonts link based on selected font family
function getGoogleFontsLink($fontFamilyStr) {
    $baseUrl = "https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800";
    if (empty($fontFamilyStr)) {
        return $baseUrl . "&family=Noto+Sans+JP:wght@400;500;700&display=swap";
    }
    
    preg_match("/'([^']+)'/", $fontFamilyStr, $matches);
    $fontName = $matches[1] ?? '';
    
    if ($fontName && $fontName !== 'Inter') {
        $fontNameEscaped = str_replace(' ', '+', $fontName);
        return $baseUrl . "&family={$fontNameEscaped}:wght@400;500;700&display=swap";
    }
    
    return $baseUrl . "&family=Noto+Sans+JP:wght@400;500;700&display=swap";
}
