<header class="hdr scrolled">
    <div class="wrap hdr-in">
        <a class="logo" href="/">ВИЗИТ<em>ДОНЕЦК</em></a>
        <nav class="nav-links">
            <a href="/#places">Места</a>
            <a href="/#map">Карта</a>
            <a href="/#events">Афиша</a>
        </nav>
        <div class="hdr-actions">
            {{-- Кнопка установки PWA --}}
            <button id="pwaInstallBtn" class="btn btn-ghost btn-sm" style="display: none;">
                <span>📲</span> Установить
            </button>

            @auth
                @php
                    $user = auth()->user();
                    $dashboardUrl = match (true) {
                        $user->hasAnyRole(['admin', 'moderator', 'editor']) => '/admin',
                        $user->hasAnyRole(['business', 'tech_partner', 'ad_partner']) => '/account',
                        default => '/account-users',
                    };

                    $dashboardLabel = match (true) {
                        $user->hasAnyRole(['admin', 'moderator', 'editor']) => '⚙️ Админ',
                        $user->hasAnyRole(['business', 'tech_partner', 'ad_partner']) => '🏢 Кабинет',
                        default => '🧳 Профиль',
                    };
                @endphp

                <a class="btn btn-ghost btn-sm" href="{{ $dashboardUrl }}">
                    <span>{{ $dashboardLabel }}</span>
                </a>
            @else
                {{-- Если не авторизован — показываем кнопку входа --}}
                <a class="btn btn-ghost btn-sm" href="/login">
                    <span>🔑</span> Войти
                </a>
            @endauth

            <a class="btn btn-grad btn-sm" href="/ref/choose">Добавить место</a>
        </div>
    </div>
</header>
