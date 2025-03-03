import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/echo.js',
            ],
            refresh: true,
        }),
        VitePWA({
                registerType: 'autoUpdate',
                manifest: {
                    name: 'Daiboken',
                    short_name: 'DBK',
                    start_url: '/',
                    display: 'standalone',
                    background_color: '#ffffff',
                    theme_color: '#000000',
                    icons: [
                        {
                            src: '/images/icons/icon-192x192.png',
                            sizes: '192x192',
                            type: 'image/png',
                        },
                        {
                            src: '/images/icons/icon-512x512.png',
                            sizes: '512x512',
                            type: 'image/png',
                        },
                    ],
                },
                workbox: {
                    runtimeCaching: [
                        {
                            urlPattern: ({ request }) => request.destination === 'document',
                            handler: 'NetworkFirst',
                            options: {
                                cacheName: 'html-cache',
                            },
                        },
                        {
                            urlPattern: ({ request }) => request.destination === 'image',
                            handler: 'CacheFirst',
                            options: {
                                cacheName: 'image-cache',
                            },
                        },
                    ],
                },
            }),
        ],
    });
