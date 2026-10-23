<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $news->title }} | ВИЗИТ ДОНЕЦК</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($news->body), 160) }}">
    <link rel="canonical" href="{{ request()->url() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $news->title }} — ВИЗИТ ДОНЕЦК">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($news->body), 160) }}">
    @if($news->image)
        <meta property="og:image" content="{{ str_starts_with($news->image, 'http') ? $news->image : url('/' . ltrim($news->image, '/')) }}">
    @endif
    <meta property="og:locale" content="ru_RU">

    {{-- SEO: NewsArticle --}}
    <script type="application/ld+json">
        @json([
            '@context'      => 'https://schema.org',
            '@type'         => 'NewsArticle',
            'headline'      => $news->title,
            'datePublished' => $news->published_at?->toIso8601String(),
            'image'         => $news->image ? url('/' . ltrim($news->image, '/')) : null,
            'author'        => ['@type' => 'Organization', 'name' => 'ВИЗИТ ДОНЕЦК'],
        ])
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css'])
</head>
<body>

<header class="hdr scrolled">
    <div class="wrap hdr-in">
        <a class="logo" href="/">ВИЗИТ<em>ДОНЕЦК</em></a>
        <nav class="nav-links">
            <a href="/#places">Места</a>
            <a href="/#map">Карта</a>
            <a href="/afisha">Афиша</a>
            <a href="/news">Новости</a>
        </nav>
        <div class="hdr-actions">
            <a class="btn btn-ghost btn-sm" href="javascript:history.back()">← Назад</a>
        </div>
    </div>
</header>

<main class="wrap nws-article">
    <nav class="nws-crumbs">
        <a href="/">Главная</a> / <a href="/news">Новости</a> / <span>{{ \Illuminate\Support\Str::limit($news->title, 40) }}</span>
    </nav>

    <article>
        <div class="nws-article__meta">
            <span class="nws-article__date">📅 {{ $news->published_at->format('d.m.Y') }}</span>
            @if($news->place)
                <a href="/place/{{ $news->place->slug }}" class="nws-article__place">
                    📍 {{ $news->place->name }}
                </a>
            @endif
        </div>

        <h1 class="nws-article__title">{{ $news->title }}</h1>

        @if($news->image)
            <div class="nws-article__cover">
                <img src="{{ str_starts_with($news->image, 'http') ? $news->image : '/' . ltrim($news->image, '/') }}" alt="{{ $news->title }}">
            </div>
        @endif

        <div class="nws-article__body">
            {!! nl2br(e($news->body)) !!}
        </div>
    </article>

    {{-- похожие новости --}}
    @if($related->count())
        <section class="nws-related">
            <h2>Читайте также</h2>
            <div class="nws-related__grid">
                @foreach($related as $r)
                    <a class="nws-card" href="{{ route('news.show', $r->id) }}">
                        <div class="nws-card__media">
                            @if($r->image)
                                <img src="{{ str_starts_with($r->image, 'http') ? $r->image : '/' . ltrim($r->image, '/') }}" alt="{{ $r->title }}" loading="lazy">
                            @else
                                <div class="nws-card__placeholder">📰</div>
                            @endif
                        </div>
                        <div class="nws-card__body">
                            <span class="nws-card__date">{{ $r->published_at->format('d.m.Y') }}</span>
                            <h3 class="nws-card__title">{{ $r->title }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</main>

</body>
</html>
