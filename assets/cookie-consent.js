/**
 * Simple GDPR Cookie Consent Manager
 * Blocks ad scripts until consent is granted.
 */

document.addEventListener('DOMContentLoaded', function () {
    const consentBanner = document.getElementById('cookie-consent-banner');
    const acceptBtn = document.getElementById('btn-accept-cookies');
    const rejectBtn = document.getElementById('btn-reject-cookies');

    // Check if consent is already stored
    const consent = localStorage.getItem('cookie_consent');

    if (!consent) {
        // Show banner if no choice made yet
        setTimeout(() => {
            consentBanner.classList.remove('translate-y-full');
        }, 1000);
    } else if (consent === 'granted') {
        loadAdScripts();
    }

    // Handle Accept
    if (acceptBtn) {
        acceptBtn.addEventListener('click', function () {
            localStorage.setItem('cookie_consent', 'granted');
            consentBanner.classList.add('translate-y-full');
            loadAdScripts();
        });
    }

    // Handle Reject
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function () {
            localStorage.setItem('cookie_consent', 'denied');
            consentBanner.classList.add('translate-y-full');
            // Do not load ads
        });
    }

    /**
     * Loads Google AdSense and other tracking scripts
     */
    function loadAdScripts() {
        console.log('Consent granted. Loading ads...');

        // Find all lazy-ad-scripts and activate them
        const adScripts = document.querySelectorAll('script[data-type="ad-script"]');

        adScripts.forEach(script => {
            const newScript = document.createElement('script');
            newScript.src = script.getAttribute('data-src');
            newScript.async = true;
            newScript.crossOrigin = "anonymous";
            document.head.appendChild(newScript);
        });

        // Push ads for existing slots
        if (window.adsbygoogle) {
            // Logic to refresh ads if needed, usually auto-handled by script load
        }
    }
});
