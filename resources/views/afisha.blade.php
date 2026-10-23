<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Афиша Донецка — события и мероприятия | ВИЗИТ ДОНЕЦК</title>
    <meta name="description" content="Афиша Донецка: концерты, выставки, спектакли, спорт и фестивали. {{ $total }} событий на ближайшие дни.">
    <link rel="canonical" href="{{ request()->url() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

            <div class="af-hero__actions">
                <button class="af-add-btn" id="openEventForm">
                    <span class="af-add-btn__ico">+</span>
                    <span>Добавить событие</span>
                </button>
            </div>

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
                                        <img src="{{ safe_img($e->image) }}" alt="{{ $e->title }}" loading="lazy">
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

<!-- Модалка добавления события -->
<div class="event-modal" id="eventModal">
    <div class="event-modal__backdrop" data-close-event></div>
    <div class="event-modal__panel">
        <div class="event-modal__head">
            <div>
                <div class="event-modal__kicker">Новое событие</div>
                <h3 class="event-modal__title">Добавить в афишу</h3>
            </div>
            <button class="event-modal__close" data-close-event aria-label="Закрыть">✕</button>
        </div>

        <form id="eventForm" class="event-modal__body">
            <div class="event-field">
                <label class="event-label">Название события *</label>
                <input type="text" name="title" class="event-input" placeholder="Концерт группы «Название»" required>
            </div>

            <div class="event-field">
                <label class="event-label">Тип события *</label>
                <select name="type" class="event-input" required>
                    <option value="">Выберите тип…</option>
                    @foreach($types as $t)
                        <option value="{{ $t->value }}">{{ $t->icon() }} {{ $t->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="event-row">
                <div class="event-field">
                    <label class="event-label">Дата *</label>
                    <input type="date" name="date" class="event-input" required min="{{ date('Y-m-d') }}">
                </div>
                <div class="event-field">
                    <label class="event-label">Время *</label>
                    <input type="time" name="time" class="event-input" required>
                </div>
            </div>

            <div class="event-field">
                <label class="event-label">Место проведения</label>
                <select name="place_id" class="event-input">
                    <option value="">Не указано</option>
                    @foreach(\App\Models\Place::approved()->orderBy('name')->get(['id', 'name']) as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <p class="event-hint">Если места нет в списке — оставьте пустым и укажите в описании</p>
            </div>

            <div class="event-field">
                <label class="event-label">Описание</label>
                <textarea name="description" class="event-input" rows="4" placeholder="Расскажите о событии, программе, участниках…"></textarea>
            </div>

            <div class="event-field">
                <label class="event-label">Изображение</label>
                <input type="file" name="image" class="event-input" accept="image/*">
                <p class="event-hint">JPG, PNG, WEBP до 5 МБ. Рекомендуемый размер 1200×800</p>
            </div>

            <div class="event-field">
                <label class="event-label">Ваш контакт для связи</label>
                <input type="text" name="contact" class="event-input" placeholder="Телефон или email">
                <p class="event-hint">Для уточнения деталей (не публикуется)</p>
            </div>

            <div class="event-actions">
                <button type="button" class="btn btn-ghost" data-close-event>Отмена</button>
                <button type="submit" class="btn btn-grad" id="eventSubmit">Отправить на модерацию</button>
            </div>
        </form>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
    (function() {
        const modal = document.getElementById('eventModal');
        const form = document.getElementById('eventForm');
        const submitBtn = document.getElementById('eventSubmit');
        const toast = document.getElementById('toast');

        // Открытие
        document.getElementById('openEventForm').addEventListener('click', () => {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        });

        // Закрытие
        document.querySelectorAll('[data-close-event]').forEach(el => {
            el.addEventListener('click', closeModal);
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
        });

        function closeModal() {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }

        // Отправка формы
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправляем…';

            try {
                const response = await fetch('/api/v1/events', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    const errors = Object.values(data.errors || {}).flat().join(', ');
                    throw new Error(errors || 'Ошибка отправки');
                }

                showToast('✓ Событие отправлено на модерацию. Мы опубликуем его после проверки.', 'success');
                form.reset();
                setTimeout(closeModal, 1500);

            } catch (err) {
                showToast('✕ ' + err.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Отправить на модерацию';
            }
        });

        function showToast(message, type = 'success') {
            toast.textContent = message;
            toast.className = 'toast toast--' + type + ' show';
            setTimeout(() => toast.classList.remove('show'), 4000);
        }
    })();
</script>

</body>
</html>
