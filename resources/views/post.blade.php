<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $post->title }} — журнал ВИЗИТ ДОНЕЦК</title>
    <meta name="description" content="{{ $post->excerpt }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:description" content="{{ $post->excerpt }}">
    <meta property="og:image" content="{{ url($post->cover) }}">
    <meta property="article:author" content="{{ $post->author?->name }}">
    <meta property="article:published_time" content="{{ $post->published_at?->toIso8601String() }}">

    @php
        $ld = [
            '@context' => 'https://schema.org',
            '@type'    => 'Article',
            'headline' => $post->title,
            'description' => $post->excerpt,
            'image'    => url($post->cover),
            'datePublished' => $post->published_at?->toIso8601String(),
            'author'   => ['@type' => 'Person', 'name' => $post->author?->name],
            'publisher'=> ['@type' => 'Organization', 'name' => 'ВИЗИТ ДОНЕЦК'],
        ];
    @endphp
    <script type="application/ld+json">@json($ld)</script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
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

<main class="wrap post-wrap">
    <nav class="crumbs">
        <a href="/">Главная</a> / <a href="/#editorial">Журнал</a> / <span>{{ $post->title }}</span>
    </nav>

    <article class="post-hero">
        <span class="ed-tag">{{ $post->tag ?? 'Подборка' }}</span>
        <h1>{{ $post->title }}</h1>
        <p class="post-meta">
            {{ $post->author?->name }} ·
            {{ $post->published_at?->translatedFormat('d F Y') }}
        </p>
        @if($post->cover)
            <img class="post-cover" src="{{ safe_img($post->cover) }}" alt="{{ $post->title }}">
        @endif
    </article>

    <section class="vcard post-body">
        @if($post->excerpt)
            <p class="post-lead">{{ $post->excerpt }}</p>
        @endif

        @if($post->body)
            <div class="prose">{!! nl2br(e($post->body)) !!}</div>
        @endif
    </section>

    @if($post->places->count())
        <section style="margin:36px 0">
            <h2>Места из подборки</h2>
            <div class="rail" style="margin-top:16px">
                @foreach($post->places as $p)
                    <a class="p-card" href="/place/{{ $p->slug }}">
                        <div class="p-img">
                            <img src="{{ safe_img($p->cover_url) }}" alt="{{ $p->name }}">
                            <span class="p-rate">★ {{ number_format($p->rating, 1, '.', '') }}</span>
                        </div>
                        <div class="p-body">
                            <h3>{{ $p->name }}</h3>
                            <p class="p-meta">{{ $p->category?->name }} · {{ $p->district?->name }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($related->count())
        <section style="margin:36px 0 60px">
            <h2>Ещё в журнале</h2>
            <div class="rail" style="margin-top:16px">
                @foreach($related as $r)
                    <a class="p-card" href="/post/{{ $r->slug }}">
                        <div class="p-img"><img src="{{ safe_img($r->cover) }}" alt=""></div>
                        <div class="p-body">
                            <h3>{{ $r->title }}</h3>
                            <p class="p-meta">{{ $r->tag ?? 'Подборка' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</main>

</body>
</html>
