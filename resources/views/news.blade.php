<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Новости Донецка — события, открытия, обновления | ВИЗИТ ДОНЕЦК</title>
    <meta name="description" content="Свежие новости Донецка: открытия заведений, события, акции и обновления городского путеводителя ВИЗИТ ДОНЕЦК.">
    <link rel="canonical" href="{{ request()->url() }}">
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
            <a href="/news" class="on">Новости</a>
        </nav>
        <div class="hdr-actions">
            <a class="btn btn-ghost btn-sm" href="/">← На главную</a>
        </div>
    </div>
</header>

<main>
    {{-- герой --}}
    <section class="nws-hero">
        <div class="wrap">
            <div class="nws-hero__kicker">Новости · {{ $news->total() }} публикаций</div>
            <h1 class="nws-hero__title">Что нового в городе</h1>
            <p class="nws-hero__sub">Открытия заведений, события, акции и обновления городского путеводителя</p>
        </div>
    </section>

    {{-- фильтр по заведению --}}
    @if($places->count())
        <section class="wrap" style="margin-bottom:24px">
            <div class="nws-filter">
                <a href="{{ route('news.index') }}" class="nws-filter__item {{ !request('place') ? 'on' : '' }}">Все</a>
                @foreach($places as $p)
                    <a href="{{ route('news.index', ['place' => $p->id]) }}"
                       class="nws-filter__item {{ request('place') == $p->id ? 'on' : '' }}">
                        {{ $p->name }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- список --}}
    <section class="wrap nws-feed">
        @forelse($news as $i => $n)
            {{-- первая новость на странице — широкая --}}
            @if($i === 0 && !$news->currentPage() > 1)
                <a class="nws-card nws-card--wide" href="{{ route('news.show', $n->id) }}">
                    <div class="nws-card__media">
                        @if($n->image)
                            <img src="{{ str_starts_with($n->image, 'http') ? $n->image : '/' . ltrim($n->image, '/') }}" alt="{{ $n->title }}" loading="lazy">
                        @else
                            <div class="nws-card__placeholder">📰</div>
                        @endif
                    </div>
                    <div class="nws-card__body">
                        <div class="nws-card__meta">
                            <span class="nws-card__date">{{ $n->published_at->format('d.m.Y') }}</span>
                            @if($n->place)<span class="nws-card__place">· {{ $n->place->name }}</span>@endif
                        </div>
                        <h2 class="nws-card__title">{{ $n->title }}</h2>
                        <p class="nws-card__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($n->body), 180) }}</p>
                        <span class="nws-card__more">Читать далее →</span>
                    </div>
                </a>
            @else
                <a class="nws-card" href="{{ route('news.show', $n->id) }}">
                    <div class="nws-card__media">
                        @if($n->image)
                            <img src="{{ str_starts_with($n->image, 'http') ? $n->image : '/' . ltrim($n->image, '/') }}" alt="{{ $n->title }}" loading="lazy">
                        @else
                            <div class="nws-card__placeholder">📰</div>
                        @endif
                    </div>
                    <div class="nws-card__body">
                        <div class="nws-card__meta">
                            <span class="nws-card__date">{{ $n->published_at->format('d.m.Y') }}</span>
                        </div>
                        <h3 class="nws-card__title">{{ $n->title }}</h3>
                        <p class="nws-card__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($n->body), 100) }}</p>
                    </div>
                </a>
            @endif
        @empty
            <div class="nws-empty">
                <div class="nws-empty__ico">📭</div>
                <h3>Пока нет новостей</h3>
                <p>Загляните позже — мы публикуем свежие новости каждую неделю</p>
                <a href="/" class="btn btn-grad">На главную</a>
            </div>
        @endforelse
    </section>

    {{-- пагинация --}}
    @if($news->hasPages())
        <div class="wrap" style="margin:32px 0 60px">
            <div class="nws-pager">
                {{ $news->links('pagination::default') }}
            </div>
        </div>
    @endif
</main>

</body>
</html>
