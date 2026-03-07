/* ============================================
   Admin Sidebar Toggle — Mobile
   ============================================ */

(function () {
    window.toggleSidebar = function () {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }
    };
})();
