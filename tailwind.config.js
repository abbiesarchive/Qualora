import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            // COLOR PALETTE
            colors: {
                cream: { DEFAULT: '#FBF6EF', dark: '#F3EADD' },
                ink: { DEFAULT: '#2D2230', soft: '#6B5B6E' },
                // Primary corally
                brand: {
                    50: '#FFF3EF', 100: '#FFE3DA', 200: '#FFC9B8', 300: '#F8A892', 400: '#EE8A74',
                    500: '#E8745F', 600: '#D45F4A', 700: '#B34A38', 800: '#8F3C2E', 900: '#6E2F25', 950: '#3E1812',
                },
                // gradient??
                glow: { 100: '#FDEFD6', 300: '#F7CD8F', 400: '#F2B263', 500: '#E89F3C', 700: '#9A6212' },
                // accent soft purple
                aura: { 100: '#EFE9FA', 300: '#B9A6E3', 500: '#8F77CC', 700: '#5E4A9A' },
                // status
                sage: { 100: '#E5F0E4', 300: '#A9CBA8', 600: '#4F7A52', 800: '#33533A' },
                clay: { 100: '#F3E4DF', 300: '#D9B3A9', 600: '#9C5F52', 800: '#6B3F36' },
            },
            backgroundImage: {
                'aura-gradient': 'linear-gradient(135deg, #E8745F 0%, #F2B263 100%)',
                'aura-glow': 'radial-gradient(60% 60% at 50% 40%, rgba(242,178,99,0.35) 0%, rgba(232,116,95,0.15) 45%, rgba(251,246,239,0) 75%)',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
        },
    },

    plugins: [forms],
};
