import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                gold: {
                    DEFAULT: '#D4AF37',
                    50: '#faf6e9',
                    100: '#f3e9c4',
                    200: '#ecdb9c',
                    300: '#e4cd73',
                    400: '#dcbf4b',
                    500: '#D4AF37',
                    600: '#b3922a',
                    700: '#8a701f',
                    800: '#624f15',
                    900: '#3a2f0c',
                },
                navy: {
                    DEFAULT: '#1a1a2e',
                    light: '#252540',
                    dark: '#12121f',
                },
                ink: {
                    900: '#0f0f0f',
                    850: '#161616',
                    800: '#1a1a1a',
                    700: '#222222',
                    600: '#2a2a2a',
                },
                brand: {
                    cyan: '#00d4ff',
                    purple: '#6d5cff',
                },
            },
        },
    },

    plugins: [forms],
};
