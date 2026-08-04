import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            /*
             * CSS dijadikan entry langsung supaya browser
             * tidak menunggu JavaScript sebelum menampilkan desain.
             */
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],

            /*
             * Browser otomatis diperbarui saat Blade,
             * CSS, atau JavaScript berubah.
             */
            refresh: true,
        }),
    ],
});
