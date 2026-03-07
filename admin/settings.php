<?php
require_once __DIR__ . '/../config.php';
requireAdmin();

$settings = getSettings();
$msg = $_GET['msg'] ?? '';
$msgType = $_GET['msgType'] ?? 'success';

// Handle settings save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_general') {
        $settings['site_name'] = trim($_POST['site_name'] ?? 'JFT Mock Test');
        $settings['header_title'] = trim($_POST['header_title'] ?? '');
        $settings['admin_password'] = trim($_POST['admin_password'] ?? 'admin123');
        $settings['default_audio_limit'] = intval($_POST['default_audio_limit'] ?? 2);
        $settings['content_protection'] = isset($_POST['content_protection']);
        $settings['compulsory_questions'] = isset($_POST['compulsory_questions']);
        $settings['font_family'] = $_POST['font_family'] ?? '';
        $settings['test_font_family'] = $_POST['test_font_family'] ?? '';
        $settings['font_size'] = !empty($_POST['font_size']) ? intval($_POST['font_size']) . 'px' : '';
        $settings['default_theme'] = $_POST['default_theme'] ?? 'light';
        $settings['theme_color'] = $_POST['theme_color'] ?? 'purple';
        $settings['upcoming_title'] = trim($_POST['upcoming_title'] ?? '');
        $settings['upcoming_date'] = trim($_POST['upcoming_date'] ?? '');
        $settings['categories'] = trim($_POST['categories'] ?? 'JFT-Basic, Skill');
        // Test page settings
        $settings['test_header_size'] = !empty($_POST['test_header_size']) ? intval($_POST['test_header_size']) . 'px' : '';
        $settings['test_desc_size'] = !empty($_POST['test_desc_size']) ? intval($_POST['test_desc_size']) . 'px' : '';
        $settings['test_ui_size'] = !empty($_POST['test_ui_size']) ? intval($_POST['test_ui_size']) . 'px' : '';
        // Index layout settings
        $settings['index_countdown_width'] = intval($_POST['index_countdown_width'] ?? 100) . '%';
        $settings['index_countdown_height'] = intval($_POST['index_countdown_height'] ?? 180) . 'px';
        $settings['index_card_width'] = intval($_POST['index_card_width'] ?? 100) . '%';
        $settings['index_card_height'] = intval($_POST['index_card_height'] ?? 120) . 'px';
        $settings['floating_btn_size'] = intval($_POST['floating_btn_size'] ?? 40);
        $settings['index_card_title_size'] = !empty($_POST['index_card_title_size']) ? intval($_POST['index_card_title_size']) : 16;
        
        $settings['show_index_hero']     = isset($_POST['show_index_hero']);
        $settings['show_index_header']    = isset($_POST['show_index_header']);
        $settings['show_index_countdown'] = isset($_POST['show_index_countdown']);
        $settings['show_index_catalog']   = isset($_POST['show_index_catalog']);
        $settings['show_test_header']     = isset($_POST['show_test_header']);
        $settings['show_footer']          = isset($_POST['show_footer']);
        $settings['show_admin_button']    = isset($_POST['show_admin_button']);
        
        saveSettings($settings);
        $msg = 'Settings saved successfully.';
        $msgType = 'success';
    }
}

$settings = getSettings();
$tests = getTests();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" id="googleFontLink" href="<?= getGoogleFontsLink($settings['font_family'] ?? '') ?>">
    <link rel="stylesheet" id="googleTestFontLink" href="<?= getGoogleFontsLink($settings['test_font_family'] ?? '') ?>">
    <style>
        :root { --floating-btn-size: <?= e(intval($settings['floating_btn_size'] ?? 40)) ?>px; }
        <?php if (!empty($settings['font_family'])): ?>
        body { font-family: <?= htmlspecialchars($settings['font_family'], ENT_COMPAT) ?> !important; }
        <?php endif; ?>
        
        .toggle-switch { position: relative; display: inline-block; width: 42px; height: 22px; flex-shrink: 0; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider-round { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border); transition: .3s; border-radius: 34px; }
        .slider-round:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
        input:checked + .slider-round { background-color: var(--primary); }
        input:checked + .slider-round:before { transform: translateX(20px); }
        .toggle-label { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; cursor: pointer; font-size: 0.9rem; }

        /* Fixed save bar */
        .sticky-save-bar {
            position: fixed;
            bottom: 0;
            left: 260px;
            right: 0;
            z-index: 1000;
            background: var(--bg-card);
            border-top: 1px solid var(--border);
            padding: 0.85rem 2rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.07);
        }

        @media (max-width: 768px) {
            .sticky-save-bar { left: 0; padding: 0.85rem 1rem; }
        }
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
                <h2>Settings</h2>
            </div>

            <div class="admin-header">
                <h1>⚙️ Settings</h1>
                <p>Manage your site configuration</p>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType ?>"><?= e($msg) ?></div>
            <?php endif; ?>

            <!-- General Settings -->
            <div class="admin-section">
                <h2>General Settings</h2>
                <form method="POST" class="settings-form">
                    <input type="hidden" name="action" value="save_general">
                    
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" 
                               value="<?= e($settings['site_name'] ?? 'JFT Mock Test') ?>"
                               placeholder="e.g. JFT Mock Test">
                    </div>
                    
                    <div class="form-group">
                        <label for="header_title">Header Title</label>
                        <input type="text" id="header_title" name="header_title" 
                               value="<?= e($settings['header_title'] ?? '') ?>"
                               placeholder="e.g. Japanese Language Mock Test">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin_password">Admin Password</label>
                            <input type="text" id="admin_password" name="admin_password" 
                                   value="<?= e($settings['admin_password'] ?? 'admin123') ?>">
                        </div>
                        <div class="form-group">
                            <label for="default_audio_limit">
                                Default Audio Limit: 
                                <strong id="audioLimitVal"><?= intval($settings['default_audio_limit'] ?? 2) ?></strong>
                            </label>
                            <input type="range" id="default_audio_limit" name="default_audio_limit"
                                   min="1" max="10" step="1"
                                   value="<?= intval($settings['default_audio_limit'] ?? 2) ?>"
                                   oninput="document.getElementById('audioLimitVal').textContent = this.value"
                                   style="width:100%; accent-color: var(--primary);">
                            <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">
                                <span>1</span><span>10</span>
                            </div>
                        </div>
                    </div>

                    <label class="toggle-label">
                        <label class="toggle-switch">
                            <input type="checkbox" name="content_protection" <?= ($settings['content_protection'] ?? true) ? 'checked' : '' ?>>
                            <span class="slider-round"></span>
                        </label>
                        Enable Content Protection (prevent copy/paste/right-click)
                    </label>

                    <label class="toggle-label" style="margin-top: 0.5rem;">
                        <label class="toggle-switch">
                            <input type="checkbox" name="compulsory_questions" <?= ($settings['compulsory_questions'] ?? false) ? 'checked' : '' ?>>
                            <span class="slider-round"></span>
                        </label>
                        Make all questions compulsory globally by default
                    </label>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="categories">Test Categories (Comma separated)</label>
                        <input type="text" id="categories" name="categories" 
                               value="<?= e($settings['categories'] ?? 'JFT-Basic, Skill') ?>"
                               placeholder="e.g. JLPT N5, JFT-Basic, Skill">
                    </div>

                     <h3 style="margin-top: 2rem; margin-bottom: 1rem; font-size: 1.05rem;">Component Visibility</h3>
                     <p style="color: var(--text-muted); font-size: 0.83rem; margin-bottom: 1rem;">Toggle visibility for major sections across the site.</p>
                     
                     <div class="form-row">
                         <div style="width: 50%;">
                             <label class="toggle-label">
                                 <label class="toggle-switch">
                                     <input type="checkbox" name="show_index_hero" <?= ($settings['show_index_hero'] ?? true) ? 'checked' : '' ?>>
                                     <span class="slider-round"></span>
                                 </label>
                                 Show Index Logo / Hero Image
                             </label>
                         </div>
                         <div style="width: 50%;">
                             <label class="toggle-label">
                                 <label class="toggle-switch">
                                     <input type="checkbox" name="show_index_header" <?= ($settings['show_index_header'] ?? true) ? 'checked' : '' ?>>
                                     <span class="slider-round"></span>
                                 </label>
                                 Show Index Title / Header Text
                             </label>
                         </div>
                     </div>
                     <div class="form-row" style="margin-top:0.5rem">
                         <div style="width: 50%;">
                             <label class="toggle-label">
                                 <label class="toggle-switch">
                                     <input type="checkbox" name="show_index_countdown" <?= ($settings['show_index_countdown'] ?? true) ? 'checked' : '' ?>>
                                     <span class="slider-round"></span>
                                 </label>
                                 Show Index Countdown
                             </label>
                         </div>
                     </div>

                     <div class="form-row" style="margin-top:0.75rem">
                         <div style="width: 50%;">
                             <label class="toggle-label">
                                 <label class="toggle-switch">
                                     <input type="checkbox" name="show_index_catalog" <?= ($settings['show_index_catalog'] ?? true) ? 'checked' : '' ?>>
                                     <span class="slider-round"></span>
                                 </label>
                                 Show Index Test Catalog
                             </label>
                         </div>
                         <div style="width: 50%;">
                             <label class="toggle-label">
                                 <label class="toggle-switch">
                                     <input type="checkbox" name="show_test_header" <?= ($settings['show_test_header'] ?? true) ? 'checked' : '' ?>>
                                     <span class="slider-round"></span>
                                 </label>
                                 Show Test Page Header
                             </label>
                         </div>
                     </div>

                     <div class="form-row" style="margin-top:0.75rem">
                         <div style="width: 50%;">
                             <label class="toggle-label">
                                 <label class="toggle-switch">
                                     <input type="checkbox" name="show_footer" <?= ($settings['show_footer'] ?? true) ? 'checked' : '' ?>>
                                     <span class="slider-round"></span>
                                 </label>
                                 Show Footer (All Pages)
                             </label>
                         </div>
                         <div style="width: 50%;">
                             <label class="toggle-label">
                                 <label class="toggle-switch">
                                     <input type="checkbox" name="show_admin_button" <?= ($settings['show_admin_button'] ?? true) ? 'checked' : '' ?>>
                                     <span class="slider-round"></span>
                                 </label>
                                 Show Floating Admin Button (Index)
                             </label>
                         </div>
                     </div>

                     <h3 style="margin-top: 2rem; margin-bottom: 1rem; font-size: 1.05rem;">Design &amp; Font</h3>
                     <div class="form-row">
                         <div class="form-group">
                             <label for="default_theme">Default Theme Mode</label>
                             <select id="default_theme" name="default_theme" onchange="document.documentElement.setAttribute('data-theme', this.value)">
                                 <option value="light" <?= ($settings['default_theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>Light Mode</option>
                                 <option value="dark" <?= ($settings['default_theme'] ?? 'light') === 'dark' ? 'selected' : '' ?>>Dark Mode</option>
                             </select>
                         </div>
                         <div class="form-group">
                             <label for="theme_color">Theme Color</label>
                             <select id="theme_color" name="theme_color" onchange="if(typeof setThemeColor === 'function') setThemeColor(this.value)">
                                 <?php
                                 $themeColors = [
                                     'purple'     => '🌸 Sakura Purple',
                                     'blue'       => '🌊 Ocean Blue',
                                     'teal'       => '🍃 Teal Green',
                                     'green'      => '🌿 Forest Green',
                                     'orange'     => '🌅 Sunset Orange',
                                     'red'        => '❤️ Cherry Red',
                                     'pink'       => '🎀 Cherry Blossom Pink',
                                     'indigo'     => '💎 Deep Indigo',
                                     'cyan'       => '💎 Cyan Crystal',
                                     'lime'       => '🍋 Fresh Lime',
                                     'amber'      => '🍯 Warm Amber',
                                     'brown'      => '🍂 Autumn Brown',
                                     'slate'      => '🪨 Slate Gray',
                                     'rose'       => '🌹 Rose Garden',
                                     'violet'     => '💜 Royal Violet',
                                     'emerald'    => '💚 Emerald Gem',
                                     'sky'        => '☁️ Sky Blue',
                                     'fuchsia'    => '💗 Vibrant Fuchsia',
                                     'gold'       => '👑 Golden Crown',
                                     'coral'      => '🐠 Coral Reef',
                                 ];
                                 $currentColor = $settings['theme_color'] ?? 'purple';
                                 foreach ($themeColors as $val => $label):
                                 ?>
                                 <option value="<?= e($val) ?>" <?= $currentColor === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                                 <?php endforeach; ?>
                             </select>
                         </div>
                     </div>
                     <div class="form-row">
                         <div class="form-group">
                             <label for="font_family">Global Font Type</label>
                             <select id="font_family" name="font_family" onchange="updateGlobalFontPreview(this)">
                                 <option value="">Default (Inter / Noto Sans JP)</option>
                                 <?php
                                $japaneseFonts = [
                                    "'Noto Sans JP', sans-serif"          => 'Noto Sans JP',
                                    "'Noto Serif JP', serif"              => 'Noto Serif JP',
                                    "'M PLUS Rounded 1c', sans-serif"     => 'M PLUS Rounded 1c',
                                    "'M PLUS 1p', sans-serif"             => 'M PLUS 1p',
                                    "'Kosugi Maru', sans-serif"           => 'Kosugi Maru',
                                    "'Kosugi', sans-serif"                => 'Kosugi',
                                    "'Sawarabi Gothic', sans-serif"       => 'Sawarabi Gothic',
                                    "'Sawarabi Mincho', serif"            => 'Sawarabi Mincho',
                                    "'Kaisei Decol', serif"               => 'Kaisei Decol',
                                    "'Kaisei HarunoUmi', serif"           => 'Kaisei HarunoUmi',
                                    "'Kaisei Opti', serif"                => 'Kaisei Opti',
                                    "'Kaisei Tokumin', serif"             => 'Kaisei Tokumin',
                                    "'Hachi Maru Pop', cursive"           => 'Hachi Maru Pop',
                                    "'Yuji Boku', serif"                  => 'Yuji Boku',
                                    "'Yuji Mai', serif"                   => 'Yuji Mai',
                                    "'Yuji Syuku', serif"                 => 'Yuji Syuku',
                                    "'Zen Kaku Gothic New', sans-serif"   => 'Zen Kaku Gothic New',
                                    "'Zen Maru Gothic', sans-serif"       => 'Zen Maru Gothic',
                                    "'Zen Old Mincho', serif"             => 'Zen Old Mincho',
                                    "'BIZ UDPGothic', sans-serif"         => 'BIZ UDPGothic',
                                ];
                                $currentFont = $settings['font_family'] ?? '';
                                foreach ($japaneseFonts as $val => $label):
                                ?>
                                <option value="<?= e($val) ?>" <?= $currentFont === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                             <label for="test_font_family">Mock Test Question & Options Font</label>
                             <select id="test_font_family" name="test_font_family" onchange="updateTestFontPreview(this)">
                                 <option value="">Same as Global</option>
                                 <?php
                                $currentTestFont = $settings['test_font_family'] ?? '';
                                foreach ($japaneseFonts as $val => $label):
                                ?>
                                <option value="<?= e($val) ?>" <?= $currentTestFont === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="font_size">
                                Global Font Size: 
                                <strong id="fontSizeVal"><?= intval($settings['font_size'] ?? '16') ?>px</strong>
                            </label>
                            <input type="range" id="font_size" name="font_size"
                                   min="10" max="80" step="1"
                                   value="<?= intval($settings['font_size'] ?? '16') ?>"
                                   oninput="document.getElementById('fontSizeVal').textContent = this.value + 'px'"
                                   style="width:100%; accent-color: var(--primary);">
                             <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">
                                 <span>10px</span><span>80px</span>
                             </div>
                         </div>
                     </div>

                     <h3 style="margin-top: 2rem; margin-bottom: 1rem; font-size: 1.05rem;">Test Page Layout</h3>
                     <p style="color: var(--text-muted); font-size: 0.83rem; margin-bottom: 1rem;">Control the size of elements on the test page.</p>
                     <div class="form-row">
                         <div class="form-group">
                             <label for="test_header_size">
                                 Test Header Font Size: 
                                 <strong id="testHeaderSizeVal"><?= intval($settings['test_header_size'] ?? '26') ?>px</strong>
                             </label>
                             <input type="range" id="test_header_size" name="test_header_size"
                                    min="14" max="48" step="1"
                                    value="<?= intval($settings['test_header_size'] ?? '26') ?>"
                                    oninput="document.getElementById('testHeaderSizeVal').textContent = this.value + 'px'"
                                    style="width:100%; accent-color: var(--primary);">
                             <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">
                                 <span>14px</span><span>48px</span>
                             </div>
                         </div>
                         <div class="form-group">
                             <label for="test_desc_size">
                                 Test Description Font Size: 
                                 <strong id="testDescSizeVal"><?= intval($settings['test_desc_size'] ?? '14') ?>px</strong>
                             </label>
                             <input type="range" id="test_desc_size" name="test_desc_size"
                                    min="10" max="24" step="1"
                                    value="<?= intval($settings['test_desc_size'] ?? '14') ?>"
                                    oninput="document.getElementById('testDescSizeVal').textContent = this.value + 'px'"
                                    style="width:100%; accent-color: var(--primary);">
                             <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">
                                 <span>10px</span><span>24px</span>
                             </div>
                         </div>
                     </div>
                     <div class="form-row">
                         <div class="form-group">
                             <label for="test_ui_size">
                                 Test UI Elements Font Size (Tags, Buttons, Options, etc.): 
                                 <strong id="testUiSizeVal"><?= intval($settings['test_ui_size'] ?? '16') ?>px</strong>
                             </label>
                             <input type="range" id="test_ui_size" name="test_ui_size"
                                    min="10" max="32" step="1"
                                    value="<?= intval($settings['test_ui_size'] ?? '16') ?>"
                                    oninput="document.getElementById('testUiSizeVal').textContent = this.value + 'px'"
                                    style="width:100%; accent-color: var(--primary);">
                             <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">
                                 <span>10px</span><span>32px</span>
                             </div>
                         </div>
                     </div>

                     <h3 style="margin-top: 2rem; margin-bottom: 1rem; font-size: 1.05rem;">Index Page Layout</h3>
                     <p style="color: var(--text-muted); font-size: 0.83rem; margin-bottom: 1rem;">Control the size and width of elements on the main page.</p>
                     
                     <div class="form-row">
                         <div class="form-group">
                             <label for="index_countdown_width">
                                 Countdown Width (%): 
                                 <strong id="countdownWidthVal"><?= intval($settings['index_countdown_width'] ?? '100') ?>%</strong>
                             </label>
                             <input type="range" id="index_countdown_width" name="index_countdown_width"
                                    min="20" max="100" step="5"
                                    value="<?= intval($settings['index_countdown_width'] ?? '100') ?>"
                                    oninput="document.getElementById('countdownWidthVal').textContent = this.value + '%'"
                                    style="width:100%; accent-color: var(--primary);">
                         </div>
                         <div class="form-group">
                             <label for="index_countdown_height">
                                 Countdown Height (px): 
                                 <strong id="countdownHeightVal"><?= intval($settings['index_countdown_height'] ?? '180') ?>px</strong>
                             </label>
                             <input type="range" id="index_countdown_height" name="index_countdown_height"
                                    min="100" max="500" step="10"
                                    value="<?= intval($settings['index_countdown_height'] ?? '180') ?>"
                                    oninput="document.getElementById('countdownHeightVal').textContent = this.value + 'px'"
                                    style="width:100%; accent-color: var(--primary);">
                         </div>
                     </div>
                     <div class="form-row">
                         <div class="form-group">
                             <label for="index_card_width">
                                 Card Width (%): 
                                 <strong id="cardWidthVal"><?= intval($settings['index_card_width'] ?? '100') ?>%</strong>
                             </label>
                             <input type="range" id="index_card_width" name="index_card_width"
                                    min="20" max="100" step="5"
                                    value="<?= intval($settings['index_card_width'] ?? '100') ?>"
                                    oninput="document.getElementById('cardWidthVal').textContent = this.value + '%'"
                                    style="width:100%; accent-color: var(--primary);">
                         </div>
                         <div class="form-group">
                             <label for="index_card_height">
                                 Card Height (px): 
                                 <strong id="cardHeightVal"><?= intval($settings['index_card_height'] ?? '120') ?>px</strong>
                             </label>
                             <input type="range" id="index_card_height" name="index_card_height"
                                    min="80" max="400" step="10"
                                    value="<?= intval($settings['index_card_height'] ?? '120') ?>"
                                    oninput="document.getElementById('cardHeightVal').textContent = this.value + 'px'"
                                    style="width:100%; accent-color: var(--primary);">
                         </div>
                     </div>
                     <div class="form-row">
                         <div class="form-group">
                             <label for="floating_btn_size">
                                 Floating Action Buttons Size (px): 
                                 <strong id="floatingBtnSizeVal"><?= intval($settings['floating_btn_size'] ?? '40') ?>px</strong>
                             </label>
                             <input type="range" id="floating_btn_size" name="floating_btn_size"
                                    min="30" max="80" step="2"
                                    value="<?= intval($settings['floating_btn_size'] ?? '40') ?>"
                                    oninput="document.getElementById('floatingBtnSizeVal').textContent = this.value + 'px'"
                                    style="width:100%; accent-color: var(--primary);">
                         </div>
                         <div class="form-group">
                             <label for="index_card_title_size">
                                 Card Title Font Size (px): 
                                 <strong id="cardTitleSizeVal"><?= intval($settings['index_card_title_size'] ?? '16') ?>px</strong>
                             </label>
                             <input type="range" id="index_card_title_size" name="index_card_title_size"
                                    min="12" max="28" step="1"
                                    value="<?= intval($settings['index_card_title_size'] ?? '16') ?>"
                                    oninput="document.getElementById('cardTitleSizeVal').textContent = this.value + 'px'"
                                    style="width:100%; accent-color: var(--primary);">
                         </div>
                     </div>

                    <h3 style="margin-top: 2rem; margin-bottom: 1rem; font-size: 1.05rem;">Upcoming Test Countdown</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="upcoming_title">Upcoming Test Title (Optional)</label>
                            <textarea id="upcoming_title" name="upcoming_title" rows="2" placeholder="e.g. Next Skill Mock Test"><?= e($settings['upcoming_title'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="upcoming_date">Upcoming Test Date & Time</label>
                            <input type="datetime-local" id="upcoming_date" name="upcoming_date" 
                                   value="<?= e($settings['upcoming_date'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="sticky-save-bar">
                        <span style="font-size:0.82rem; color:var(--text-muted);">Changes are saved to the database.</span>
                        <button type="submit" class="btn btn-primary">💾 Save Settings</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        function updateGlobalFontPreview(sel) {
            const val = sel.options[sel.selectedIndex].text;
            if(val !== 'Default (Inter / Noto Sans JP)' && val) {
                let clean = val.replace(/ /g, '+');
                document.getElementById('googleFontLink').href = 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=' + clean + ':wght@400;500;700&display=swap';
                document.body.style.setProperty('font-family', sel.value, 'important');
            } else {
                document.getElementById('googleFontLink').href = 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+JP:wght@400;500;700&display=swap';
                document.body.style.fontFamily = '';
            }
        }
        
        function updateTestFontPreview(sel) {
            const val = sel.options[sel.selectedIndex].text;
            if(val !== 'Same as Global' && val) {
                let clean = val.replace(/ /g, '+');
                document.getElementById('googleTestFontLink').href = 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=' + clean + ':wght@400;500;700&display=swap';
            }
        }
    </script>
</body>
</html>
