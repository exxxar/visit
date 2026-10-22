<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Рекламный партнёр — КОД НОВОРОССИИ</title>
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
            <div class="ref-form-head__ico">📣</div>
            <div>
                <h1>Стать рекламным партнёром</h1>
                <p>Вас пригласил <b style="color:var(--cyan)">{{ $phoneFormatted }}</b></p>
            </div>
        </div>

        @if($errors->any())
            <div class="ref-error">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('ref.ad.store') }}" class="ref-form">
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
                <h3>📱 Ваша площадка</h3>

                <div class="ref-row">
                    <div class="ref-field">
                        <label>Платформа *</label>
                        <select name="platform" required>
                            <option value="">Выберите…</option>
                            <option value="telegram" {{ old('platform') === 'telegram' ? 'selected' : '' }}>Telegram-канал</option>
                            <option value="vk" {{ old('platform') === 'vk' ? 'selected' : '' }}>ВКонтакте</option>
                            <option value="instagram" {{ old('platform') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                            <option value="youtube" {{ old('platform') === 'youtube' ? 'selected' : '' }}>YouTube</option>
                            <option value="tiktok" {{ old('platform') === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                            <option value="other" {{ old('platform') === 'other' ? 'selected' : '' }}>Другое</option>
                        </select>
                    </div>
                    <div class="ref-field">
                        <label>Размер аудитории</label>
                        <input type="text" name="audience" value="{{ old('audience') }}" placeholder="например, 15 000 подписчиков">
                    </div>
                </div>

                <div class="ref-field">
                    <label>Ссылка на площадку</label>
                    <input type="url" name="social_link" value="{{ old('social_link') }}" placeholder="https://…">
                </div>
            </section>

            <div class="ref-actions">
                <button type="submit" class="btn btn-grad btn-lg">Стать партнёром</button>
                <p class="ref-hint">Получите персональную реферальную ссылку и зарабатывайте на привлечении клиентов</p>
            </div>
        </form>
    </div>
</main>

<script>
    (function (){
        const adFormEl = document.querySelector('form[action="{{ route('ref.ad.store') }}"]');
        new FormAutoSave(adFormEl, 'ref_ad_form');

        adFormEl.addEventListener('submit', () => {
            setTimeout(() => {
                localStorage.removeItem('ref_ad_form');
            }, 100);
        });
    })();
</script>
</body>
</html>
