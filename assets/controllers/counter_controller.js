import { Controller } from '@hotwired/stimulus';

/*
 * Counter Controller
 * Animates a number from 0 to target value when scrolled into view.
 *
 * Usage:
 *   <span data-controller="counter"
 *         data-counter-target-value="5000"
 *         data-counter-duration-value="2000"
 *         data-counter-suffix-value="+">0</span>
 */
export default class extends Controller {
    static values = {
        target: { type: Number, default: 0 },
        duration: { type: Number, default: 1500 },
        suffix: { type: String, default: '' },
        prefix: { type: String, default: '' },
    };

    connect() {
        this._counted = false;
        this._observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting && !this._counted) {
                        this._counted = true;
                        this._animate();
                        this._observer.unobserve(this.element);
                    }
                });
            },
            { threshold: 0.3 }
        );
        this._observer.observe(this.element);
    }

    _animate() {
        const target = this.targetValue;
        const duration = this.durationValue;
        const start = performance.now();

        const step = (now) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(eased * target);

            this.element.textContent = `${this.prefixValue}${current.toLocaleString()}${this.suffixValue}`;

            if (progress < 1) {
                this._rafId = requestAnimationFrame(step);
            }
        };

        this._rafId = requestAnimationFrame(step);
    }

    disconnect() {
        if (this._observer) {
            this._observer.disconnect();
        }
        if (this._rafId) {
            cancelAnimationFrame(this._rafId);
        }
    }
}
