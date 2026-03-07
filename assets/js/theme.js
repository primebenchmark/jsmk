/* ============================================
   Theme Toggle — Light / Dark Mode + Color Themes
   ============================================ */

(function () {
    const THEME_KEY = 'mcq_theme';
    const COLOR_KEY = 'mcq_theme_color';

    const colorPalettes = {
        purple: {
            primary: '#6C5CE7',
            primaryDark: '#5A4BD1',
            primaryLight: '#A29BFE',
            secondary: '#00CEC9',
            accent: '#FD79A8'
        },
        blue: {
            primary: '#0984E3',
            primaryDark: '#0769C0',
            primaryLight: '#74B9FF',
            secondary: '#00CEC9',
            accent: '#FD79A8'
        },
        teal: {
            primary: '#00B894',
            primaryDark: '#009975',
            primaryLight: '#55EFC4',
            secondary: '#0984E3',
            accent: '#FDCB6E'
        },
        green: {
            primary: '#27AE60',
            primaryDark: '#1E8449',
            primaryLight: '#58D68D',
            secondary: '#00CEC9',
            accent: '#F39C12'
        },
        orange: {
            primary: '#E17055',
            primaryDark: '#CB4335',
            primaryLight: '#FAB1A0',
            secondary: '#FDCB6E',
            accent: '#6C5CE7'
        },
        red: {
            primary: '#E74C3C',
            primaryDark: '#C0392B',
            primaryLight: '#EC7063',
            secondary: '#F39C12',
            accent: '#9B59B6'
        },
        pink: {
            primary: '#FD79A8',
            primaryDark: '#E84393',
            primaryLight: '#FDCB9F',
            secondary: '#6C5CE7',
            accent: '#00CEC9'
        },
        indigo: {
            primary: '#5C6BC0',
            primaryDark: '#3F51B5',
            primaryLight: '#7986CB',
            secondary: '#26A69A',
            accent: '#FF7043'
        },
        cyan: {
            primary: '#00BCD4',
            primaryDark: '#0097A7',
            primaryLight: '#4DD0E1',
            secondary: '#7C4DFF',
            accent: '#FF4081'
        },
        lime: {
            primary: '#8BC34A',
            primaryDark: '#689F38',
            primaryLight: '#AED581',
            secondary: '#00BCD4',
            accent: '#FF5722'
        },
        amber: {
            primary: '#FFA000',
            primaryDark: '#FF8F00',
            primaryLight: '#FFB300',
            secondary: '#7C4DFF',
            accent: '#00BCD4'
        },
        brown: {
            primary: '#795548',
            primaryDark: '#5D4037',
            primaryLight: '#8D6E63',
            secondary: '#00BCD4',
            accent: '#FF7043'
        },
        slate: {
            primary: '#607D8B',
            primaryDark: '#455A64',
            primaryLight: '#78909C',
            secondary: '#26A69A',
            accent: '#FF7043'
        },
        rose: {
            primary: '#E91E63',
            primaryDark: '#C2185B',
            primaryLight: '#F06292',
            secondary: '#00BCD4',
            accent: '#FFC107'
        },
        violet: {
            primary: '#9C27B0',
            primaryDark: '#7B1FA2',
            primaryLight: '#BA68C8',
            secondary: '#00BCD4',
            accent: '#FFEB3B'
        },
        emerald: {
            primary: '#2ECC71',
            primaryDark: '#27AE60',
            primaryLight: '#58D68D',
            secondary: '#3498DB',
            accent: '#F39C12'
        },
        sky: {
            primary: '#3498DB',
            primaryDark: '#2980B9',
            primaryLight: '#5DADE2',
            secondary: '#1ABC9C',
            accent: '#E74C3C'
        },
        fuchsia: {
            primary: '#D946EF',
            primaryDark: '#A21CAF',
            primaryLight: '#E879F9',
            secondary: '#06B6D4',
            accent: '#F59E0B'
        },
        gold: {
            primary: '#F59E0B',
            primaryDark: '#D97706',
            primaryLight: '#FBBF24',
            secondary: '#8B5CF6',
            accent: '#EC4899'
        },
        coral: {
            primary: '#FF6B6B',
            primaryDark: '#EE5A5A',
            primaryLight: '#FF8E8E',
            secondary: '#4ECDC4',
            accent: '#FFE66D'
        }
    };

    function applyColorTheme(color) {
        const palette = colorPalettes[color] || colorPalettes.purple;
        const root = document.documentElement;
        root.style.setProperty('--primary', palette.primary);
        root.style.setProperty('--primary-dark', palette.primaryDark);
        root.style.setProperty('--primary-light', palette.primaryLight);
        root.style.setProperty('--secondary', palette.secondary);
        root.style.setProperty('--accent', palette.accent);
    }

    function getPreferredTheme() {
        const saved = localStorage.getItem(THEME_KEY);
        if (saved) return saved;
        if (typeof DEFAULT_THEME !== 'undefined') return DEFAULT_THEME;
        return 'light';
    }

    function getPreferredColor() {
        if (typeof DEFAULT_COLOR !== 'undefined') return DEFAULT_COLOR;
        return 'purple';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        const icon = document.getElementById('themeIcon');
        if (icon) {
            icon.textContent = theme === 'light' ? '🌙' : '☀️';
        }
        localStorage.setItem(THEME_KEY, theme);
    }

    function initTheme() {
        applyTheme(getPreferredTheme());
        applyColorTheme(getPreferredColor());
    }

    initTheme();

    window.toggleTheme = function () {
        const current = document.documentElement.getAttribute('data-theme') || 'light';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    };

    window.setThemeColor = function (color) {
        applyColorTheme(color);
    };
})();
