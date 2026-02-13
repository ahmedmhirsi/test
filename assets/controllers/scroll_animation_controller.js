import { Controller } from '@hotwired/stimulus';

/*
 * Scroll Animation Controller
 * Uses GSAP ScrollTrigger (loaded via CDN) for performant scroll-linked animations.
 * Leverages gsap.context() for automatic cleanup on Turbo navigation.
 *
 * Usage:
 *   <div data-controller="scroll-animation">
 *     <div data-scroll-animation-target="animatable">I fade up on scroll</div>
 *     <div data-scroll-animation-target="animatable" data-animation="scale">I scale in</div>
 *     <div data-scroll-animation-target="animatable" data-animation="slide-left">I slide from left</div>
 *   </div>
 */
export default class extends Controller {
    static targets = ['animatable'];

    connect() {
        // Wait a tick for GSAP CDN to be available
        this._initTimeout = setTimeout(() => this._initAnimations(), 100);
    }

    _initAnimations() {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
            // Fallback: use IntersectionObserver if GSAP not loaded
            this._useFallback();
            return;
        }

        gsap.registerPlugin(ScrollTrigger);

        // Create context for clean Turbo disconnect
        this.ctx = gsap.context(() => {
            this.animatableTargets.forEach((el, index) => {
                const animationType = el.dataset.animation || 'fade-up';
                const delay = index * 0.08;

                let fromVars = { opacity: 0, duration: 0.8, delay };
                let scrollConfig = {
                    trigger: el,
                    start: 'top 85%',
                    end: 'bottom 20%',
                    toggleActions: 'play none none reverse',
                };

                switch (animationType) {
                    case 'fade-up':
                        fromVars.y = 50;
                        break;
                    case 'scale':
                        fromVars.scale = 0.85;
                        break;
                    case 'slide-left':
                        fromVars.x = -60;
                        break;
                    case 'slide-right':
                        fromVars.x = 60;
                        break;
                    case 'fade':
                        break;
                    case 'scrub':
                        fromVars.y = 50;
                        scrollConfig.scrub = 1;
                        scrollConfig.toggleActions = undefined;
                        break;
                    default:
                        fromVars.y = 40;
                }

                gsap.from(el, {
                    ...fromVars,
                    scrollTrigger: scrollConfig,
                    ease: 'power3.out',
                });
            });
        }, this.element);
    }

    _useFallback() {
        // Lightweight IntersectionObserver fallback
        this._observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        this._observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.15, rootMargin: '0px 0px -50px 0px' }
        );

        this.animatableTargets.forEach((el) => this._observer.observe(el));
    }

    disconnect() {
        // Clean up GSAP context (kills all ScrollTriggers + resets styles)
        if (this.ctx) {
            this.ctx.revert();
        }
        // Clean up fallback observer
        if (this._observer) {
            this._observer.disconnect();
        }
        // Clean up timeout
        if (this._initTimeout) {
            clearTimeout(this._initTimeout);
        }
    }
}
