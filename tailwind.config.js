import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                paper: 'var(--color-paper)',
                mist: 'var(--color-mist)',
                rule: 'var(--color-rule)',
                ink: 'var(--color-ink)',
                'ink-soft': 'var(--color-ink-soft)',
                accent: 'var(--color-accent)',
                'accent-deep': 'var(--color-accent-deep)',
                navy: 'var(--color-navy)',
                'navy-2': 'var(--color-navy-2)',
                'on-navy': 'var(--color-on-navy)',
                wa: 'var(--color-wa)',
                danger: 'var(--color-danger)',
                'danger-soft': 'var(--color-danger-soft)',
            },
            fontFamily: {
                display: ['Satoshi', ...defaultTheme.fontFamily.sans],
                body: ['Switzer', ...defaultTheme.fontFamily.sans],
                mono: ['"Geist Mono"', ...defaultTheme.fontFamily.mono],
            },
            boxShadow: {
                whisper: '0 1px 2px oklch(22% 0.01 262 / 0.06)',
                lift: '0 10px 28px -14px oklch(20% 0.02 262 / 0.4)',
            },
        },
    },
    plugins: [],
};
