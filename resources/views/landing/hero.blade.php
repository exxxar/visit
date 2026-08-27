<section class="hero" id="top">
    <div class="orb orb1"></div><div class="orb orb2"></div><div class="orb orb3"></div>
    <div class="wrap hero-grid">
        <div class="hero-copy">
            <div class="kicker reveal">Городской digital-гид · {{ now()->year }}</div>
            <h1 class="hero-logo reveal">ВИЗИТ<span class="grad">ДОНЕЦК</span></h1>
            <p class="hero-sub reveal">{{ $settings['hero']['sub'] ?? 'Главный путеводитель по заведениям, услугам и отдыху города' }}</p>
            <p class="hero-add reveal">{{ $settings['hero']['add'] ?? 'Все лучшие места Донецка — в одном путеводителе.' }}</p>

            <form class="hero-search reveal" id="searchForm">
                <label for="searchInput">Куда хотите сходить?</label>
                <div class="search-row">
                    <input id="searchInput" type="text" placeholder="Ресторан, кофейня, SPA, барбершоп, автомойка…" autocomplete="off">
                    <button class="btn btn-grad" type="submit">Найти место</button>
                </div>
            </form>

            <div class="quick reveal">
                <button class="chip" data-cat="food">🍽 Поесть</button>
                <button class="chip" data-cat="coffee">☕ Выпить кофе</button>
                <button class="chip" data-cat="bars">🍸 Отдохнуть</button>
                <button class="chip" data-cat="shop">🛍 Купить</button>
                <button class="chip" data-cat="beauty">💆 Красота</button>
                <button class="chip" data-cat="leisure">🎭 Развлечения</button>
                <button class="chip" data-cat="sport">🏋 Спорт</button>
                <button class="chip" data-cat="auto">🚗 Авто</button>
            </div>
        </div>

        <div class="hero-art reveal">
            <div class="art-glow"></div>
            <img id="heroImg" src="assets/hero-palm.jpg" alt="Пальма Мерцалова — символ Донецка в неоновом арт-стиле">
            <div class="float-chip fc1">★ 4.9 · ДонМак</div>
            <div class="float-chip fc2">🍸 Fastoran · доставка c 10:00 до 18:30</div>
            <div class="float-chip fc3">📍 {{ $settings['counters']['places'] ?? '1 240' }} мест на карте</div>
        </div>
    </div>

    {{-- абстрактная карта, переход в контент --}}
    <div class="hero-map" aria-hidden="true">
        <svg viewBox="0 0 1200 260" preserveAspectRatio="xMidYMax slice" fill="none">
            <defs><linearGradient id="hg" x1="0" y1="0" x2="1" y2="0"><stop stop-color="#22d3ee"/><stop offset=".5" stop-color="#8b5cf6"/><stop offset="1" stop-color="#f050e0"/></linearGradient></defs>
            <path d="M-10 190 C 200 150, 420 210, 640 160 C 860 110, 1040 170, 1210 130" stroke="url(#hg)" stroke-width="1.6" opacity=".7"/>
            <path d="M-10 90 C 260 120, 520 60, 780 100 C 980 130, 1100 90, 1210 100" stroke="#22d3ee" stroke-width="1" opacity=".35"/>
            <path class="route" d="M60 240 C 300 190, 480 220, 640 150 C 780 90, 960 120, 1150 60" stroke="url(#hg)" stroke-width="2"/>
            <circle class="pt" cx="300" cy="176" r="4" fill="#22d3ee"/><circle class="pt2" cx="640" cy="160" r="5" fill="#f050e0"/>
            <circle class="pt3" cx="880" cy="128" r="4" fill="#a3e635"/><circle class="pt" cx="520" cy="188" r="3.5" fill="#ff8a3c"/>
            <circle class="pt2" cx="1050" cy="140" r="4" fill="#8b5cf6"/><circle class="pt3" cx="150" cy="196" r="3.5" fill="#4f7dff"/>
        </svg>
    </div>
</section>
