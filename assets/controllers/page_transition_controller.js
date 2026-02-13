import { Controller } from '@hotwired/stimulus';

/*
 * Page Transition Controller
 * Auto-animates page content on load using GSAP with CSS fallbacks.
 * Attach to the main content wrapper: data-controller="page-transition"
 */
export default class extends Controller {
    connect() {
        // Small delay to let DOM paint first frame
        requestAnimationFrame(() => {
            this._animatePageContent();
            this._setupFlashMessages();
        });
    }

    _animatePageContent() {
        if (typeof gsap === 'undefined') return;

        // 1. Page title reveal
        const titles = this.element.querySelectorAll('.page-title');
        if (titles.length) {
            gsap.from(titles, {
                y: 24,
                opacity: 0,
                filter: 'blur(4px)',
                duration: 0.7,
                ease: 'power3.out',
            });
        }

        // 2. Subtitles
        const subs = this.element.querySelectorAll('.page-subtitle');
        if (subs.length) {
            gsap.from(subs, {
                y: 16,
                opacity: 0,
                duration: 0.6,
                delay: 0.15,
                ease: 'power3.out',
            });
        }

        // 3. Stat cards stagger
        const statCards = this.element.querySelectorAll('.animate-card');
        if (statCards.length) {
            gsap.from(statCards, {
                y: 20,
                opacity: 0,
                scale: 0.96,
                duration: 0.6,
                stagger: 0.08,
                ease: 'power3.out',
            });
        }

        // 4. Table rows stagger
        const rows = this.element.querySelectorAll('.animate-row');
        if (rows.length) {
            gsap.from(rows, {
                x: -16,
                opacity: 0,
                duration: 0.45,
                stagger: 0.04,
                ease: 'power2.out',
                delay: 0.2,
            });
        }

        // 5. Progress bars
        const bars = this.element.querySelectorAll('.animate-bar');
        if (bars.length) {
            gsap.from(bars, {
                scaleX: 0,
                duration: 0.9,
                ease: 'power3.out',
                delay: 0.4,
                stagger: 0.06,
            });
        }

        // 6. Section cards
        const sections = this.element.querySelectorAll('.section-card');
        if (sections.length) {
            gsap.from(sections, {
                y: 20,
                opacity: 0,
                duration: 0.6,
                stagger: 0.1,
                ease: 'power3.out',
                delay: 0.15,
            });
        }

        // 7. Search/filter bars
        const toolbars = this.element.querySelectorAll('.animate-toolbar');
        if (toolbars.length) {
            gsap.from(toolbars, {
                y: 12,
                opacity: 0,
                duration: 0.5,
                stagger: 0.08,
                ease: 'power3.out',
                delay: 0.1,
            });
        }
    }

    _setupFlashMessages() {
        const flashes = this.element.querySelectorAll('.flash-message');
        flashes.forEach((flash) => {
            // Auto-dismiss after 5s
            setTimeout(() => {
                flash.classList.add('dismissing');
                setTimeout(() => flash.remove(), 300);
            }, 5000);

            // Click to dismiss
            const closeBtn = flash.querySelector('[data-dismiss]');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    flash.classList.add('dismissing');
                    setTimeout(() => flash.remove(), 300);
                });
            }
        });
    }

    disconnect() {
        // Clean up GSAP animations on Turbo navigation
        if (typeof gsap !== 'undefined') {
            gsap.killTweensOf(this.element.querySelectorAll('*'));
        }
    }
}
