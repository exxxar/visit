<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->title }} — афиша ВИЗИТ ДОНЕЦК</title>
    <meta name="description" content="{{ $event->description ?? $event->type->label() . ' ' . $event->starts_at->translatedFormat('d F') }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="event">
    <meta property="og:title" content="{{ $event->title }}">
    <meta property="og:description" content="{{ $event->type->label() }} · {{ $event->starts_at->translatedFormat('d F, H:i') }}">
    <meta property="og:image" content="{{ url($event->image) }}">
    <meta property="og:locale" content="ru_RU">

    @php
        $ld = array_filter([
            '@context' => 'https://schema.org',
            '@type'    => 'Event',
            'name'     => $event->title,
            'description' => $event->description,
            'image'    => $event->image ? url($event->image) : null,
            'startDate'=> $event->starts_at->toIso8601String(),
            'endDate'  => $event->ends_at?->toIso8601String(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => $event->place ? [
                '@type' => 'Place',
                'name'  => $event->place->name,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $event->place->address,
                    'addressLocality' => 'Донецк',
                ],
            ] : null,
            'offers' => $event->price ? [
                '@type' => 'Offer',
                'price' => preg_replace('/[^\d]/', '', $event->price),
                'priceCurrency' => 'RUB',
                'availability' => 'https://schema.org/InStock',
                'url' => url()->current(),
            ] : null,
            'organizer' => [
                '@type' => 'Organization',
                'name'  => 'ВИЗИТ ДОНЕЦК',
                'url'   => url('/'),
            ],
        ]);
    @endphp
    <script type="application/ld+json">@json($ld)</script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css'])
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

<main class="wrap ev-wrap">
    <nav class="crumbs">
        <a href="/">Главная</a> / <a href="/#events">Афиша</a> / <span>{{ $event->title }}</span>
    </nav>

    @if($event->image)
        <img class="ev-cover" src="{{ safe_img($event->image) }}" alt="{{ $event->title }}">
    @endif

    <div class="ev-main">
        <div class="ev-date-block">
            <b>{{ $event->starts_at->format('d') }}</b>
            <span>{{ strtoupper(mb_substr($event->starts_at->translatedFormat('F'), 0, 3, 'UTF-8')) }}</span>
        </div>

        <div class="ev-info">
            <span class="ev-type" style="--tc:{{ $event->type->color() ?? 'var(--cyan)' }}">
                {{ $event->type->icon() }} {{ $event->type->label() }}
            </span>
            <h1>{{ $event->title }}</h1>

            <p class="ev-when">
                🕒 {{ $event->starts_at->translatedFormat('d F, l') }}
                · {{ $event->starts_at->format('H:i') }}
                @if($event->ends_at) — {{ $event->ends_at->format('H:i') }} @endif
            </p>

            @if($event->price)
                <p class="ev-price">💰 {{ $event->price }}</p>
            @endif

            @if($event->description)
                <div class="ev-desc vcard">
                    <h3>О событии</h3>
                    <p>{{ $event->description }}</p>
                </div>
            @endif

            @if($event->place)
                <div class="ev-place vcard">
                    <div class="kicker">📍 Площадка</div>
                    <a href="/place/{{ $event->place->slug }}" class="ev-place-link">
                        <h3 style="margin:0">{{ $event->place->name }}</h3>
                        <p style="color:var(--mut);margin:4px 0 0">{{ $event->place->category?->name }} · {{ $event->place->district?->name }}</p>
                        <p style="color:var(--mut);margin:4px 0 0">{{ $event->place->address }}</p>
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if($related->count())
        <section style="margin:36px 0 60px">
            <h2>Скоро в городе</h2>
            <div class="rail" style="margin-top:16px">
                @foreach($related as $r)
                    <a class="p-card" href="/event/{{ $r->slug }}">
                        <div class="p-img">
                            <img src="{{ safe_img($r->image) }}" alt="{{ $r->title }}">
                            <span class="p-rate">{{ $r->type->icon() }}</span>
                        </div>
                        <div class="p-body">
                            <h3>{{ $r->title }}</h3>
                            <p class="p-meta">{{ $r->starts_at->translatedFormat('d F') }} · {{ $r->place?->name ?? 'Донецк' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</main>

</body>
</html>
