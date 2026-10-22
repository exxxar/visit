<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $place->name }} — {{ $place->category?->name }} в районе {{ $place->district?->name }} | КОД НОВОРОССИИ</title>
    <meta name="description" content="{{ $place->short_description }} Рейтинг {{ number_format($place->rating, 1, '.', '') }}, отзывов: {{ $place->reviews_count }}. Адрес: {{ $place->address }}.">
    <link rel="canonical" href="{{ request()->url() }}">
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="place">
    <meta property="og:title" content="{{ $place->name }} — КОД НОВОРОССИИ">
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

    {{-- Передаем данные в JS --}}
    <script>
        window.PLACE_DATA = {
            id: {{ $place->id }},
            name: '{{ addslashes($place->name) }}',
            short_description: '{{ addslashes($place->short_description) }}',
            lat: {{ $place->lat ?? 'null' }},
            lng: {{ $place->lng ?? 'null' }},
            isOwner: {{ auth()->check() && auth()->id() === $place->user_id ? 'true' : 'false' }},
            currentUserId: {{ auth()->check() ? auth()->id() : 'null' }}
        };
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/css/place.css', 'resources/js/place.js'])
</head>
<body>

<header class="hdr scrolled">
    <div class="wrap hdr-in">
        <a class="logo" href="/">КОД<em>НОВОРОССИИ</em></a>
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
                    <img src="{{ safe_img($ph->path) }}" alt="" class="{{ $ph->is_cover ? 'on' : '' }}" onclick="setMain(this)">
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

            <div class="pl-actions" style="flex-wrap:wrap;gap:8px; margin-top: 16px;">
                <a class="btn btn-grad" href="tel:{{ preg_replace('/[^+\d]/', '', $place->phone) }}">📞 Позвонить</a>

                @if($place->external_id && $place->external_source === 'mypwa.ru')
                    <button type="button" class="btn btn-ghost" id="openMenuBtn" data-place-id="{{ $place->id }}">
                        <span>📋</span> Меню
                    </button>
                @endif

                <button type="button" class="btn btn-ghost" id="favBtn" data-place-id="{{ $place->id }}">
                    <span id="favIcon">🤍</span> <span id="favText">В избранное</span>
                </button>

                <button type="button" class="btn btn-ghost" id="shareBtn">
                    <span>🔗</span> Поделиться
                </button>

                <button type="button" class="btn btn-ghost" id="openRouteBtn">
                    <span>🗺</span> Маршрут
                </button>

                <button type="button" class="btn btn-ghost" id="openPlaceReportBtn" style="color:var(--rose); border-color:rgba(255,92,122,.3)">
                    <span>⚠️</span> Сообщить о неточности
                </button>
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

    {{-- Отзывы --}}
    <section class="pl-reviews reveal" id="reviews">
        <div class="reviews-header">
            <h2>Отзывы ({{ $place->reviews_count }})</h2>
            @auth
                @if($place->user_id !== auth()->id() && !$place->reviews()->where('user_id', auth()->id())->exists())
                    <button class="btn btn-grad" id="openReviewForm">✍️ Оставить отзыв</button>
                @endif
            @else
                {{-- ИЗМЕНЕНО: Ссылка на авторизацию с возвратом на текущую страницу --}}
                <a href="/login?redirect={{ urlencode(request()->fullUrl()) }}" class="btn btn-ghost">
                    🔑 Войти, чтобы оставить отзыв
                </a>
            @endauth
        </div>

        <div id="reviewsList" class="reviews-list">
            <div class="loading">Загрузка отзывов...</div>
        </div>
        <div id="reviewsPagination" class="reviews-pagination"></div>
    </section>

    {{-- Модалка формы отзыва --}}
    <div class="menu-modal" id="reviewModal">
        <div class="menu-modal__backdrop" data-close></div>
        <div class="menu-modal__panel">
            <div class="menu-modal__head">
                <div>
                    <div class="menu-modal__kicker">Ваше мнение важно</div>
                    <h3 class="menu-modal__title">Оставить отзыв</h3>
                </div>
                <button class="menu-modal__close" data-close>✕</button>
            </div>
            <form id="reviewForm" class="menu-modal__body" style="padding:24px">
                @csrf
                <div class="fb-field" style="margin-bottom:20px">
                    <label class="fb-label">Ваша оценка *</label>
                    <div class="star-rating" id="starRating">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="star" data-value="{{ $i }}">★</span>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" required>
                </div>
                <div class="fb-field" style="margin-bottom:20px">
                    <label class="fb-label">Ваш отзыв *</label>
                    <textarea name="content" class="fb-input" rows="5" required minlength="10" maxlength="2000" placeholder="Поделитесь своим опытом..."></textarea>
                </div>
                <div class="fb-field" style="margin-bottom:20px">
                    <label class="fb-label">Фото (до 5 штук)</label>
                    <input type="file" name="photos[]" class="fb-input" multiple accept="image/*" style="padding:10px">
                </div>
                <div class="fb-actions" style="justify-content:flex-end;gap:12px">
                    <button type="button" class="btn btn-ghost" data-close>Отмена</button>
                    <button type="submit" class="btn btn-grad">Отправить на модерацию</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Модалка ответа владельца --}}
    <div class="menu-modal" id="replyModal">
        <div class="menu-modal__backdrop" data-close></div>
        <div class="menu-modal__panel" style="max-width:600px">
            <div class="menu-modal__head">
                <div>
                    <div class="menu-modal__kicker">Ответ на отзыв</div>
                    <h3 class="menu-modal__title">Ответить клиенту</h3>
                </div>
                <button class="menu-modal__close" data-close>✕</button>
            </div>
            <form id="replyForm" class="menu-modal__body" style="padding:24px">
                @csrf
                <input type="hidden" name="review_id" id="replyReviewId">
                <div class="fb-field" style="margin-bottom:20px">
                    <label class="fb-label">Ваш ответ *</label>
                    <textarea name="content" class="fb-input" rows="5" required minlength="10" maxlength="1000"></textarea>
                </div>
                <div class="fb-actions" style="justify-content:flex-end;gap:12px">
                    <button type="button" class="btn btn-ghost" data-close>Отмена</button>
                    <button type="submit" class="btn btn-grad">Опубликовать ответ</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Модалка жалобы НА ОТЗЫВ --}}
    <div class="menu-modal" id="reviewReportModal">
        <div class="menu-modal__backdrop" data-close></div>
        <div class="menu-modal__panel" style="max-width:500px">
            <div class="menu-modal__head">
                <div>
                    <div class="menu-modal__kicker">Помогите нам стать лучше</div>
                    <h3 class="menu-modal__title">Пожаловаться на отзыв</h3>
                </div>
                <button class="menu-modal__close" data-close>✕</button>
            </div>
            <form id="reviewReportForm" class="menu-modal__body" style="padding:24px">
                @csrf
                <input type="hidden" name="review_id" id="reviewReportReviewId">
                <div class="fb-field" style="margin-bottom:20px">
                    <label class="fb-label">Причина жалобы *</label>
                    <select name="reason" class="fb-input" required>
                        <option value="">Выберите причину</option>
                        <option value="spam">Спам или реклама</option>
                        <option value="offensive">Оскорбления</option>
                        <option value="fake">Недостоверная информация</option>
                        <option value="other">Другое</option>
                    </select>
                </div>
                <div class="fb-field" style="margin-bottom:20px">
                    <label class="fb-label">Описание</label>
                    <textarea name="description" class="fb-input" rows="3" maxlength="500"></textarea>
                </div>
                <div class="fb-actions" style="justify-content:flex-end;gap:12px">
                    <button type="button" class="btn btn-ghost" data-close>Отмена</button>
                    <button type="submit" class="btn btn-grad">Отправить жалобу</button>
                </div>
            </form>
        </div>
    </div>

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

<!-- Модалка меню -->
<div class="menu-modal" id="menuModal">
    <div class="menu-modal__backdrop" data-close-menu></div>
    <div class="menu-modal__panel">
        <div class="menu-modal__head">
            <div>
                <div class="menu-modal__kicker">Прайс-лист</div>
                <h3 class="menu-modal__title" id="menuTitle">{{ $place->name }}</h3>
                <div class="menu-modal__meta" id="menuMeta"></div>
            </div>
            <button class="menu-modal__close" data-close-menu>✕</button>
        </div>
        <div class="menu-modal__search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="menuSearch" placeholder="Поиск по меню…">
            <button type="button" class="menu-modal__clear" id="menuSearchClear" hidden>✕</button>
        </div>
        <div class="menu-modal__body" id="menuBody"></div>
    </div>
</div>

<!-- Модалка: Построить маршрут -->
<div class="menu-modal" id="routeModal">
    <div class="menu-modal__backdrop" data-close-route></div>
    <div class="menu-modal__panel" style="max-width:400px">
        <div class="menu-modal__head">
            <h3 class="menu-modal__title">Построить маршрут</h3>
            <button class="menu-modal__close" data-close-route>✕</button>
        </div>
        <div style="padding:0 24px 24px; display:flex; flex-direction:column; gap:12px">
            <a href="#" id="routeYandex" target="_blank" class="route-btn route-btn--yandex"><span>🟡</span> Яндекс Карты</a>
            <a href="#" id="route2gis" target="_blank" class="route-btn route-btn--2gis"><span>🟢</span> 2ГИС</a>
            <a href="#" id="routeGoogle" target="_blank" class="route-btn route-btn--google"><span>🔵</span> Google Maps</a>
        </div>
    </div>
</div>

<!-- Модалка: Сообщить о неточности ЗАВЕДЕНИЯ -->
<div class="menu-modal" id="placeReportModal">
    <div class="menu-modal__backdrop" data-close-place-report></div>
    <div class="menu-modal__panel">
        <div class="menu-modal__head">
            <div>
                <div class="menu-modal__kicker">Помогите стать лучше</div>
                <h3 class="menu-modal__title">Сообщить о неточности</h3>
            </div>
            <button class="menu-modal__close" data-close-place-report>✕</button>
        </div>
        <form id="placeReportForm" class="menu-modal__body" style="padding:24px">
            <input type="hidden" name="place_id" value="{{ $place->id }}">
            <div class="fb-field" style="margin-bottom:16px">
                <label class="fb-label">Что не так? *</label>
                <select name="issue_type" class="fb-input" required>
                    <option value="">Выберите проблему…</option>
                    <option value="closed">Заведение закрыто / переехало</option>
                    <option value="hours">Неверный график работы</option>
                    <option value="phone">Неверный телефон</option>
                    <option value="menu">Устаревшее меню / цены</option>
                    <option value="other">Другое</option>
                </select>
            </div>
            <div class="fb-field" style="margin-bottom:16px">
                <label class="fb-label">Описание</label>
                <textarea name="description" class="fb-input" rows="3" placeholder="Опишите проблему подробно…"></textarea>
            </div>
            <div class="fb-field" style="margin-bottom:16px">
                <label class="fb-label">📷 Скриншоты или фото</label>
                <input type="file" name="screenshots[]" class="fb-input" multiple accept="image/*" style="padding:8px">
            </div>
            <div class="fb-field" style="margin-bottom:20px">
                <label class="fb-label">🎤 Голосовое сообщение</label>
                <div style="display:flex; gap:10px; align-items:center">
                    <button type="button" id="recordBtn" class="btn btn-ghost" style="border-color:var(--rose); color:var(--rose); width:48px; height:48px; padding:0; display:grid; place-items:center">🎙</button>
                    <div id="recordStatus" style="font-size:13px; color:var(--mut)">Нажмите, чтобы записать</div>
                    <audio id="audioPreview" controls style="display:none; flex:1; height:36px"></audio>
                </div>
                <input type="file" name="audio_recording" id="audioInput" hidden>
            </div>
            <div class="fb-actions" style="justify-content:flex-end">
                <button type="button" class="btn btn-ghost" data-close-place-report>Отмена</button>
                <button type="submit" class="btn btn-grad" id="placeReportSubmit">Отправить</button>
            </div>
        </form>
    </div>
</div>

{{-- НОВАЯ МОДАЛКА: Выбор для неавторизованного пользователя при добавлении в избранное --}}
<div class="menu-modal" id="localFavModal">
    <div class="menu-modal__backdrop" data-close-local-fav></div>
    <div class="menu-modal__panel" style="max-width:480px">
        <div class="menu-modal__head">
            <div>
                <div class="menu-modal__kicker">Избранное</div>
                <h3 class="menu-modal__title">Сохранить место?</h3>
            </div>
            <button class="menu-modal__close" data-close-local-fav>✕</button>
        </div>
        <div class="menu-modal__body" style="padding:24px; text-align:center">
            <p style="color:var(--mut); margin-bottom:24px; line-height:1.6">
                Вы не авторизованы. Вы можете сохранить это место локально в текущем браузере или войти в аккаунт, чтобы синхронизировать избранное на всех устройствах.
            </p>
            <div style="display:flex; flex-direction:column; gap:12px">
                <a href="/login?redirect={{ urlencode(request()->fullUrl()) }}" class="btn btn-grad" style="width:100%">
                    🔑 Войти в аккаунт
                </a>
                <button id="saveLocalFavBtn" class="btn btn-ghost" style="width:100%">
                    💾 Сохранить только в этом браузере
                </button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
