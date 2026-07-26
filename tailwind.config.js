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
            // Keep Figtree as the default sans for the rest of the app; the
            // landing page sets its own faces explicitly via the keys below.
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                'label-bold': ['Hanken Grotesk', ...defaultTheme.fontFamily.sans],
                'label-sm': ['Hanken Grotesk', ...defaultTheme.fontFamily.sans],
                'body-md': ['Hanken Grotesk', ...defaultTheme.fontFamily.sans],
                'body-lg': ['Hanken Grotesk', ...defaultTheme.fontFamily.sans],
                'headline-md': ['Anton', ...defaultTheme.fontFamily.sans],
                'headline-lg': ['Anton', ...defaultTheme.fontFamily.sans],
                'display-lg': ['Anton', ...defaultTheme.fontFamily.sans],
                'display-lg-mobile': ['Anton', ...defaultTheme.fontFamily.sans],
            },

            // Material Design 3 token set used by the landing page, plus the
            // brand accent (gold) used throughout the martial-arts theme.
            colors: {
                'on-primary-fixed': '#410004',
                'primary-fixed': '#ffdad7',
                'on-primary-fixed-variant': '#930014',
                'tertiary-fixed-dim': '#c6c6c7',
                'surface-container-high': '#e9e8e7',
                'on-background': '#1b1c1c',
                'outline': '#926f6c',
                'on-primary': '#ffffff',
                'on-tertiary': '#ffffff',
                'surface-container-low': '#f5f3f3',
                'primary-fixed-dim': '#ffb3ae',
                'surface': '#fbf9f8',
                'error': '#ba1a1a',
                'on-error-container': '#93000a',
                'on-tertiary-container': '#fbfbfb',
                'surface-bright': '#fbf9f8',
                'on-error': '#ffffff',
                'surface-variant': '#e4e2e2',
                'on-primary-container': '#fff9f8',
                'tertiary-container': '#727474',
                'on-secondary': '#ffffff',
                'background': '#fbf9f8',
                'tertiary': '#5a5b5c',
                'on-secondary-container': '#636262',
                'secondary-fixed-dim': '#c8c6c5',
                'surface-container-highest': '#e4e2e2',
                'on-surface': '#1b1c1c',
                'on-secondary-fixed-variant': '#474746',
                'surface-container-lowest': '#ffffff',
                'on-tertiary-fixed-variant': '#454747',
                'surface-container': '#efeded',
                'inverse-surface': '#303031',
                'secondary-container': '#e2dfde',
                'primary-container': '#e21d2c',
                'outline-variant': '#e7bdb9',
                'primary': '#b9001c',
                'secondary': '#5f5e5e',
                'surface-tint': '#c0001d',
                'on-surface-variant': '#5d3f3d',
                'surface-dim': '#dbdad9',
                'inverse-primary': '#ffb3ae',
                'on-tertiary-fixed': '#1a1c1c',
                'on-secondary-fixed': '#1c1b1b',
                'inverse-on-surface': '#f2f0f0',
                'secondary-fixed': '#e5e2e1',
                'error-container': '#ffdad6',
                'tertiary-fixed': '#e2e2e2',
                'accent': '#FFD700',
                'on-accent': '#1b1c1c',
            },

            // Sharp corners for the bold combat-sport look (only the named
            // radii are flattened; rounded-full stays available).
            borderRadius: {
                DEFAULT: '0px',
                lg: '0px',
                xl: '0px',
            },

            // Named spacing scale used across the landing page.
            spacing: {
                xs: '4px',
                base: '8px',
                sm: '12px',
                md: '24px',
                gutter: '24px',
                lg: '48px',
                'container-max': '1280px',
                xl: '80px',
            },

            // Type scale pairing each display/body/label role with its
            // line-height, tracking and weight.
            fontSize: {
                'label-bold': ['14px', { lineHeight: '20px', letterSpacing: '0.05em', fontWeight: '700' }],
                'label-sm': ['12px', { lineHeight: '16px', fontWeight: '500' }],
                'body-md': ['16px', { lineHeight: '24px', fontWeight: '400' }],
                'body-lg': ['18px', { lineHeight: '28px', fontWeight: '400' }],
                'headline-md': ['32px', { lineHeight: '36px', letterSpacing: '0.01em', fontWeight: '400' }],
                'headline-lg': ['40px', { lineHeight: '44px', letterSpacing: '0.01em', fontWeight: '400' }],
                'display-lg': ['72px', { lineHeight: '72px', letterSpacing: '0.02em', fontWeight: '400' }],
                'display-lg-mobile': ['48px', { lineHeight: '48px', letterSpacing: '0.02em', fontWeight: '400' }],
            },
        },
    },

    corePlugins: {
        preflight: false,
        collapse: false,
        container: false,
        forms: false,
    },

    plugins: [],
};
