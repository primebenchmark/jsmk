<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_guard.php';
requireDeviceAuth();

$settings = getSettings();
$tests    = getTests();

$siteName    = $settings['site_name']    ?? 'JFT Mock Test';
$headerTitle = $settings['header_title'] ?? 'JFT Mock Test';
$contentProtection = $settings['content_protection'] ?? true;
$fontFamily  = $settings['font_family'] ?? '';
$fontSize    = $settings['font_size']   ?? '';
$defaultTheme = $settings['default_theme'] ?? 'light';
$themeColor = $settings['theme_color'] ?? 'purple';

$upcomingTitle = $settings['upcoming_title'] ?? '';
$upcomingDate  = $settings['upcoming_date']  ?? '';

$countdownWidth   = (strpos($settings['index_countdown_width'] ?? '', 'px') !== false) ? '100%' : ($settings['index_countdown_width'] ?? '100%');
$countdownHeight  = $settings['index_countdown_height'] ?? '180px';

$cardWidth        = (strpos($settings['index_card_width'] ?? '', 'px') !== false) ? '100%' : ($settings['index_card_width'] ?? '100%');
$cardHeight       = $settings['index_card_height'] ?? '120px';

$cardTitleSize    = ($settings['index_card_title_size'] ?? 16) . 'px';
$floatingBtnSize  = ($settings['floating_btn_size'] ?? 40) . 'px';

$showIndexHero      = $settings['show_index_hero']   ?? true;
$showIndexHeader    = $settings['show_index_header'] ?? true;
$showIndexCountdown = $settings['show_index_countdown'] ?? true;
$showIndexCatalog   = $settings['show_index_catalog'] ?? true;
$showFooter         = $settings['show_footer'] ?? true;
$showAdminButton    = $settings['show_admin_button'] ?? true;

// Group tests by category
$categories = [];
foreach ($tests as $test) {
    $cat = !empty($test['category']) ? trim($test['category']) : 'General';
    $categories[$cat][] = $test;
}
ksort($categories);

$totalTests     = count($tests);
$totalQuestions = array_sum(array_column($tests, 'questions'));
$totalCats      = count($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($siteName) ?> — Prepare smarter. Score higher.">
    <title><?= e($siteName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="<?= getGoogleFontsLink($fontFamily) ?>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
    <style>
        <?php if ($fontFamily): ?>:root { --font-family: <?= e($fontFamily) ?>; } body { font-family: var(--font-family) !important; }<?php endif; ?>
        <?php if ($fontSize):   ?>:root { --font-size: <?= e($fontSize) ?>; }    body { font-size:  var(--font-size) !important; }<?php endif; ?>

        /* ── Centered Layout ── */
        :root {
            --floating-btn-size: <?= e($floatingBtnSize) ?>;
        }
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .bg-gradient {
            position: fixed;
            left: 0;
        }

        /* ── Logo ── */
        .site-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            border-radius: 50%;
            object-fit: contain;
        }

        /* ── Hero ── */
        .hero {
            position: relative;
            z-index: 2;
            text-align: center;
            width: 100%;
            padding: 3rem 1.5rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .hero-glass {
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(18px) saturate(1.6);
            -webkit-backdrop-filter: blur(18px) saturate(1.6);
            border: 1px solid rgba(255,255,255,0.65);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(108,92,231,0.08);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 1200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        [data-theme="dark"] .hero-glass {
            background: rgba(26,26,46,0.60);
            border-color: rgba(255,255,255,0.10);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(108,92,231,.1);
            border: 1px solid rgba(108,92,231,.25);
            color: var(--primary);
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.35rem 0.9rem;
            border-radius: 99px;
            margin-bottom: 1.4rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .hero h1 {
            font-size: clamp(1.8rem, 5vw, 3rem);
            font-weight: 800;
            line-height: 1.15;
            color: var(--text-primary);
            margin-bottom: 0.9rem;
        }

        .hero-sub {
            font-size: 1rem;
            color: var(--text-secondary);
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.65;
        }

        /* ── Countdown ── */
        .countdown-wrap {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: <?= e($countdownWidth) ?>;
            margin: 1.5rem auto 2rem;
            padding: 0 1.5rem;
        }

        .countdown-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
            height: <?= e($countdownHeight) ?>;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .countdown-accent {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--primary);
            border-radius: 3px 3px 0 0;
        }

        .countdown-label {
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--primary);
            margin-bottom: 0.4rem;
        }

        .countdown-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.4rem;
            color: var(--text-primary);
        }

        .countdown-dials {
            display: flex;
            justify-content: center;
            gap: 0.9rem;
        }

        .dial {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            min-width: 76px;
            padding: 1rem 0.75rem 0.7rem;
            transition: transform .2s;
        }

        .dial:hover { transform: translateY(-3px); }

        .dial-val {
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .dial-unit {
            font-size: 0.67rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-top: 0.3rem;
        }

        /* ── Catalog ── */
        .catalog {
            position: relative;
            z-index: 2;
            width: 100%;
            margin: 0 auto;
            padding: 0 1.5rem 4rem;
        }

        .cat-section { margin-bottom: 3rem; }

        .cat-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1.2rem;
        }

        .cat-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 99px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-primary);
            box-shadow: var(--shadow);
        }

        .cat-count {
            background: var(--bg-surface);
            border-radius: 99px;
            padding: 0.1rem 0.48rem;
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .cat-divider {
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(<?= e($cardWidth) ?>, 1fr));
            gap: 1.2rem;
            justify-content: center;
        }

        /* Test Card */
        .tcard {
            position: relative;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
            cursor: pointer;
            box-shadow: var(--shadow);
        }

        .tcard:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 38px rgba(0,0,0,.1);
            border-color: var(--card-accent);
            text-decoration: none;
            color: inherit;
        }

        .tcard-accent {
            height: 4px;
            background: var(--card-accent, var(--primary));
            flex-shrink: 0;
        }

        .tcard-glow {
            position: absolute;
            top: -40px; right: -40px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: var(--card-accent, var(--primary));
            opacity: .05;
            filter: blur(30px);
            pointer-events: none;
        }

        .tcard-body {
            padding: 1.25rem 1.35rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .tcard-icon {
            width: 42px; height: 42px;
            border-radius: var(--radius-sm);
            background: var(--card-accent, var(--primary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 0.85rem;
            flex-shrink: 0;
        }

        .tcard-name {
            font-size: <?= e($cardTitleSize) ?>;
            font-weight: 700;
            margin-bottom: 0.28rem;
            color: var(--text-primary);
        }

        .tcard-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.3rem 0.6rem;
        }

        .tcard-meta-item {
            font-size: 0.72rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.2rem;
        }

        /* Empty */
        .empty-catalog { text-align: center; padding: 5rem 1rem; color: var(--text-muted); }
        .empty-catalog .empty-icon { font-size: 4rem; margin-bottom: 1rem; }

        /* Footer */
        .page-footer {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--text-muted);
            font-size: 0.82rem;
            position: relative;
            z-index: 2;
            border-top: 1px solid var(--border);
        }

        /* Logout Floating button */
        .logout-float-btn {
            position: fixed;
            right: 4.5rem;
            bottom: 2rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            color: var(--text-secondary);
            font-size: calc(var(--floating-btn-size) * 0.42);
            width: var(--floating-btn-size);
            height: var(--floating-btn-size);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            z-index: 50;
            padding: 0;
            box-sizing: border-box;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .logout-float-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            color: #E17055;
        }

        /* Admin Floating button */
        .admin-float-btn {
            position: fixed;
            left: 1.5rem;
            bottom: 2rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            color: var(--text-primary);
            text-decoration: none;
            font-size: calc(var(--floating-btn-size) * 0.45);
            width: var(--floating-btn-size);
            height: var(--floating-btn-size);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            z-index: 50;
            padding: 0;
            box-sizing: border-box;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .admin-float-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 640px) {
            .hero { padding: 2.5rem 1rem 1.5rem; }
            .countdown-dials { gap: 0.5rem; }
            .dial { min-width: 60px; padding: 0.75rem 0.5rem 0.6rem; }
            .dial-val { font-size: 1.55rem; }
            .cards-grid { grid-template-columns: 1fr; }
            .admin-float-btn { bottom: 16px; left: 16px; }
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

    <?php if ($showAdminButton): ?>
    <a href="admin/index.php" class="admin-float-btn" title="Admin Panel">
        🛡️
    </a>
    <?php endif; ?>

    <!-- Logout button -->
    <button class="logout-float-btn" title="Logout" onclick="deviceLogout()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
    </button>

    <!-- ══ HERO ══════════════════════════════ -->
    <?php if ($showIndexHero || $showIndexHeader): ?>
    <section class="hero">
        <div class="hero-glass">
            <?php if ($showIndexHero): ?>
            <img src="logo.png" alt="<?= e($siteName) ?>" class="site-logo" onerror="this.style.display='none'">
            <?php endif; ?>
            <?php if ($showIndexHeader): ?>
            <h1><?= e($headerTitle) ?></h1>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ══ COUNTDOWN ═════════════════════════ -->
    <?php if ($showIndexCountdown && !empty($upcomingDate)): ?>
    <div class="countdown-wrap" id="countdownWrap" style="display:none">
        <div class="countdown-card">
            <div class="countdown-accent"></div>
            <div class="countdown-title"><?= !empty($upcomingTitle) ? nl2br(e($upcomingTitle)) : 'Upcoming Mock Test' ?></div>
            <div class="countdown-dials">
                <div class="dial"><div class="dial-val" id="cd-days">00</div><div class="dial-unit">Days</div></div>
                <div class="dial"><div class="dial-val" id="cd-hours">00</div><div class="dial-unit">Hours</div></div>
                <div class="dial"><div class="dial-val" id="cd-minutes">00</div><div class="dial-unit">Mins</div></div>
                <div class="dial"><div class="dial-val" id="cd-seconds">00</div><div class="dial-unit">Secs</div></div>
            </div>
        </div>
    </div>
    <script>
    (function() {
        const dest = new Date("<?= e($upcomingDate) ?>").getTime();
        const wrap = document.getElementById('countdownWrap');
        function tick() {
            const now  = Date.now();
            const dist = dest - now;
            if (dist <= 0) { wrap.style.display = 'none'; return; }
            wrap.style.display = 'block';
            document.getElementById('cd-days').textContent    = String(Math.floor(dist / 864e5)).padStart(2,'0');
            document.getElementById('cd-hours').textContent   = String(Math.floor((dist % 864e5) / 36e5)).padStart(2,'0');
            document.getElementById('cd-minutes').textContent = String(Math.floor((dist % 36e5) / 6e4)).padStart(2,'0');
            document.getElementById('cd-seconds').textContent = String(Math.floor((dist % 6e4) / 1e3)).padStart(2,'0');
        }
        tick();
        setInterval(tick, 1000);
    })();
    </script>
    <?php endif; ?>

    <!-- ══ TEST CATALOG ═══════════════════════ -->
    <?php if ($showIndexCatalog): ?>
    <div class="catalog">
        <?php if (empty($tests)): ?>
            <div class="empty-catalog">
                <div class="empty-icon">📝</div>
                <h2 style="font-size:1.4rem; margin-bottom:.5rem;">No tests available yet</h2>
                <p>Check back soon — tests are being added!</p>
            </div>
        <?php else: ?>
            <?php foreach ($categories as $catName => $catTests): ?>
            <div class="cat-section">
                <!-- Category header -->
                <div class="cat-header">
                    <div class="cat-divider"></div>
                    <div class="cat-pill">
                        🏷️ <?= e($catName) ?>
                        <span class="cat-count"><?= count($catTests) ?></span>
                    </div>
                    <div class="cat-divider"></div>
                </div>

                <!-- Cards -->
                <div class="cards-grid">
                    <?php foreach ($catTests as $test):
                        $color = $test['color'];
                        $isComp = in_array($test['compulsory'] ?? 'global', ['yes']);
                    ?>
                    <a href="quiz.php?id=<?= urlencode($test['id']) ?>"
                       class="tcard"
                       style="--card-accent: <?= e($color) ?>">

                        <div class="tcard-accent"></div>
                        <div class="tcard-glow"></div>

                        <div class="tcard-body">
                            <!-- <div class="tcard-icon">📝</div> -->
                            <div class="tcard-name"><?= e($test['name']) ?></div>
                            <div class="tcard-meta">
                                <span class="tcard-meta-item">📊 <?= $test['questions'] ?> Questions</span>
                                <span class="tcard-meta-item">📋 <?= $test['sections'] ?> Sections</span>
                                <span class="tcard-meta-item">🎧 <?= $test['audio_limit'] ?>× plays</span>
                                <?php if ($isComp): ?>
                                    <span class="tcard-compulsory" style="font-size: 0.67rem; padding: 0.15rem 0.48rem; border-radius: 99px; font-weight: 600; background: rgba(253,121,168,.1); color: #fd79a8; border: 1px solid rgba(253,121,168,.2);">All Required</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($showFooter): ?>
    <footer class="page-footer">
        <p>&copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved. &nbsp;|&nbsp; <a href="admin/login.php" style="color: var(--text-muted); text-decoration: none;">Admin</a></p>
    </footer>
    <?php endif; ?>

    <script>
        const DEFAULT_THEME = '<?= e($defaultTheme) ?>';
        const DEFAULT_COLOR = '<?= e($themeColor) ?>';
    </script>
    <script src="assets/js/theme.js"></script>
    <script>
        function deviceLogout() {
            const token = localStorage.getItem('device_token');
            if (token) {
                fetch('api/device/logout.php', {
                    method: 'GET',
                    headers: { 'X-Device-Token': token }
                }).finally(() => {
                    // Keep token in localStorage - don't remove it
                    // On next visit, auto-login will detect the token and restore the session
                    window.location.href = 'login.php';
                });
            } else {
                window.location.href = 'login.php';
            }
        }
    </script>

    <?php if ($contentProtection): ?>
    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('copy',  e => e.preventDefault());
        document.addEventListener('cut',   e => e.preventDefault());
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && ['c','x','u','s','p'].includes(e.key.toLowerCase())) e.preventDefault();
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && ['i','j','c'].includes(e.key.toLowerCase()))) e.preventDefault();
            if (e.key === 'PrintScreen') e.preventDefault();
        });
    </script>
    <?php endif; ?>
</body>
</html>
