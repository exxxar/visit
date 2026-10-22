import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/landing.css',
                'resources/css/place.css',
                'resources/js/landing.js',
                'resources/css/admin.css',
                'resources/js/app.js',
                'resources/js/place.js',
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
        VitePWA({
            registerType: 'autoUpdate', // ← ВАЖНО: меняем с 'prompt' на 'autoUpdate'
            injectRegister: 'auto',
            includeAssets: ['favicon.png', 'apple-touch-icon.png', 'masked-icon.svg'],
            manifest: {
                id: '/',
                name: 'КОД НОВОРОССИИ',
                short_name: 'КОД НОВОРОССИИ',
                description: 'Главный путеводитель по городу',
                theme_color: '#05070d',
                background_color: '#05070d',
                display: 'standalone',
                orientation: 'portrait',
                scope: '/',
                start_url: '/',
                icons: [
                    {
                        src: '/icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'any'
                    },
                    {
                        src: '/icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any'
                    }
                ],
                screenshots: [
                    {
                        src: '/screenshots/mobile.png',
                        sizes: '540x720',
                        type: 'image/png',
                        form_factor: 'narrow'
                    },
                    {
                        src: '/screenshots/desktop.png',
                        sizes: '1280x720',
                        type: 'image/png',
                        form_factor: 'wide'
                    }
                ]
            },
            workbox: {
                // Кэшируем только статику, НЕ html
                globPatterns: ['**/*.{js,css,ico,png,svg,webp,jpg,jpeg,woff,woff2}'],

                // Что показывать, если пользователь офлайн и запрашивает страницу
                navigateFallback: '/offline.html',

                // Разрешаем навигационные fallback для всех путей
                navigateFallbackAllowlist: [/^(?!.*\/api\/).*/],

                runtimeCaching: [
                    // 1. Навигация (HTML-страницы) — всегда сначала сеть, потом кэш
                    {
                        urlPattern: ({ request }) => request.mode === 'navigate',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pages-cache',
                            networkTimeoutSeconds: 3,
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 60 * 60 * 24 * 30 // 30 дней
                            }
                        }
                    },
                    // 2. Шрифты Google
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-cache',
                            expiration: { maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 365 },
                            cacheableResponse: { statuses: [0, 200] }
                        }
                    },
                    // 3. Изображения со сторонних ресурсов
                    {
                        urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp)$/,
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'images-cache',
                            expiration: { maxEntries: 60, maxAgeSeconds: 60 * 60 * 24 * 7 }
                        }
                    }
                ]
            },
            devOptions: {
                enabled: false
            }
        })
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
});
