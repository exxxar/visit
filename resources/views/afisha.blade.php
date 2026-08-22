<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Афиша Донецка — события и мероприятия | ВИЗИТ ДОНЕЦК</title>
    <meta name="description" content="Афиша Донецка: концерты, выставки, спектакли, спорт и фестивали. {{ $total }} событий на ближайшие дни.">
    <link rel="canonical" href="{{ url()->current() }}">
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
        </nav>
        <div class="hdr-actions">
            <a class="btn btn-ghost btn-sm" href="/">← На главную</a>
        </div>
    </div>
</header>

<main>
    <section class="af-hero">
        <div class="wrap">
            <div class="af-hero__kicker">Афиша · {{ $total }} {{ \Illuminate\Support\Str::plural('событие', $total) }}</div>
            <h1 class="af-hero__title">Что происходит в городе</h1>
            <p class="af-hero__sub">Концерты, выставки, спектакли, фестивали и спорт — всё, что нельзя пропустить</p>

            {{-- фильтр по типу --}}
            <div class="af-types">
                <a href="{{ route('afisha') }}" class="af-type {{ !$active ? 'on' : '' }}">Все</a>
                @foreach($types as $t)
                    <a href="{{ route('afisha', ['type' => $t->value]) }}"
                       class="af-type {{ $active === $t->value ? 'on' : '' }}">
                        {{ $t->icon() }} {{ $t->label() }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="af-feed">
        <div class="wrap">
            @forelse($days as $day)
                <div class="af-day">
                    <div class="af-day__head">
                        <div class="af-day__date">
                            <b>{{ $day['day'] }}</b>
                            <span>{{ $day['month'] }}</span>
                        </div>
                        <h2 class="af-day__label">{{ $day['label'] }}</h2>
                        <span class="af-day__count">{{ $day['events']->count() }} {{ \Illuminate\Support\Str::plural('событие', $day['events']->count()) }}</span>
                    </div>

                    <div class="af-grid">
                        @foreach($day['events'] as $e)
                            <a class="af-card" href="/event/{{ $e->slug }}">
                                @if($e->image)
                                    <div class="af-card__media">
                                        <img src="{{ $e->image }}" alt="{{ $e->title }}" loading="lazy">
                                        <span class="af-card__type">{{ $e->type->icon() }}</span>
                                    </div>
                                @endif
                                <div class="af-card__body">
                                    <div class="af-card__time">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        {{ $e->starts_at->format('H:i') }}
                                    </div>
                                    <h3 class="af-card__title">{{ $e->title }}</h3>
                                    <p class="af-card__meta">
                                        {{ $e->type->label() }} · {{ $e->place?->name ?? 'Донецк' }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="af-empty">
                    <div class="af-empty__ico">🎭</div>
                    <h3>Пока нет событий</h3>
                    <p>Загляните позже — афиша обновляется каждый день</p>
                    <a href="{{ route('afisha') }}" class="btn btn-grad">Сбросить фильтры</a>
                </div>
            @endforelse
        </div>
    </section>
</main>

</body>
</html>
