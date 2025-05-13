// import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

const defineConfig = ({
    plugins: [
        laravel({
            input: [
                'resources/css/scss/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '@components': '/resources/js/components',
            '@plugins': '/resources/js/plugins',
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});

export default defineConfig;
