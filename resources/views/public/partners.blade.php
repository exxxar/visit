<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Партнёры — КОД НОВОРОССИИ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css'])
</head>
<body>

<header class="hdr scrolled">
    <div class="wrap hdr-in">
        <a class="logo" href="/">КОД<em>НОВОРОССИИ</em></a>
        <a class="btn btn-ghost btn-sm" href="{{ route('ref.choose') }}">← К реферальной программе</a>
    </div>
</header>

<main class="partners-page">
    <div class="partners-header">
        <div class="kicker" style="justify-content:center">Наша команда</div>
        <h1>Проверенные <span class="grad">партнёры</span></h1>
        <p>Лучшие специалисты платформы, готовые помочь вашему бизнесу расти. Выбирайте по рейтингу и отзывам.</p>
    </div>

    @if($partners->count() > 0)
        <div class="partners-grid">
            @foreach($partners as $partner)
                @php
                    $levelName = config("partners.levels.{$partner->partner_level}.name") ?? 'Партнёр';
                    $typeLabel = ($partner->meta['partner_type'] ?? '') === 'tech' ? 'Разработка' : 'Продвижение';
                @endphp

                <a href="{{ route('partner.public', $partner->slug) }}" class="partner-card">
                    <div class="partner-card__avatar">
                        {{ mb_substr($partner->name, 0, 1) }}
                    </div>

                    <h3 class="partner-card__name">{{ $partner->name }}</h3>

                    <div class="partner-card__badges">
                        <span class="partner-card__badge">🏆 {{ $levelName }}</span>
                        <span class="partner-card__badge">{{ $typeLabel }}</span>
                        @if($partner->partner_rating > 0)
                            <span class="partner-card__badge partner-card__badge--rating">
                                ★ {{ number_format($partner->partner_rating, 1) }}
                            </span>
                        @endif
                    </div>

                    <div class="partner-card__stats">
                        <div class="partner-card__stat">
                            <b>{{ $partner->completed_orders_count }}</b>
                            <span>заказов</span>
                        </div>
                        <div class="partner-card__stat">
                            <b>{{ $partner->reviews_count }}</b>
                            <span>отзывов</span>
                        </div>
                    </div>

                    <div class="partner-card__link">
                        Подробнее <span>→</span>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Пагинация -->
        <div style="margin-top: 48px; display: flex; justify-content: center;">
            {{ $partners->links('pagination::simple-tailwind') }}
            <!-- Или используй свой кастомный пагинатор, если есть -->
        </div>
    @else
        <div class="partners-cta">
            <div class="partners-cta__content">
                <div class="partners-cta__ico">🚀</div>
                <h2>Вы специалист в своей области?</h2>
                <p>
                    Присоединяйтесь к нашей сети, получайте заказы от реальных клиентов
                    и растите свой доход вместе с экосистемой КОД НОВОРОССИИ.
                </p>
                <a href="{{ route('ref.ad') }}" class="btn btn-grad partners-cta__btn">
                    Стать рекламным партнёром
                    <span>→</span>
                </a>
            </div>
        </div>

    @endif
</main>

<footer style="border-top: 1px solid var(--line); padding: 40px 0; text-align: center; color: var(--mut); font-size: 13px;">
    <div class="wrap">
        © {{ date('Y') }} КОД НОВОРОССИИ. Все права защищены.
    </div>
</footer>

</body>
</html>
