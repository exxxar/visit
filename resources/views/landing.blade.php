<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>ВИЗИТ ДОНЕЦК — главный путеводитель по городу</title>
    <meta name="description" content="{{ $settings['hero']['sub'] ?? 'Все лучшие места Донецка — в одном путеводителе.' }}">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#05070d">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Визит Донецк">
    <meta name="application-name" content="Визит Донецк">

    <link rel="manifest" href="/build/manifest.webmanifest">

    <!-- ВЕРСИЯ ПРИЛОЖЕНИЯ ИЗ БЕКЕНДА -->
    <meta name="app-version" content="{{ $settings['app_version'] ?? '1.0.0' }}">

    <link rel="icon" type="image/png" href="/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700;800;900&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])

    <style>
        /* Стили для тоста обновления (впишутся в твой дизайн) */
        .pwa-update-toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(150%);
            z-index: 9999;
            width: min(90vw, 400px);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .pwa-update-toast[hidden] {
            display: block; /* Чтобы работала анимация */
            transform: translateX(-50%) translateY(150%);
        }
        .pwa-update-toast.show {
            transform: translateX(-50%) translateY(0);
        }
        .pwa-update-toast__content {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 20px;
            background: rgba(10, 14, 24, 0.92);
            border: 1px solid rgba(34, 211, 238, 0.3);
            border-radius: 18px;
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
        .pwa-update-toast__ico { font-size: 28px; }
        .pwa-update-toast__text { flex: 1; display: flex; flex-direction: column; }
        .pwa-update-toast__text b { font-size: 14px; color: var(--txt); }
        .pwa-update-toast__text span { font-size: 12px; color: var(--mut); margin-top: 2px; }
    </style>

</head>
<body>
@include('landing.header')
@include('landing.hero')
@include('landing.partners')
@include('landing.quick')
@include('landing.bento')
@include('landing.popular')
@include('landing.moods')
@include('landing.districts')
@include('landing.map')
@include('landing.new')
@include('landing.editorial')
@include('landing.events')
@include('landing.print')
@include('landing.business')
@include('landing.nearby')
@include('landing.subscribe')
@include('landing.footer')
@include('landing.mnav')
@include('landing.modals')
@include('landing.stories')
@include('landing.feedback')

<script>window.LANDING = @js($payload)</script>



<!-- UI для обновления PWA (скрыт по умолчанию) -->
<div id="pwa-update-toast" class="pwa-update-toast" hidden>
    <div class="pwa-update-toast__content">
        <span class="pwa-update-toast__ico">🚀</span>
        <div class="pwa-update-toast__text">
            <b>Доступна новая версия</b>
            <span>Обновите приложение для лучших впечатлений</span>
        </div>
        <button id="pwa-update-btn" class="btn btn-grad btn-sm">Обновить</button>
    </div>
</div>

{{-- Затемнение для панели ИИ --}}
<div class="ai-backdrop" id="aiBackdrop"></div>

{{-- Боковая панель ИИ-помощника --}}
<aside class="ai-panel" id="aiPanel">
    <div class="ai-panel__header">
        <div>
            <div class="ai-panel__kicker">ИИ-помощник</div>
            <h3 class="ai-panel__title" id="aiPanelTitle">Подборка для вас</h3>
        </div>
        <button class="ai-panel__close" id="closeAiPanel">✕</button>
    </div>

    <div class="ai-panel__messages" id="aiMessages">
        {{-- Приветствие появится здесь динамически --}}
    </div>

    <form class="ai-panel__input" id="aiForm">
        <input type="text" id="aiInput" class="ai-input" placeholder="Уточните запрос..." autocomplete="off">
        <button type="submit" class="ai-send-btn" id="aiSendBtn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
    </form>
</aside>
</body>
</html>

