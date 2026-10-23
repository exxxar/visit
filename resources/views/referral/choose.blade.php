<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Присоединяйтесь к ВИЗИТ ДОНЕЦК</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
</head>
<body>

<header class="hdr scrolled">
    <div class="wrap hdr-in">
        <a class="logo" href="/">ВИЗИТ<em>ДОНЕЦК</em></a>
    </div>
</header>

<main class="ref-choose">
    <div class="wrap">
        <div class="ref-head reveal">
            <span class="ref-head__badge">Вас пригласили</span>
            <h1 class="ref-head__title">
                Добро пожаловать<br>
                <span class="grad">в партнёрскую программу</span>
            </h1>
            <p class="ref-head__sub">
                Вас пригласил пользователь <b>{{ $phoneFormatted }}</b>.
                @if($referrer)
                    Он уже участник системы — выберите, как хотите присоединиться.
                @else
                    Зарегистрируйтесь и станьте частью экосистемы — когда приглашающий создаст аккаунт, вы автоматически станете его рефералом.
                @endif
            </p>
        </div>

        <div class="ref-grid">
            {{-- Главная --}}
            <a href="/" class="ref-card ref-card--home reveal">
                <div class="ref-card__ico">🏠</div>
                <h3>Просто на сайт</h3>
                <p>Перейти на главную и посмотреть городской путеводитель</p>
                <span class="ref-card__arrow">→</span>
            </a>

            {{-- Клиент --}}
            <a href="{{ route('ref.client') }}" class="ref-card ref-card--client reveal">
                <div class="ref-card__ico">🏢</div>
                <div class="ref-card__tag">Клиент</div>
                <h3>Создать заведение</h3>
                <p>Добавьте свой бизнес в городской путеводитель и получайте клиентов</p>
                <span class="ref-card__arrow">→</span>
            </a>

            {{-- Тех.партнёр --}}
            <a href="{{ route('ref.tech') }}" class="ref-card ref-card--tech reveal">
                <div class="ref-card__ico">💻</div>
                <div class="ref-card__tag">Технический партнёр</div>
                <h3>Я — айтишник</h3>
                <p>Разработчик, дизайнер, контент-мейкер — помогайте развивать платформу</p>
                <span class="ref-card__arrow">→</span>
            </a>

            {{-- Рекламный партнёр --}}
            <a href="{{ route('ref.ad') }}" class="ref-card ref-card--ad reveal">
                <div class="ref-card__ico">📣</div>
                <div class="ref-card__tag">Рекламный партнёр</div>
                <h3>Я — блогер / SMM</h3>
                <p>Приводите клиентов и зарабатывайте на партнёрской программе</p>
                <span class="ref-card__arrow">→</span>
            </a>

            {{-- ПРОДВИНУТЬ ЗАВЕДЕНИЕ --}}
            <a href="{{ route('ref.promote') }}" class="ref-card ref-card--promote reveal">
                <div class="ref-card__ico">🚀</div>
                <div class="ref-card__tag">Продвижение</div>
                <h3>Продвинуть заведение</h3>
                <p>Закажите SMM, таргет, контент или продвижение под ключ</p>
                <span class="ref-card__arrow">→</span>
            </a>
        </div>

        {{-- ===== НОВЫЙ БЛОК: УСЛОВИЯ ДЛЯ РЕКЛАМНЫХ ПАРТНЁРОВ ===== --}}
        <section class="ref-benefits reveal">
            <div class="ref-benefits__head">
                <span class="ref-benefits__kicker">💰 Партнёрская программа</span>
                <h2 class="ref-benefits__title">
                    Как зарабатывают<br>
                    <span class="grad">рекламные партнёры</span>
                </h2>
                <p class="ref-benefits__sub">
                    Прозрачная система дохода: выполняете заказы — получаете процент.
                    Чем больше заказов — тем выше уровень и процент комиссии.
                </p>
            </div>

            {{-- Уровни партнёрства --}}
            <div class="ref-levels">
                <div class="ref-level">
                    <div class="ref-level__ico">🌱</div>
                    <div class="ref-level__name">Новичок</div>
                    <div class="ref-level__pct"><b>10%</b></div>
                    <div class="ref-level__desc">с каждого заказа</div>
                    <div class="ref-level__cond">0+ заказов</div>
                </div>
                <div class="ref-level">
                    <div class="ref-level__ico">⭐</div>
                    <div class="ref-level__name">Опытный</div>
                    <div class="ref-level__pct"><b>15%</b></div>
                    <div class="ref-level__desc">с каждого заказа</div>
                    <div class="ref-level__cond">5+ заказов</div>
                </div>
                <div class="ref-level">
                    <div class="ref-level__ico">💎</div>
                    <div class="ref-level__name">Эксперт</div>
                    <div class="ref-level__pct"><b>20%</b></div>
                    <div class="ref-level__desc">с каждого заказа</div>
                    <div class="ref-level__cond">20+ заказов</div>
                </div>
                <div class="ref-level ref-level--top">
                    <div class="ref-level__ico">👑</div>
                    <div class="ref-level__name">Топ-партнёр</div>
                    <div class="ref-level__pct"><b>25%</b></div>
                    <div class="ref-level__desc">с каждого заказа</div>
                    <div class="ref-level__cond">50+ заказов</div>
                </div>
            </div>

            {{-- MLM-сеть --}}
            <div class="ref-mlm">
                <div class="ref-mlm__head">
                    <h3>🌐 Сетевой доход — зарабатывайте с команды</h3>
                    <p>Приглашайте других рекламных партнёров по реферальной ссылке и получайте бонус с каждого их заказа</p>
                </div>
                <div class="ref-mlm__levels">
                    <div class="ref-mlm__item">
                        <div class="ref-mlm__lvl">1-й уровень</div>
                        <div class="ref-mlm__pct" style="color:var(--orange,#ff8a3c)"><b>5%</b></div>
                        <div class="ref-mlm__desc">от дохода партнёра, которого вы пригласили лично</div>
                    </div>
                    <div class="ref-mlm__item">
                        <div class="ref-mlm__lvl">2-й уровень</div>
                        <div class="ref-mlm__pct" style="color:var(--magenta,#f050e0)"><b>3%</b></div>
                        <div class="ref-mlm__desc">от дохода партнёра, которого пригласил ваш реферал</div>
                    </div>
                    <div class="ref-mlm__item">
                        <div class="ref-mlm__lvl">3-й уровень</div>
                        <div class="ref-mlm__pct" style="color:var(--violet,#8b5cf6)"><b>1%</b></div>
                        <div class="ref-mlm__desc">от дохода партнёра третьего поколения</div>
                    </div>
                </div>
            </div>

            {{-- Преимущества --}}
            <div class="ref-perks">
                <div class="ref-perk">
                    <div class="ref-perk__ico">💼</div>
                    <div>
                        <h4>Личный кошелёк</h4>
                        <p>Все начисления — на вашем балансе в личном кабинете</p>
                    </div>
                </div>
                <div class="ref-perk">
                    <div class="ref-perk__ico">📊</div>
                    <div>
                        <h4>Прозрачная история</h4>
                        <p>Каждая операция зафиксирована: начисления, выводы, бонусы</p>
                    </div>
                </div>
                <div class="ref-perk">
                    <div class="ref-perk__ico">💸</div>
                    <div>
                        <h4>Вывод средств</h4>
                        <p>Запрашивайте вывод на карту или телефон в любое время</p>
                    </div>
                </div>
                <div class="ref-perk">
                    <div class="ref-perk__ico">🎁</div>
                    <div>
                        <h4>Бонусы за рост</h4>
                        <p>При достижении нового уровня — разовый бонус на баланс</p>
                    </div>
                </div>
            </div>

            <div class="ref-benefits__cta">
                <a href="{{ route('ref.ad') }}" class="ref-benefits__btn">
                    Стать рекламным партнёром
                    <span>→</span>
                </a>
            </div>
        </section>


        {{-- ===== ВТОРИЧНАЯ КНОПКА: СПИСОК ПАРТНЁРОВ ===== --}}
        <div class="ref-partners-link reveal">
            <a href="{{ route('partners.index') }}" class="ref-partners-btn">
                <span class="ref-partners-btn__ico">🌐</span>
                <div class="ref-partners-btn__text">
                    <b>Наша партнёрская сеть</b>
                    <span>Посмотрите, кто уже работает с нами и имеет высокий рейтинг</span>
                </div>
                <span class="ref-partners-btn__arrow">→</span>
            </a>
        </div>

        <div class="ref-footer reveal">
            <p>
                <b>Как работает реферальная система?</b><br>
                Приглашайте партнёров по ссылке <code>визит-донецк.рф?ref=ВАШ_ТЕЛЕФОН</code> — получайте бонусы за каждого приведённого клиента, техпартнёра или рекламного партнёра.
            </p>
        </div>
    </div>
</main>

</body>
</html>
