import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Port Vite harus sama dengan VITE_PORT di .env (default 5273).
const vitePort = Number(process.env.VITE_PORT ?? 5273);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: vitePort,
        strictPort: true,
        cors: true,
        // Browser ada di Mac, server Vite ada di dalam container -> HMR harus
        // menunjuk ke localhost (port yang di-publish), bukan 0.0.0.0.
        hmr: {
            host: 'localhost',
            port: vitePort,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
