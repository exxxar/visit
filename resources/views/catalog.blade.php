<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Все места Донецка — каталог заведений | ВИЗИТ ДОНЕЦК</title>
    <meta name="description" content="Полный каталог заведений Донецка: рестораны, кафе, бары, салоны красоты, спорт и отдых. Поиск по районам и категориям.">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="Каталог мест Донецка — ВИЗИТ ДОНЕЦК">
    <meta property="og:description" content="{{ $places->total() }} заведений в каталоге. Поиск по категориям и районам.">

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
            <a href="/#events">Афиша</a>
        </nav>
        <div class="hdr-actions">
            <a class="btn btn-ghost btn-sm" href="/">← На главную</a>
        </div>
    </div>
</header>

<main>
    {{-- Hero с поиском --}}
    <section class="cat-hero">
        <div class="wrap">
            <div class="cat-hero__kicker">Каталог · {{ $places->total() }} {{ \Illuminate\Support\Str::plural('заведение', $places->total()) }}</div>
            <h1 class="cat-hero__title">Все места Донецка</h1>
            <p class="cat-hero__sub">Рестораны, кофейни, салоны красоты, спорт и отдых — находи своё место в городе</p>

            <form method="GET" action="{{ route('catalog') }}" class="cat-search" id="catalogForm">
                {{-- сохраняем активные фильтры при поиске --}}
                @foreach($selectedCats as $c)<input type="hidden" name="categories[]" value="{{ $c }}">@endforeach
                @foreach($selectedDists as $d)<input type="hidden" name="districts[]" value="{{ $d }}">@endforeach

                <div class="cat-search__field">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Название, кухня или район…" autofocus>
                    @if($q)
                        <a href="{{ route('catalog', array_merge(request()->except('q'), ['categories' => $selectedCats, 'districts' => $selectedDists])) }}" class="cat-search__clear">✕</a>
                    @endif
                </div>
                <button type="submit" class="btn btn-grad">Найти</button>
            </form>
        </div>
    </section>

    {{-- Фильтры --}}
    <section class="cat-filters">
        <div class="wrap">
            <div class="cat-filters__grid">
                {{-- Категории --}}
                <div class="cat-filters__block">
                    <div class="cat-filters__head">
                        <h3>Категории</h3>
                        @if($selectedCats)
                            <a href="{{ route('catalog', array_merge(request()->except('categories'), ['districts' => $selectedDists, 'q' => $q])) }}" class="cat-filters__reset">Сбросить</a>
                        @endif
                    </div>
                    <div class="cat-chips">
                        @foreach($categories as $cat)
                            @php $active = in_array($cat->slug, $selectedCats, true); @endphp
                            <label class="cat-chip {{ $active ? 'on' : '' }}">
                                <input type="checkbox" name="categories[]" value="{{ $cat->slug }}"
                                       form="catalogForm" {{ $active ? 'checked' : '' }}
                                       onchange="this.form.submit()">
                                <span class="cat-chip__ico">{{ $cat->icon ?? '📍' }}</span>
                                <span class="cat-chip__name">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Районы --}}
                <div class="cat-filters__block">
                    <div class="cat-filters__head">
                        <h3>Районы</h3>
                        @if($selectedDists)
                            <a href="{{ route('catalog', array_merge(request()->except('districts'), ['categories' => $selectedCats, 'q' => $q])) }}" class="cat-filters__reset">Сбросить</a>
                        @endif
                    </div>
                    <div class="cat-chips">
                        @foreach($districts as $dist)
                            @php $active = in_array($dist->slug, $selectedDists, true); @endphp
                            <label class="cat-chip cat-chip--dist {{ $active ? 'on' : '' }}">
                                <input type="checkbox" name="districts[]" value="{{ $dist->slug }}"
                                       form="catalogForm" {{ $active ? 'checked' : '' }}
                                       onchange="this.form.submit()">
                                <span class="cat-chip__ico">📍</span>
                                <span class="cat-chip__name">{{ $dist->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Результаты --}}
    <section class="cat-results">
        <div class="wrap">
            @if($places->count())
                <div class="cat-results__head">
                    <span>Найдено: <b>{{ $places->total() }}</b></span>
                    @if($q || $selectedCats || $selectedDists)
                        <a href="{{ route('catalog') }}" class="btn btn-ghost btn-sm">Сбросить все фильтры</a>
                    @endif
                </div>

                <div class="cat-grid">
                    @foreach($places as $p)
                        @php
                            $yandexUrl = 'https://yandex.ru/maps/?text=' . urlencode($p->address . ', Донецк')
                                       . ($p->lat && $p->lng ? '&ll=' . $p->lng . ',' . $p->lat . '&z=17' : '');
                        @endphp
                        <article class="cat-card {{ $p->is_featured ? 'cat-card--featured' : '' }}">
                            <a class="cat-card__media" href="/place/{{ $p->slug }}">
                                <img src="/{{ $p->cover_url }}" alt="{{ $p->name }}" loading="lazy">
                                @if($p->is_featured)
                                    <span class="cat-card__badge">★ Спецразмещение</span>
                                @endif
                                <span class="cat-card__rate">★ {{ number_format($p->rating, 1, '.', '') }}</span>
                            </a>

                            <div class="cat-card__body">
                                <div class="cat-card__meta">
                                    <span>{{ $p->category?->icon }} {{ $p->category?->name }}</span>
                                    <span>·</span>
                                    <span>{{ $p->district?->name }}</span>
                                </div>

                                <h3 class="cat-card__title">
                                    <a href="/place/{{ $p->slug }}">{{ $p->name }}</a>
                                </h3>

                                @if($p->short_description)
                                    <p class="cat-card__desc">{{ \Illuminate\Support\Str::limit($p->short_description, 100) }}</p>
                                @endif

                                <div class="cat-card__addr">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span>{{ $p->address }}</span>
                                </div>

                                <div class="cat-card__actions">
                                    <a class="btn btn-ghost btn-sm" href="/place/{{ $p->slug }}">Подробнее</a>
                                    <a class="btn btn-ghost btn-sm cat-card__map" href="{{ $yandexUrl }}" target="_blank" rel="nofollow noopener">
                                        🗺 На карте
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="cat-pager">
                    {{ $places->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="cat-empty">
                    <div class="cat-empty__ico">🔍</div>
                    <h3>Ничего не найдено</h3>
                    <p>Попробуйте изменить фильтры или поисковый запрос</p>
                    <a href="{{ route('catalog') }}" class="btn btn-grad">Сбросить фильтры</a>
                </div>
            @endif
        </div>
    </section>
</main>

<script>
    // header на скролле
    addEventListener('scroll', () => {
        document.querySelector('.hdr')?.classList.toggle('scrolled', scrollY > 10);
    }, { passive: true });
</script>

</body>
</html>
