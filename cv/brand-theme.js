/* One theme preference for the blog, CMS, portfolio and CV on the same origin. */
(() => {
    const root = document.documentElement;
    const key = 'gb-theme';
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    const stored = () => { try { const value = localStorage.getItem(key); return ['light','dark'].includes(value) ? value : null; } catch { return null; } };
    const preferred = () => stored() || (media.matches ? 'dark' : 'light');
    const apply = (theme, persist = false) => {
        root.setAttribute('data-theme-mode', theme);
        root.setAttribute('data-bs-theme', theme);
        const es = root.lang !== 'en';
        const label = theme === 'dark' ? (es ? 'Activar modo claro' : 'Switch to light mode') : (es ? 'Activar modo oscuro' : 'Switch to dark mode');
        document.querySelectorAll('#theme-toggle,[data-brand-theme-toggle]').forEach(button => {
            button.setAttribute('aria-pressed', String(theme === 'dark'));
            button.setAttribute('aria-label', label);
            const text = button.querySelector('.theme-toggle-label,[data-brand-theme-label]');
            if (text) text.textContent = label;
            const icon = button.querySelector('.theme-toggle-icon');
            if (icon) icon.className = 'bi ' + (theme === 'dark' ? 'bi-sun-fill' : 'bi-moon-stars-fill') + ' theme-toggle-icon';
        });
        document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'dark' ? '#0e1c27' : '#ffffff');
        document.dispatchEvent(new CustomEvent('gb:themechange', {detail:{theme}}));
        if (persist) { try { localStorage.setItem(key, theme); } catch { /* Theme still works with blocked storage. */ } }
    };
    window.MS_THEME = {apply, preferred};
    apply(preferred());
    document.addEventListener('DOMContentLoaded', () => {
        apply(preferred());
        document.querySelectorAll('#theme-toggle,[data-brand-theme-toggle]').forEach(button => button.addEventListener('click', () => apply(root.getAttribute('data-theme-mode') === 'dark' ? 'light' : 'dark', true)));
    });
    media.addEventListener('change', () => { if (!stored()) apply(preferred()); });
    window.addEventListener('storage', event => { if (event.key === key || event.key === null) apply(preferred()); });
})();
