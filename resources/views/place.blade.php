<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $place->name }} — {{ $place->category?->name }} в районе {{ $place->district?->name }} | ВИЗИТ ДОНЕЦК</title>
    <meta name="description" content="{{ $place->short_description }} Рейтинг {{ number_format($place->rating, 1, '.', '') }}, отзывов: {{ $place->reviews_count }}. Адрес: {{ $place->address }}.">
    <link rel="canonical" href="{{ request()->url() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="place">
    <meta property="og:title" content="{{ $place->name }} — ВИЗИТ ДОНЕЦК">
    <meta property="og:description" content="{{ $place->short_description }}">
    <meta property="og:image" content="{{ $place->cover_url ? asset($place->cover_url) : '' }}">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:locale" content="ru_RU">

    {{-- SEO: LocalBusiness --}}
    @php
        $ld = array_filter([
            '@context'  => 'https://schema.org',
            '@type'     => 'LocalBusiness',
            'name'      => $place->name,
            'description' => $place->short_description,
            'image'     => $place->cover_url ? asset($place->cover_url) : null,
            'telephone' => $place->phone,
            'url'       => $place->site,
            'priceRange' => str_repeat('$', $place->price_level),
            'address'   => [
                '@type' => 'PostalAddress',
                'streetAddress' => $place->address,
                'addressLocality' => 'Донецк',
                'addressCountry' => 'RU',
            ],
            'geo' => $place->lat ? [
                '@type' => 'GeoCoordinates',
                'latitude'  => (float) $place->lat,
                'longitude' => (float) $place->lng,
            ] : null,
            'aggregateRating' => $place->reviews_count ? [
                '@type' => 'AggregateRating',
                'ratingValue' => (float) $place->rating,
                'reviewCount' => $place->reviews_count,
            ] : null,
        ]);
    @endphp
    <script type="application/ld+json">@json($ld)</script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/place.js'])
</head>
<body>

<header class="hdr scrolled">
    <div class="wrap hdr-in">
        <a class="logo" href="/">ВИЗИТ<em>ДОНЕЦК</em></a>
        <nav class="nav-links">
            <a href="/#places">Места</a><a href="/#map">Карта</a><a href="/#events">Афиша</a>
        </nav>
        <div class="hdr-actions">
            <a class="btn btn-ghost btn-sm" href="javascript:history.back()">← Назад</a>
        </div>
    </div>
</header>

<main class="wrap place-wrap">
    <nav class="crumbs reveal">
        <a href="/">Главная</a> / <a href="/#map">{{ $place->category?->name }}</a> / <span>{{ $place->name }}</span>
    </nav>

    <div class="pl-grid">
        {{-- галерея --}}
        <div class="reveal">
            <img id="plMain" class="pl-main" src="{{ safe_img($place->cover_url) }}" alt="{{ $place->name }}">
            <div class="pl-thumbs">
                @foreach($place->photos as $ph)
                    <img src="{{ safe_img($ph->path) }}" alt="" class="{{ $ph->is_cover ? 'on' : '' }}"
                         onclick="setMain(this)">
                @endforeach
            </div>
        </div>

        {{-- инфо-карточка --}}
        <aside class="glass pl-info reveal">
            <div class="kicker">{{ $place->category?->icon }} {{ $place->category?->name }} · {{ $place->district?->name }}</div>
            <h1 style="font-size:clamp(24px,3vw,36px)">{{ $place->name }}</h1>
            <div class="pl-rate">
                <span class="stars">{{ str_repeat('★', (int)$place->rating) }}</span>
                <b>{{ number_format($place->rating, 1, '.', '') }}</b>
                <span style="color:var(--mut)">{{ $place->reviews_count }} отзывов</span>
                <span class="p-price" style="margin-left:auto">
                    @for($i=1;$i<=3;$i++)@if($i <= $place->price_level)<b>$</b>@else $@endif @endfor
                </span>
            </div>

            <p class="pl-addr">📍 {{ $place->address }}</p>

            <div class="pl-actions" style="flex-wrap:wrap;gap:8px">
                <a class="btn btn-grad" href="tel:{{ preg_replace('/[^+\d]/', '', $place->phone) }}">📞 Позвонить</a>
                <a class="btn btn-ghost" target="_blank" rel="nofollow"
                   href="https://yandex.ru/maps/?text={{ $place->lat }},{{ $place->lng }}">Маршрут</a>
                @if($place->external_id && $place->external_source === 'mypwa.ru')
                    <button
                        type="button"
                        class="pl-menu-btn"
                        id="openMenuBtn"
                        data-place-id="{{ $place->id }}"
                    >
                        <span class="pl-menu-btn__ico">📋</span>
                        <span class="pl-menu-btn__txt">
                            <b>Меню</b>
                            <small>Прайс-лист</small>
                        </span>
                    </button>
                @endif
            </div>

            @if($place->working_hours)
                <div class="pl-hours">
                    @foreach($place->working_hours as $h)
                        <div><span>{{ $h['d'] ?? '' }}</span><b>{{ $h['from'] ?? '' }}–{{ $h['to'] ?? '' }}</b></div>
                    @endforeach
                </div>
            @endif

            <div class="pl-soc">
                @if($place->site)<a href="{{ $place->site }}" rel="nofollow">🌐 Сайт</a>@endif
                @if($place->socials['telegram'] ?? null)<a href="https://t.me/{{ ltrim($place->socials['telegram'], '@') }}" rel="nofollow">✈️ Telegram</a>@endif
                @if($place->socials['vk'] ?? null)<a href="https://{{ ltrim($place->socials['vk'], 'https://') }}" rel="nofollow">💙 VK</a>@endif
            </div>
        </aside>
    </div>

    <section class="vcard reveal" style="margin-top:26px">
        <h2 style="margin-top:0">О месте</h2>
        <p style="color:var(--mut);max-width:800px">{{ $place->description }}</p>
    </section>

    {{-- отзывы --}}
    <section class="pl-reviews reveal" id="reviews">
        <h2>Отзывы ({{ $place->reviews->count() }})</h2>

        @forelse($place->reviews as $r)
            <div class="rev">
                <div class="rev-head">
                    <b>{{ $r->user?->name ?? $r->author_name }}</b>
                    <span class="stars">{{ str_repeat('★', $r->rating) }}</span>
                    <span style="color:var(--mut);font-size:12px">{{ $r->created_at->format('d.m.Y') }}</span>
                </div>
                <p>{{ $r->text }}</p>
            </div>
        @empty
            <p style="color:var(--mut)">Пока нет отзывов — будьте первым!</p>
        @endforelse

        <form id="reviewForm" class="glass" style="padding:22px;margin-top:18px" data-slug="{{ $place->slug }}">
            <h3 style="margin-top:0">Оставить отзыв</h3>
            <div class="f-2">
                <input class="f-in" name="author_name" placeholder="Ваше имя"
                       value="{{ auth()->user()?->name }}">
                <select class="f-in" name="rating">
                    <option value="5">★★★★★ — отлично</option>
                    <option value="4">★★★★ — хорошо</option>
                    <option value="3">★★★ — нормально</option>
                    <option value="2">★★ — плохо</option>
                    <option value="1">★ — ужасно</option>
                </select>
            </div>
            <textarea class="f-in" name="text" rows="3" placeholder="Поделитесь впечатлением…" style="margin-top:12px"></textarea>
            <button class="btn btn-grad" type="submit" style="margin-top:14px">Отправить на модерацию</button>
        </form>
    </section>

    {{-- похожие --}}
    @if($similar->count())
        <section class="reveal" style="margin:36px 0 60px">
            <h2>Похожие места</h2>
            <div class="rail" style="margin-top:16px">
                @foreach($similar as $p)
                    <a class="p-card" href="/place/{{ $p->slug }}">
                        <div class="p-img"><img src="{{ safe_img($p->cover_url) }}" alt=""><span class="p-rate">★ {{ number_format($p->rating, 1, '.', '') }}</span></div>
                        <div class="p-body"><h3>{{ $p->name }}</h3><p class="p-meta">{{ $p->category?->name }} · {{ $p->district?->name }}</p></div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</main>

<div class="toast" id="toast"></div>

<!-- Модалка меню / прайс-листа -->
<div class="menu-modal" id="menuModal">
    <div class="menu-modal__backdrop" data-close-menu></div>
    <div class="menu-modal__panel">
        <div class="menu-modal__head">
            <div>
                <div class="menu-modal__kicker">Прайс-лист</div>
                <h3 class="menu-modal__title" id="menuTitle">{{ $place->name }}</h3>
                <div class="menu-modal__meta" id="menuMeta"></div>
            </div>
            <button class="menu-modal__close" data-close-menu aria-label="Закрыть">✕</button>
        </div>

        <div class="menu-modal__search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="menuSearch" placeholder="Поиск по меню…">
            <button type="button" class="menu-modal__clear" id="menuSearchClear" hidden>✕</button>
        </div>

        <div class="menu-modal__body" id="menuBody"></div>
    </div>
</div>

{{-- Встроенный JS для модалки меню --}}
<script>
    (function() {
        const modal   = document.getElementById('menuModal');
        const body    = document.getElementById('menuBody');
        const meta    = document.getElementById('menuMeta');
        const search  = document.getElementById('menuSearch');
        const sClear  = document.getElementById('menuSearchClear');
        const openBtn = document.getElementById('openMenuBtn');

        if (!modal || !openBtn) return;

        let items  = [];
        let loaded = false;

        /* ---------- открытие ---------- */
        openBtn.addEventListener('click', async () => {
            openMenu();
            if (!loaded) await loadMenu(openBtn.dataset.placeId);
        });

        document.querySelectorAll('[data-close-menu]').forEach(el => {
            el.addEventListener('click', closeMenu);
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && modal.classList.contains('open')) closeMenu();
        });

        search.addEventListener('input', e => {
            const q = e.target.value.trim().toLowerCase();
            sClear.hidden = !q;
            renderMenu(q);
        });
        sClear.addEventListener('click', () => {
            search.value = '';
            sClear.hidden = true;
            renderMenu('');
        });

        /* ---------- логика ---------- */
        function openMenu() {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }

        async function loadMenu(placeId) {
            body.innerHTML = `
            <div class="menu-modal__state">
                <div class="menu-modal__spinner"></div>
                <p>Загружаем меню…</p>
            </div>`;
            try {
                const r = await fetch(`/api/v1/places/${placeId}/menu`, {
                    headers: { Accept: 'application/json' },
                });
                if (!r.ok) throw new Error('HTTP ' + r.status);
                const data = await r.json();
                items  = Array.isArray(data.data) ? data.data : [];
                loaded = true;
                meta.textContent = items.length
                    ? `${items.length} ${pluralize(items.length, ['позиция','позиции','позиций'])}`
                    : 'Меню пока пустое';
                renderMenu('');
            } catch (err) {
                body.innerHTML = `
                <div class="menu-modal__state menu-modal__state--error">
                    <div class="menu-modal__ico">⚠</div>
                    <h4>Не удалось загрузить меню</h4>
                    <p>Попробуйте обновить страницу</p>
                </div>`;
            }
        }

        function renderMenu(query) {
            if (!items.length) {
                body.innerHTML = `
                <div class="menu-modal__state">
                    <div class="menu-modal__ico">📋</div>
                    <h4>Меню пока не добавлено</h4>
                </div>`;
                return;
            }
            const q = (query || '').trim().toLowerCase();
            const filtered = q
                ? items.filter(it =>
                    (it.name || '').toLowerCase().includes(q) ||
                    (it.description || '').toLowerCase().includes(q) ||
                    (it.category || '').toLowerCase().includes(q))
                : items;

            if (!filtered.length) {
                body.innerHTML = `
                <div class="menu-modal__state">
                    <div class="menu-modal__ico">🔍</div>
                    <h4>Ничего не найдено</h4>
                </div>`;
                return;
            }

            const grouped = {};
            filtered.forEach(it => {
                const cat = it.category || 'Без категории';
                (grouped[cat] ||= []).push(it);
            });

            body.innerHTML = Object.entries(grouped).map(([cat, list]) => `
            <section class="menu-modal__group">
                <h4 class="menu-modal__group-title">${escape(cat)}</h4>
                <div class="menu-modal__grid">
                    ${list.map(renderItem).join('')}
                </div>
            </section>
        `).join('');
        }

        function renderItem(it) {
            const price = typeof it.price === 'number'
                ? new Intl.NumberFormat('ru-RU').format(it.price) + ' ₽'
                : '—';

            const desc = (it.description || '')
                .replace(/Цена:\s*\d+\s*(?:руб(?:лей)?|₽)\.?/gi, '')
                .replace(/Вес:\s*\d+\s*(?:грамм|г|гр|мл|ml)[^.]*/gi, '')
                .replace(/#[a-zA-Zа-яА-ЯёЁ]+/g, '')
                .trim();

            // Используем твой /assets/placeholder.jpg как fallback
            const fallback = '/assets/placeholder.jpg';

            return `
    <article class="menu-item">
        <div class="menu-item__img">
            <img
                src="${escapeAttr(it.image || fallback)}"
                alt="${escapeAttr(it.name)}"
                loading="lazy"
                onerror="this.onerror=null;this.src='${fallback}';this.classList.add('img-fallback')"
            >
        </div>
        <div class="menu-item__body">
            <h5 class="menu-item__name">${escape(it.name)}</h5>
            ${desc ? `<p class="menu-item__desc">${escape(desc)}</p>` : ''}
            <div class="menu-item__foot">
                <span class="menu-item__price">${price}</span>
                ${it.weight ? `<span class="menu-item__weight">${escape(it.weight)}</span>` : ''}
            </div>
        </div>
    </article>`;
        }

        function escape(s) {
            return String(s ?? '').replace(/[&<>"']/g, c =>
                ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
        }
        function escapeAttr(s) { return escape(s); }
        function pluralize(n, forms) {
            n = Math.abs(n) % 100;
            const n1 = n % 10;
            if (n > 10 && n < 20) return forms[2];
            if (n1 > 1 && n1 < 5) return forms[1];
            if (n1 === 1) return forms[0];
            return forms[2];
        }
    })();
</script>
</body>
</html>
