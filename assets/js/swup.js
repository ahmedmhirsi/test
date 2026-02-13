const swup = new Swup({
    containers: ["#swup", "#nav"],
    animationSelector: '[class*="swup-transition-"]',
    animateHistoryBrowsing: true
});

// Scroll to top on transition start
swup.hooks.before('content:replace', () => {
    window.scrollTo(0, 0);
});

// Re-initialize Stimulus controllers after content replacement
swup.hooks.on('content:replace', () => {
    console.log('Swup: Content replaced. Re-initializing scripts...');
    // If specific non-Stimulus libs need re-init, do it here.
});

export default swup;
