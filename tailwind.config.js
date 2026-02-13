/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
        "./src/Twig/Components/**/*.php",
    ],
    theme: {
        extend: {
            colors: {
                'nexus-black': '#161516', // Deep Black from doc
                'nexus-navy': '#10202A', // Top Navy from doc
                'nexus-slate': '#3D4D55', // Muted Slate from doc
                'nexus-stone': '#A79E9C', // Warm Grey/Stone from doc
                'nexus-cream': '#D3C3B9', // Executive Cream from doc
                'nexus-primary': '#B58863', // Bronze/Gold Accent from doc
                'nexus-accent': '#B58863', // Same as primary for consistency

                // OKLCH Palette (Approximation for Tailwind 3, or use native CSS var for dynamic mixing)
                'oklch-primary': 'oklch(65% 0.15 45)', // Example bronze-ish
                'oklch-navy': 'oklch(20% 0.05 250)',
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
                display: ['Outfit', 'sans-serif'],
                body: ['Manrope', 'sans-serif'],
            },
            backgroundImage: {
                'aurora-gradient': 'linear-gradient(to right bottom, #6366f1, #a855f7, #ec4899)',
                'cyber-grid': 'linear-gradient(to right, #1e293b 1px, transparent 1px), linear-gradient(to bottom, #1e293b 1px, transparent 1px)',
                'grainy-noise': "url(\"data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E\")",
            },
            animation: {
                'float': 'float 6s ease-in-out infinite',
                'float-delayed': 'float 6s ease-in-out 3s infinite',
                'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'spin-slow': 'spin 8s linear infinite',
                'morph': 'morph 8s ease-in-out infinite',
                'gradient-x': 'gradient-x 15s ease infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-20px)' },
                },
                morph: {
                    '0%, 100%': { borderRadius: '60% 40% 30% 70% / 60% 30% 70% 40%' },
                    '50%': { borderRadius: '30% 60% 70% 40% / 50% 60% 30% 60%' },
                },
                'gradient-x': {
                    '0%, 100%': {
                        'background-size': '200% 200%',
                        'background-position': 'right center'
                    },
                    '50%': {
                        'background-size': '200% 200%',
                        'background-position': 'left center'
                    },
                },
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
