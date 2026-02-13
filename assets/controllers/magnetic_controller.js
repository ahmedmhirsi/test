import { Controller } from '@hotwired/stimulus';

/*
 * Magnetic Controller
 * Buttons/elements subtly attract toward cursor for a premium, tactile feel.
 *
 * Usage:
 *   <button data-controller="magnetic" data-magnetic-strength-value="0.3">
 *     Click Me
 *   </button>
 */
export default class extends Controller {
    static values = {
        strength: { type: Number, default: 0.3 },
    };

    connect() {
        this._onMouseMove = this._handleMouseMove.bind(this);
        this._onMouseLeave = this._handleMouseLeave.bind(this);

        this.element.addEventListener('mousemove', this._onMouseMove);
        this.element.addEventListener('mouseleave', this._onMouseLeave);

        this.element.style.transition = 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
        this.element.style.willChange = 'transform';
    }

    _handleMouseMove(e) {
        const rect = this.element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const deltaX = (e.clientX - centerX) * this.strengthValue;
        const deltaY = (e.clientY - centerY) * this.strengthValue;

        this.element.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
    }

    _handleMouseLeave() {
        this.element.style.transform = 'translate(0, 0)';
    }

    disconnect() {
        this.element.removeEventListener('mousemove', this._onMouseMove);
        this.element.removeEventListener('mouseleave', this._onMouseLeave);
        this.element.style.transform = '';
        this.element.style.transition = '';
        this.element.style.willChange = '';
    }
}
