import animations from 'tailwindcss-animatecss';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            screens: { // https://vuetifyjs.com/en/features/display-and-platform/#usage
                'sm': '600px',
                'md': '960px',
                'lg': '1280px',
                'xl': '1920px',
                '2xl': '2560px',
            },
            sizing: {
                'min-w-xs': '20rem',
            },
        },
        animatedSettings: {
            animatedSpeed: 1000,
            // heartBeatSpeed: 500,
            // hingeSpeed: 2000,
            // bounceInSpeed: 750,
            // bounceOutSpeed: 750,
            // animationDelaySpeed: 500,
            classes: ['infinite', 'slower', 'faster', 'rubberBand', 'fadeIn', 'fadeOut', 'fadeOutUp']
        }
    },
    plugins: [
        animations
    ],
}

