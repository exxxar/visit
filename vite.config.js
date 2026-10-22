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
            registerType: 'prompt',
            injectRegister: 'auto',
            includeAssets: ['favicon.ico', 'apple-touch-icon.png', 'masked-icon.svg'],
            manifest: {
                id: '/', // ✅ ИСПРАВЛЕНИЕ 5: Явный ID приложения
                name: 'ВИЗИТ ДОНЕЦК',
                short_name: 'Визит ДНР',
                description: 'Главный путеводитель по городу',
                theme_color: '#05070d',
                background_color: '#05070d',
                display: 'standalone',
                display_override: ['standalone', 'window-controls-overlay'], // ✅ ИСПРАВЛЕНИЕ 8 (Опционально)
                orientation: 'portrait',
                scope: '/',
                start_url: '/',

                // ✅ ИСПРАВЛЕНИЕ 3 и 4: Разделяем purpose и используем правильные размеры
                icons: [
                    {
                        src: '/icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'any' // Убрали 'maskable', если иконка не имеет специальных безопасных полей
                    },
                    {
                        src: '/icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any'
                    }
                ],

                // ✅ ИСПРАВЛЕНИЕ 1 и 2: Добавляем скриншоты для десктопа и мобильных
                screenshots: [
                    {
                        src: '/screenshots/mobile.png', // <-- ТЫ ДОЛЖЕН СОЗДАТЬ ЭТОТ ФАЙЛ
                        sizes: '540x720',               // <-- УКАЖИ РЕАЛЬНЫЕ РАЗМЕРЫ ЭТОЙ КАРТИНКИ
                        type: 'image/png',
                        form_factor: 'narrow'           // Для мобильных
                    },
                    {
                        src: '/screenshots/desktop.png', // <-- ТЫ ДОЛЖЕН СОЗДАТЬ ЭТОТ ФАЙЛ
                        sizes: '1280x720',               // <-- УКАЖИ РЕАЛЬНЫЕ РАЗМЕРЫ ЭТОЙ КАРТИНКИ
                        type: 'image/png',
                        form_factor: 'wide'              // Для компьютеров
                    }
                ]
            },
            workbox: {
                globPatterns: ['**/*.{js,css,ico,png,svg,webp,jpg,jpeg,woff,woff2}'],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-cache',
                            expiration: { maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 365 },
                            cacheableResponse: { statuses: [0, 200] }
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
