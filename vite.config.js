import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    experimental: {
        // Chunks reference each other relatively, so the build works from a
        // subfolder too (e.g. http://localhost/app/public under XAMPP).
        renderBuiltUrl(filename, { hostType }) {
            return hostType === 'js' ? { relative: true } : filename;
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
