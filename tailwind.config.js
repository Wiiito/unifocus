import forms from '@tailwindcss/forms';

/**
 * O tema aponta para as CSS custom properties definidas em resources/css/app.css.
 * Consequência: as classes do Tailwind (bg-primary, text-on-surface, ...) trocam
 * de cor sozinhas quando [data-theme="dark"] entra no <html> — sem precisar
 * duplicar cada classe com a variante dark:.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    darkMode: ['selector', '[data-theme="dark"]'],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/View/Components/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: 'var(--primary)',
                    hover: 'var(--primary-hover)',
                    container: 'var(--primary-container)',
                    fixed: 'var(--primary-fixed)',
                    'fixed-dim': 'var(--primary-fixed-dim)',
                },
                'on-primary': {
                    DEFAULT: 'var(--on-primary)',
                    container: 'var(--on-primary-container)',
                    fixed: 'var(--on-primary-fixed)',
                },

                secondary: {
                    DEFAULT: 'var(--secondary)',
                    container: 'var(--secondary-container)',
                    fixed: 'var(--secondary-fixed)',
                    'fixed-dim': 'var(--secondary-fixed-dim)',
                },
                'on-secondary': {
                    DEFAULT: 'var(--on-secondary)',
                    container: 'var(--on-secondary-container)',
                    fixed: 'var(--on-secondary-fixed)',
                },

                tertiary: {
                    DEFAULT: 'var(--tertiary)',
                    container: 'var(--tertiary-container)',
                    fixed: 'var(--tertiary-fixed)',
                    'fixed-dim': 'var(--tertiary-fixed-dim)',
                },
                'on-tertiary': {
                    DEFAULT: 'var(--on-tertiary)',
                    container: 'var(--on-tertiary-container)',
                },

                background: 'var(--background)',
                'on-background': 'var(--on-background)',

                surface: {
                    DEFAULT: 'var(--surface)',
                    bright: 'var(--surface-bright)',
                    dim: 'var(--surface-dim)',
                    variant: 'var(--surface-variant)',
                    container: {
                        DEFAULT: 'var(--surface-container)',
                        lowest: 'var(--surface-container-lowest)',
                        low: 'var(--surface-container-low)',
                        high: 'var(--surface-container-high)',
                        highest: 'var(--surface-container-highest)',
                    },
                },
                'on-surface': {
                    DEFAULT: 'var(--on-surface)',
                    variant: 'var(--on-surface-variant)',
                },

                outline: {
                    DEFAULT: 'var(--outline)',
                    variant: 'var(--outline-variant)',
                },

                error: {
                    DEFAULT: 'var(--error)',
                    container: 'var(--error-container)',
                },
                'on-error': {
                    DEFAULT: 'var(--on-error)',
                    container: 'var(--on-error-container)',
                },

                streak: {
                    DEFAULT: 'var(--streak-fire)',
                    bg: 'var(--streak-fire-bg)',
                    border: 'var(--streak-fire-border)',
                },

                card: {
                    DEFAULT: 'var(--card-bg)',
                    border: 'var(--card-border)',
                },
                header: 'var(--header-bg)',
                glass: 'var(--glass-border)',
            },

            fontFamily: {
                sans: ['Hanken Grotesk', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                mono: ['JetBrains Mono', 'monospace'],
            },

            spacing: {
                xs: 'var(--spacing-xs)',
                base: 'var(--spacing-base)',
                sm: 'var(--spacing-sm)',
                gutter: 'var(--spacing-gutter)',
                md: 'var(--spacing-md)',
                lg: 'var(--spacing-lg)',
                xl: 'var(--spacing-xl)',
            },

            borderRadius: {
                sm: 'var(--radius-sm)',
                md: 'var(--radius-md)',
                lg: 'var(--radius-lg)',
                xl: 'var(--radius-xl)',
                full: 'var(--radius-full)',
            },

            boxShadow: {
                card: 'var(--card-shadow)',
                'card-hover': 'var(--card-shadow-hover)',
            },

            transitionTimingFunction: {
                normal: 'cubic-bezier(0.4, 0, 0.2, 1)',
                bounce: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
            },

            transitionDuration: {
                fast: '150ms',
                normal: '250ms',
                bounce: '350ms',
            },

            keyframes: {
                'flame-pulse': {
                    '0%': { transform: 'scale(1)', filter: 'drop-shadow(0 0 2px rgba(249, 115, 22, 0.4))' },
                    '100%': { transform: 'scale(1.15)', filter: 'drop-shadow(0 0 8px rgba(249, 115, 22, 0.8))' },
                },
                'urgent-ping': {
                    '0%': { boxShadow: '0 0 0 0 rgba(186, 26, 26, 0.5)' },
                    '70%': { boxShadow: '0 0 0 8px rgba(186, 26, 26, 0)' },
                    '100%': { boxShadow: '0 0 0 0 rgba(186, 26, 26, 0)' },
                },
                'slide-in': {
                    from: { opacity: '0', transform: 'translateY(20px) scale(0.9)' },
                    to: { opacity: '1', transform: 'translateY(0) scale(1)' },
                },
            },

            animation: {
                'flame-pulse': 'flame-pulse 2s ease-in-out infinite alternate',
                'urgent-ping': 'urgent-ping 1.8s infinite',
                'slide-in': 'slide-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)',
            },
        },
    },

    plugins: [forms],
};
