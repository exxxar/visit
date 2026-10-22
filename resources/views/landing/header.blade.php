<header class="hdr scrolled">
    <div class="wrap hdr-in">
        <a class="logo" href="/">КОД<em>НОВОРОССИИ</em></a>
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
                {{-- Если авторизован — показываем кабинет --}}
                <a class="btn btn-ghost btn-sm" href="/account">
                    <span>👤</span> Кабинет
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
