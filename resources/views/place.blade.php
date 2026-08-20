<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $place->name }} — {{ $place->category?->name }} в районе {{ $place->district?->name }} | ВИЗИТ ДОНЕЦК</title>
    <meta name="description" content="{{ $place->short_description }} Рейтинг {{ number_format($place->rating, 1, '.', '') }}, отзывов: {{ $place->reviews_count }}. Адрес: {{ $place->address }}.">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="place">
    <meta property="og:title" content="{{ $place->name }} — ВИЗИТ ДОНЕЦК">
    <meta property="og:description" content="{{ $place->short_description }}">
    <meta property="og:image" content="{{ url($place->cover_url) }}">
    <meta property="og:locale" content="ru_RU">

    {{-- SEO: LocalBusiness --}}
    @php
        $ld = array_filter([
            '@context'  => 'https://schema.org',
            '@type'     => 'LocalBusiness',
            'name'      => $place->name,
            'description' => $place->short_description,
            'image'     => url($place->cover_url),
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
            <img id="plMain" class="pl-main" src="/{{ $place->cover_url }}" alt="{{ $place->name }}">
            <div class="pl-thumbs">
                @foreach($place->photos as $ph)
                    <img src="/{{ $ph->path }}" alt="" class="{{ $ph->is_cover ? 'on' : '' }}"
                         onclick="setMain(this)">
                @endforeach
            </div>
        </div>

        {{-- инфо-карточка --}}
        <aside class="glass pl-info reveal">
            <div class="kicker">{{ $place->category?->icon }} {{ $place->category?->name }} · {{ $place->district?->name }}</div>
            <h1 style="font-size:clamp(24px,3vw,36px)">{{ $place->name }}</h1>
            <div class="pl-rate">
                <span class="stars">{{ str_repeat('★', $place->rating) }}</span>
                <b>{{ number_format($place->rating, 1, '.', '') }}</b>
                <span style="color:var(--mut)">{{ $place->reviews_count }} отзывов</span>
                <span class="p-price" style="margin-left:auto">
                    @for($i=1;$i<=3;$i++)@if($i <= $place->price_level)<b>$</b>@else $@endif @endfor
                </span>
            </div>

            <p class="pl-addr">📍 {{ $place->address }}</p>

            <div class="pl-actions">
                <a class="btn btn-grad" href="tel:{{ preg_replace('/[^+\d]/', '', $place->phone) }}">📞 Позвонить</a>
                <a class="btn btn-ghost" target="_blank" rel="nofollow"
                   href="https://yandex.ru/maps/?text={{ $place->lat }},{{ $place->lng }}"> Маршрут</a>
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
                        <div class="p-img"><img src="/{{ $p->cover_url }}" alt=""><span class="p-rate">★ {{ number_format($p->rating, 1, '.', '') }}</span></div>
                        <div class="p-body"><h3>{{ $p->name }}</h3><p class="p-meta">{{ $p->category?->name }} · {{ $p->district?->name }}</p></div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</main>

<div class="toast" id="toast"></div>
</body>
</html>
