import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url'
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        watch: {
            ignored: ['**/vendor/**', '**/node_modules/**'],
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/scss/app.scss',
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
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            '@components': fileURLToPath(new URL('./resources/js/Components', import.meta.url)),
            '@layouts': fileURLToPath(new URL('./resources/js/Layouts', import.meta.url)),
            '@pages': fileURLToPath(new URL('./resources/js/Pages', import.meta.url)),
            '@plugins': fileURLToPath(new URL('./resources/js/Plugins', import.meta.url)),
            'ziggy-js': fileURLToPath(new URL('./vendor/tightenco/ziggy', import.meta.url)),
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
