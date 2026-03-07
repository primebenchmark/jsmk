<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --floating-btn-size: <?= e(intval($settings['floating_btn_size'] ?? 40)) ?>px;
    }
</style>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <span class="sidebar-logo">👤</span>
        <h2>Admin Panel</h2>
        <button class="sidebar-toggle-close" id="sidebarClose" onclick="toggleSidebar()">✕</button>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>"><span class="nav-icon">📊</span> Dashboard</a>
        <a href="tests.php" class="nav-item <?= $currentPage === 'tests.php' || $currentPage === 'edit.php' ? 'active' : '' ?>"><span class="nav-icon">📝</span> Tests</a>
        <a href="analytics.php" class="nav-item <?= $currentPage === 'analytics.php' ? 'active' : '' ?>"><span class="nav-icon">📈</span> Analytics</a>
        <a href="settings.php" class="nav-item <?= $currentPage === 'settings.php' ? 'active' : '' ?>"><span class="nav-icon">⚙️</span> Settings</a>
        <a href="test_settings.php" class="nav-item <?= $currentPage === 'test_settings.php' ? 'active' : '' ?>"><span class="nav-icon">🛠️</span> Test Settings</a>
        <a href="database.php" class="nav-item <?= $currentPage === 'database.php' ? 'active' : '' ?>"><span class="nav-icon">💽</span> Import / Export Database</a>
        <a href="upload.php" class="nav-item <?= $currentPage === 'upload.php' ? 'active' : '' ?>"><span class="nav-icon">📤</span> Upload</a>
        <a href="../index.php" class="nav-item"><span class="nav-icon">🌐</span> View Site</a>
        <a href="index.php?logout=1" class="nav-item logout"><span class="nav-icon">🚪</span> Logout</a>
    </nav>
</aside>
