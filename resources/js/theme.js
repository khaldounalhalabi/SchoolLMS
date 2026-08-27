/**
 * Theme switching.
 *
 * The initial stamp happens pre-paint in the inline <head> script
 * (resources/views/components/theme-script.blade.php) — this module only
 * handles user interaction and live OS changes after hydration.
 *
 * Contract, shared with that script:
 *   localStorage['theme']        preference: 'light' | 'dark' | 'system'
 *   <html data-theme-pref>       the same preference, reflected for CSS/JS
 *   <html data-theme>            the RESOLVED theme: 'light' | 'dark'
 */

const KEY = 'theme';
const root = document.documentElement;
const media = window.matchMedia('(prefers-color-scheme: dark)');

const resolve = (pref) => (pref === 'light' || pref === 'dark' ? pref : media.matches ? 'dark' : 'light');

const readPref = () => root.getAttribute('data-theme-pref') || 'system';

const syncButtons = () => {
    const pref = readPref();
    document.querySelectorAll('[data-theme-set]').forEach((btn) => {
        const active = btn.dataset.themeSet === pref;
        btn.classList.toggle('active', active);
        btn.setAttribute('aria-pressed', String(active));
    });
};

const apply = (pref) => {
    root.setAttribute('data-theme-pref', pref);
    root.setAttribute('data-theme', resolve(pref));
    try {
        localStorage.setItem(KEY, pref);
    } catch (e) {
        /* private mode / storage disabled — theme still applies for this page */
    }
    syncButtons();
};

document.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-theme-set]');
    if (btn) apply(btn.dataset.themeSet);
});

// Keep 'system' tracking the OS live, without overriding an explicit choice.
media.addEventListener('change', () => {
    if (readPref() === 'system') root.setAttribute('data-theme', resolve('system'));
});

// Reflect state on load, and again if the switcher renders after this module.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', syncButtons);
} else {
    syncButtons();
}
