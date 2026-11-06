import js from '@eslint/js';
import globals from 'globals';
import vue from 'eslint-plugin-vue';

export default [
    {
        ignores: ['node_modules/**', 'public/**', 'vendor/**', 'storage/**', 'bootstrap/cache/**'],
    },
    js.configs.recommended,
    ...vue.configs['flat/recommended'],
    {
        files: ['resources/js/**/*.{js,vue}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
            },
        },
        rules: {
            'vue/no-unused-vars': 'error',
            'no-console': 'off',
            'no-unused-vars': ['error', { caughtErrors: 'none' }],
            'vue/require-prop-types': 'off',
            'vue/no-v-html': 'off',
            'vue/html-indent': ['error', 2, {
                attribute: 1,
                closeBracket: 0,
                alignAttributesVertically: true,
                ignores: [],
            }],
            'vue/max-attributes-per-line': ['error', {
                singleline: 4,
                multiline: 1,
            }],
        },
    },
];
