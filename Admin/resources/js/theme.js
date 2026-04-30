/**
 * OnePortal Admin — Global Theme System
 * Handles light/dark mode toggle with localStorage persistence.
 * Apply on any page by importing this file.
 */

const THEME_KEY = 'op_theme';
const DARK_CLASS = 'dark';

/**
 * Apply the saved or preferred theme immediately (before paint).
 * Called inline so there's no flash of wrong theme.
 */
function applyTheme() {
    const saved = localStorage.getItem(THEME_KEY);
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = saved ? saved === 'dark' : prefersDark;

    if (isDark) {
        document.documentElement.classList.add(DARK_CLASS);
    } else {
        document.documentElement.classList.remove(DARK_CLASS);
    }
}

/**
 * Toggle between light and dark, persist to localStorage,
 * and update any toggle buttons on the page.
 */
function toggleTheme() {
    const isDark = document.documentElement.classList.toggle(DARK_CLASS);
    localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
    syncToggleButtons();
}

/**
 * Update all toggle button icons/labels to reflect current theme.
 */
function syncToggleButtons() {
    const isDark = document.documentElement.classList.contains(DARK_CLASS);
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        const iconLight = btn.querySelector('.theme-icon-light');
        const iconDark  = btn.querySelector('.theme-icon-dark');
        const label     = btn.querySelector('.theme-label');

        if (iconLight) iconLight.style.display = isDark ? 'none'  : 'inline-flex';
        if (iconDark)  iconDark.style.display  = isDark ? 'inline-flex' : 'none';
        if (label)     label.textContent        = isDark ? 'Light Mode' : 'Dark Mode';

        btn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        btn.setAttribute('title',      isDark ? 'Switch to light mode' : 'Switch to dark mode');
    });
}

// ── Apply theme immediately on script load ──────────────────
applyTheme();

// ── Wire up buttons after DOM is ready ──────────────────────
document.addEventListener('DOMContentLoaded', () => {
    syncToggleButtons();

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', toggleTheme);
    });
});

// ── Expose globally for inline use if needed ────────────────
window.opTheme = { toggle: toggleTheme, apply: applyTheme };
