import { Controller } from '@hotwired/stimulus';

/*
 * Theme Toggle Controller
 * Manages dark/light mode with localStorage persistence and FOIT prevention.
 *
 * Usage:
 *   <body data-controller="theme-toggle">
 *     <button data-action="click->theme-toggle#toggle" data-theme-toggle-target="button">
 *       Toggle Theme
 *     </button>
 *   </body>
 */
export default class extends Controller {
    static targets = ['button', 'icon'];

    connect() {
        // Determine the theme: localStorage > system preference > default light
        const savedTheme = localStorage.getItem('smartnexus-theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
            document.documentElement.classList.add('dark');
            this._isDark = true;
        } else {
            document.documentElement.classList.remove('dark');
            this._isDark = false;
        }

        this._updateUI();

        // Listen for system preference changes
        this._mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        this._systemChangeHandler = (e) => {
            if (!localStorage.getItem('smartnexus-theme')) {
                this._isDark = e.matches;
                document.documentElement.classList.toggle('dark', e.matches);
                this._updateUI();
            }
        };
        this._mediaQuery.addEventListener('change', this._systemChangeHandler);
    }

    toggle() {
        this._isDark = !this._isDark;

        // Add transition class for smooth color changes
        document.documentElement.classList.add('transitioning-theme');

        document.documentElement.classList.toggle('dark', this._isDark);
        localStorage.setItem('smartnexus-theme', this._isDark ? 'dark' : 'light');

        this._updateUI();

        // Remove transition class after animation completes
        setTimeout(() => {
            document.documentElement.classList.remove('transitioning-theme');
        }, 500);
    }

    _updateUI() {
        // Update toggle button icon if present
        if (this.hasIconTarget) {
            this.iconTarget.textContent = this._isDark ? 'light_mode' : 'dark_mode';
        }

        // Update button title
        if (this.hasButtonTarget) {
            this.buttonTarget.title = this._isDark ? 'Switch to light mode' : 'Switch to dark mode';
        }
    }

    disconnect() {
        if (this._mediaQuery && this._systemChangeHandler) {
            this._mediaQuery.removeEventListener('change', this._systemChangeHandler);
        }
    }
}
