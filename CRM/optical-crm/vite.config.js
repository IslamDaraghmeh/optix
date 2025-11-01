import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        // Production build optimizations
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
            },
        },
        rollupOptions: {
            output: {
                manualChunks: {
                    // Split vendor code for better caching
                    vendor: ['axios'],
                },
            },
        },
        chunkSizeWarningLimit: 1000,
        cssCodeSplit: true,
        sourcemap: false, // Disable sourcemaps in production for smaller files
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
