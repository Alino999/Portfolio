import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/admin.css',
                'resources/js/admin.js',
                'resources/js/app.js',
                'resources/css/portfolio.css',
                'resources/js/portfolio.js',
            ],
            refresh: true,
        }),
    ],
});
