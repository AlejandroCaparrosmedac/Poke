/**
 * Dark Mode Toggle Script
 * Maneja el cambio entre modo claro y oscuro
 * Persiste la preferencia en localStorage
 */

class DarkModeManager {
    constructor() {
        this.storageKey = 'pokédex-theme';
        this.darkModeClass = 'dark-mode';
        this.init();
    }

    init() {
        // Cargar preferencia guardada o usar preferencia del sistema
        const savedTheme = localStorage.getItem(this.storageKey);

        if (savedTheme) {
            this.setTheme(savedTheme === 'dark');
        } else {
            // Usar preferencia del sistema
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.setTheme(prefersDark);
        }

        // Escuchar cambios de tema del sistema
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem(this.storageKey)) {
                this.setTheme(e.matches);
            }
        });
    }

    toggle() {
        const isDarkMode = document.body.classList.contains(this.darkModeClass);
        this.setTheme(!isDarkMode);
    }

    setTheme(isDark) {
        if (isDark) {
            document.body.classList.add(this.darkModeClass);
            localStorage.setItem(this.storageKey, 'dark');
            this.updateToggleButton('☀️');
        } else {
            document.body.classList.remove(this.darkModeClass);
            localStorage.setItem(this.storageKey, 'light');
            this.updateToggleButton('🌙');
        }
    }

    updateToggleButton(icon) {
        const btn = document.getElementById('theme-toggle');
        if (btn) {
            btn.textContent = icon;
        }
    }

    isDarkMode() {
        return document.body.classList.contains(this.darkModeClass);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.darkModeManager = new DarkModeManager();
});
