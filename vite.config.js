import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/profile-form.js',
                'resources/js/profile-avatar.js',
                'resources/js/post-slug-input.js',
                'resources/js/post-form.js',
                'resources/js/post-theme-preview.js',
                'resources/js/post-author-select.js',
                'resources/js/tinymce-editor.js',
                'resources/js/post-body-code.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
