import Alpine from ''alpinejs'';

window.Alpine = Alpine;
Alpine.start();

// Theme management
window.toggleTheme = function() {
    const html = document.documentElement;
    const current = html.getAttribute(''data-theme'');
    const next = current === ''dark'' ? ''light'' : ''dark'';
    html.setAttribute(''data-theme'', next);
    localStorage.setItem(''theme'', next);
};

// Initialize Lucide icons when DOM is ready
document.addEventListener(''DOMContentLoaded'', () => {
    if (window.lucide) {
        window.lucide.createIcons();
    }
});

// CSRF token for all AJAX requests
const token = document.querySelector(''meta[name="csrf-token"]'');
if (token) {
    window.axios.defaults.headers.common[''X-CSRF-TOKEN''] = token.content;
}
