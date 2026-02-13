import { Controller } from '@hotwired/stimulus';

/*
 * Text Reveal Controller
 * Splits text into words/characters and stagger-animates them on scroll.
 * Waits for fonts to load before calculating geometry.
 *
 * Usage:
 *   <h1 data-controller="text-reveal" data-text-reveal-split-value="words">
 *     My Amazing Headline
 *   </h1>
 */
export default class extends Controller {
    static values = {
        split: { type: String, default: 'words' }, // 'words' or 'chars'
        delay: { type: Number, default: 0.04 },
    };

    connect() {
        // Store original text for cleanup
        this._originalHTML = this.element.innerHTML;
        this._revealed = false;

        // Wait for fonts before splitting to ensure correct geometry
        document.fonts.ready.then(() => {
            this._splitText();
            this._observeScroll();
        });
    }

    _splitText() {
        const text = this.element.textContent.trim();
        this.element.innerHTML = '';
        this.element.style.overflow = 'hidden';

        if (this.splitValue === 'chars') {
            [...text].forEach((char, i) => {
                const span = document.createElement('span');
                span.textContent = char === ' ' ? '\u00A0' : char;
                span.style.cssText = `
          display: inline-block;
          opacity: 0;
          transform: translateY(100%) rotateX(-80deg);
          transition: opacity 0.5s ease ${i * this.delayValue}s,
                      transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) ${i * this.delayValue}s;
        `;
                span.classList.add('text-reveal-char');
                this.element.appendChild(span);
            });
        } else {
            text.split(/\s+/).forEach((word, i) => {
                const wrapper = document.createElement('span');
                wrapper.style.cssText = 'display: inline-block; overflow: hidden; margin-right: 0.3em;';

                const inner = document.createElement('span');
                inner.textContent = word;
                inner.style.cssText = `
          display: inline-block;
          opacity: 0;
          transform: translateY(110%);
          transition: opacity 0.6s ease ${i * this.delayValue}s,
                      transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) ${i * this.delayValue}s;
        `;
                inner.classList.add('text-reveal-word');

                wrapper.appendChild(inner);
                this.element.appendChild(wrapper);
            });
        }
    }

    _observeScroll() {
        this._observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting && !this._revealed) {
                        this._revealed = true;
                        this._reveal();
                        this._observer.unobserve(this.element);
                    }
                });
            },
            { threshold: 0.2 }
        );

        this._observer.observe(this.element);
    }

    _reveal() {
        const selector = this.splitValue === 'chars' ? '.text-reveal-char' : '.text-reveal-word';
        this.element.querySelectorAll(selector).forEach((el) => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0) rotateX(0)';
        });
    }

    disconnect() {
        if (this._observer) {
            this._observer.disconnect();
        }
        // Restore original HTML for Turbo cache
        this.element.innerHTML = this._originalHTML;
        this.element.style.overflow = '';
    }
}
