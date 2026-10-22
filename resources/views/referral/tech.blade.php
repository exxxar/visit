<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Технический партнёр — КОД НОВОРОССИИ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css'])
</head>
<body>

<header class="hdr scrolled">
    <div class="wrap hdr-in">
        <a class="logo" href="/">КОД<em>НОВОРОССИИ</em></a>
        <a class="btn btn-ghost btn-sm" href="{{ route('ref.choose', ['phone' => $phone]) }}">← К выбору</a>
    </div>
</header>

<main class="ref-form-page">
    <div class="wrap">
        <div class="ref-form-head">
            <div class="ref-form-head__ico">💻</div>
            <div>
                <h1>Стать техническим партнёром</h1>
                <p>Вас пригласил <b style="color:var(--cyan)">{{ $phoneFormatted }}</b></p>
            </div>
        </div>

        @if($errors->any())
            <div class="ref-error">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('ref.tech.store') }}" class="ref-form">
            @csrf

            <section class="ref-section">
                <h3>👤 Контактные данные</h3>

                <div class="ref-row">
                    <div class="ref-field">
                        <label>Имя *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="ref-field">
                        <label>Телефон *</label>
                        <input type="tel" name="user_phone" value="{{ old('user_phone') }}" required>
                    </div>
                </div>

                <div class="ref-row">
                    <div class="ref-field">
                        <label>Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="ref-field">
                        <label>Пароль *</label>
                        <input type="password" name="password" required minlength="8">
                    </div>
                </div>
            </section>

            <section class="ref-section">
                <h3>💼 Профессиональная информация</h3>

                <div class="ref-field">
                    <label>Специализация *</label>
                    <input type="text" name="speciality" value="{{ old('speciality') }}" required
                           placeholder="Frontend / Backend / Mobile / Design / SMM / SEO / Контент">
                </div>

                <div class="ref-field">
                    <label>Ссылка на портфолио / сайт</label>
                    <input type="url" name="portfolio" value="{{ old('portfolio') }}" placeholder="https://…">
                </div>

                <div class="ref-field">
                    <label>Опыт и навыки</label>
                    <textarea name="experience" rows="5" placeholder="Расскажите о себе: стек, проекты, сильные стороны…">{{ old('experience') }}</textarea>
                </div>
            </section>

            <div class="ref-actions">
                <button type="submit" class="btn btn-grad btn-lg">Стать партнёром</button>
                <p class="ref-hint">После регистрации с вами свяжется команда проекта</p>
            </div>
        </form>
    </div>
</main>
<script>
    (function (){
        /* ===== АВТОСОХРАНЕНИЕ ===== */
        const techFormEl = document.querySelector('form[action="{{ route('ref.tech.store') }}"]');
        new FormAutoSave(techFormEl, 'ref_tech_form');

        /* очистка после успешной отправки */
        techFormEl.addEventListener('submit', () => {
            setTimeout(() => {
                localStorage.removeItem('ref_tech_form');
            }, 100);
        });
    })();
</script>
</body>
</html>
