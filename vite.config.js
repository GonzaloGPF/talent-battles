import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';
import path from 'path';
import manifestSRI from 'vite-plugin-manifest-sri';
import vuetify from 'vite-plugin-vuetify';

export default defineConfig({
    server: {
        hmr: {
            host: 'localhost'
        },
    },
    define: {
        // __VUE_I18N_FULL_INSTALL__: true,
        // __VUE_I18N_LEGACY_API__: false,
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__ : false,
        __VUE_OPTIONS_API__: false,
        __VUE_PROD_DEVTOOLS__: false,
        __INTLIFY_PROD_DEVTOOLS__: false,
    },
    plugins: [
        laravel(['resources/js/app.js']),
        manifestSRI(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({ styles: { configFile: path.resolve(import.meta.dirname, 'resources/sass/vendor/vuetify.scss') } }),
        i18n(),
    ],
    resolve: {
        alias: {
                '@': path.resolve(import.meta.dirname, 'resources/js'),
        },
    },
});
