import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Http/Livewire/**/*.php',
        './app/Http/Livewire/**/*.blade.php',
    ],
    safelist: [
        // Dynamic badge/status colors
        { pattern: /bg-(red|green|yellow|amber|blue|purple|orange|indigo|emerald|gray)-(50|100|200|500|600|700)/ },
        { pattern: /text-(red|green|yellow|amber|blue|purple|orange|indigo|emerald|gray)-(600|700|800)/ },
        { pattern: /border-(red|green|yellow|amber|blue|purple|orange|indigo|emerald|gray)-(200|300|400|500)/ },
        // Progress bars
        { pattern: /w-\d+\/\d+/ },
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50:  '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
            },
            animation: {
                'fade-in':   'fadeIn 0.15s ease-in-out',
                'slide-down': 'slideDown 0.2s ease-out',
                'slide-up':   'slideUp 0.2s ease-out',
            },
            keyframes: {
                fadeIn: {
                    '0%':   { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideDown: {
                    '0%':   { opacity: '0', transform: 'translateY(-8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideUp: {
                    '0%':   { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
