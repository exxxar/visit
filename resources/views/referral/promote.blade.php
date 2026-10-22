<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Продвинуть заведение — КОД НОВОРОССИИ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])


    <style>
        /* Тарифы */
        .promo-tariffs {
            display: grid;
            gap: 12px;
        }

        .promo-tariff {
            position: relative;
            display: flex;
            gap: 16px;
            padding: 18px 20px;
            background: var(--panel);
            border: 2px solid var(--line);
            border-radius: 16px;
            cursor: pointer;
            transition: 0.25s;
        }

        .promo-tariff:hover {
            border-color: rgba(255, 138, 60, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(255, 138, 60, 0.12);
        }

        .promo-tariff input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .promo-tariff--selected {
            border-color: var(--orange, #ff8a3c);
            background: linear-gradient(135deg, rgba(255, 138, 60, 0.08), rgba(240, 80, 224, 0.08));
            box-shadow: 0 0 0 3px rgba(255, 138, 60, 0.15);
        }

        .promo-tariff__icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 26px;
            background: linear-gradient(135deg, rgba(255, 138, 60, 0.15), rgba(240, 80, 224, 0.15));
            border: 1px solid rgba(255, 138, 60, 0.25);
            flex-shrink: 0;
        }

        .promo-tariff__body {
            flex: 1;
            min-width: 0;
        }

        .promo-tariff__body h3 {
            font-family: var(--disp);
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 4px;
            color: var(--txt);
        }

        .promo-tariff__body p {
            color: var(--mut);
            font-size: 13px;
            line-height: 1.4;
            margin: 0 0 8px;
        }

        .promo-tariff__price {
            font-family: var(--disp);
            font-size: 20px;
            font-weight: 800;
            color: var(--orange, #ff8a3c);
        }

        .promo-tariff__check {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--orange, #ff8a3c);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 16px;
            font-weight: 900;
            opacity: 0;
            transform: scale(0.8);
            transition: 0.25s;
        }

        .promo-tariff--selected .promo-tariff__check {
            opacity: 1;
            transform: scale(1);
            box-shadow: 0 0 14px -3px rgba(255, 138, 60, 0.7);
        }

        /* ===== СВОДКА ===== */
        .promo-summary__list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 18px;
        }

        .promo-summary__item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: rgba(255, 138, 60, 0.06);
            border: 1px solid rgba(255, 138, 60, 0.2);
            border-radius: 12px;
        }

        .promo-summary__icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .promo-summary__title {
            flex: 1;
            font-size: 14px;
            font-weight: 600;
            color: var(--txt);
        }

        .promo-summary__price {
            font-family: var(--disp);
            font-size: 16px;
            font-weight: 800;
            color: var(--orange, #ff8a3c);
            white-space: nowrap;
        }

        .promo-summary__total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: linear-gradient(135deg, rgba(255, 138, 60, 0.12), rgba(240, 80, 224, 0.12));
            border: 1px solid rgba(255, 138, 60, 0.3);
            border-radius: 14px;
        }

        .promo-summary__total span {
            font-size: 14px;
            color: var(--mut);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .promo-summary__total b {
            font-family: var(--disp);
            font-size: 24px;
            font-weight: 800;
            color: var(--orange, #ff8a3c);
        }

        /* ===== ТАРИФЫ ===== */
        .promo-tariffs {
            display: grid;
            gap: 12px;
        }

        .promo-tariff {
            position: relative;
            display: flex;
            gap: 16px;
            padding: 18px 20px;
            background: var(--panel);
            border: 2px solid var(--line);
            border-radius: 16px;
            cursor: pointer;
            user-select: none;
            transition: all 0.25s ease;
            outline: none;
        }

        .promo-tariff:hover {
            border-color: rgba(255, 138, 60, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(255, 138, 60, 0.12);
        }

        .promo-tariff:focus-visible {
            border-color: var(--cyan);
            box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.2);
        }

        /* Полностью скрываем нативный чекбокс, но оставляем его в DOM для отправки формы */
        .promo-tariff input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }

        .promo-tariff--selected {
            border-color: var(--orange, #ff8a3c);
            background: linear-gradient(135deg, rgba(255, 138, 60, 0.08), rgba(240, 80, 224, 0.06));
            box-shadow: 0 0 0 1px rgba(255, 138, 60, 0.3), 0 8px 24px rgba(255, 138, 60, 0.15);
        }

        .promo-tariff__icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 26px;
            background: linear-gradient(135deg, rgba(255, 138, 60, 0.15), rgba(240, 80, 224, 0.15));
            border: 1px solid rgba(255, 138, 60, 0.25);
            flex-shrink: 0;
            transition: transform 0.25s ease;
        }

        .promo-tariff--selected .promo-tariff__icon {
            transform: scale(1.05);
        }

        .promo-tariff__body {
            flex: 1;
            min-width: 0;
        }

        .promo-tariff__body h3 {
            font-family: var(--disp);
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 4px;
            color: var(--txt);
        }

        .promo-tariff__body p {
            color: var(--mut);
            font-size: 13px;
            line-height: 1.4;
            margin: 0 0 8px;
        }

        .promo-tariff__price {
            font-family: var(--disp);
            font-size: 20px;
            font-weight: 800;
            color: var(--orange, #ff8a3c);
        }

        /* Кастомная галочка */
        .promo-tariff__check {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--orange, #ff8a3c);
            color: #fff;
            display: grid;
            place-items: center;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); /* Пружинистая анимация */
            box-shadow: 0 4px 12px rgba(255, 138, 60, 0.4);
        }

        .promo-tariff--selected .promo-tariff__check {
            opacity: 1;
            transform: scale(1);
        }

        /* ===== СВОДКА ===== */
        .promo-summary {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .promo-summary__list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 18px;
        }

        .promo-summary__item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: rgba(255, 138, 60, 0.06);
            border: 1px solid rgba(255, 138, 60, 0.2);
            border-radius: 12px;
            animation: slideIn 0.25s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .promo-summary__icon { font-size: 24px; flex-shrink: 0; }
        .promo-summary__title { flex: 1; font-size: 14px; font-weight: 600; color: var(--txt); }
        .promo-summary__price {
            font-family: var(--disp);
            font-size: 16px;
            font-weight: 800;
            color: var(--orange, #ff8a3c);
            white-space: nowrap;
        }

        .promo-summary__total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: linear-gradient(135deg, rgba(255, 138, 60, 0.12), rgba(240, 80, 224, 0.12));
            border: 1px solid rgba(255, 138, 60, 0.3);
            border-radius: 14px;
        }

        .promo-summary__total span {
            font-size: 14px;
            color: var(--mut);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .promo-summary__total b {
            font-family: var(--disp);
            font-size: 24px;
            font-weight: 800;
            color: var(--orange, #ff8a3c);
        }
    </style>
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
            <div class="ref-form-head__ico">🚀</div>
            <div>
                <h1>Продвинуть заведение</h1>
                <p>Вас пригласил <b style="color:var(--cyan)">{{ $phoneFormatted }}</b></p>
            </div>
        </div>

        @if($errors->any())
            <div class="ref-error">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('ref.promote.store') }}" class="ref-form">
            @csrf

            {{-- Выбор тарифов (множественный) --}}
            <section class="ref-section">
                <h3>📦 Выберите услуги (можно несколько)</h3>
                <p class="ref-hint" style="margin-bottom:18px">
                    Отметьте галочками все интересующие вас услуги — мы подберём оптимальное решение
                </p>

                <div class="promo-tariffs">
                    @foreach($services as $id => $svc)
                        <div class="promo-tariff" role="button" tabindex="0" data-svc-id="{{ $id }}" data-price="{{ $svc['price'] }}">
                            <input type="checkbox" name="services[]" value="{{ $id }}">
                            <div class="promo-tariff__icon">{{ $svc['icon'] }}</div>
                            <div class="promo-tariff__body">
                                <h3>{{ $svc['label'] }}</h3>
                                <p>{{ $svc['desc'] ?? '' }}</p>
                                <div class="promo-tariff__price">{{ number_format($svc['price'], 0, '.', ' ') }} ₽</div>
                            </div>
                            <div class="promo-tariff__check">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Сводка выбранных услуг --}}
            <section class="ref-section promo-summary" id="promoSummary" hidden>
                <h3>📋 Ваша заявка</h3>
                <div class="promo-summary__list" id="summaryList"></div>
                <div class="promo-summary__total">
                    <span>Итого:</span>
                    <b id="summaryTotal">0 ₽</b>
                </div>
            </section>
            {{-- Данные заведения --}}
            <section class="ref-section">
                <h3>🏢 О заведении</h3>

                <div class="ref-field">
                    <label>Название заведения *</label>
                    <input type="text" name="place_name" class="inp" value="{{ old('place_name') }}" required placeholder="Например: GASTRO BAR">
                </div>

                <div class="ref-row">
                    <div class="ref-field">
                        <label>Адрес</label>
                        <input type="text" name="place_address" class="inp" value="{{ old('place_address') }}" placeholder="ул. Артёма, 100">
                    </div>
                    <div class="ref-field">
                        <label>Телефон заведения</label>
                        <input type="tel" name="place_phone" class="inp" value="{{ old('place_phone') }}" placeholder="+7 ___ ___-__-__">
                    </div>
                </div>

                <div class="ref-field">
                    <label>Сайт / соцсети</label>
                    <input type="text" name="place_site" class="inp" value="{{ old('place_site') }}" placeholder="https://… или @instagram">
                </div>
            </section>

            {{-- Контактное лицо --}}
            <section class="ref-section">
                <h3>👤 Контактное лицо</h3>

                <div class="ref-row">
                    <div class="ref-field">
                        <label>Имя *</label>
                        <input type="text" name="contact_name" class="inp" value="{{ old('contact_name') }}" required placeholder="Как к вам обращаться">
                    </div>
                    <div class="ref-field">
                        <label>Телефон *</label>
                        <input type="tel" name="contact_phone" class="inp" value="{{ old('contact_phone') }}" required placeholder="+7 ___ ___-__-__">
                    </div>
                </div>

                <div class="ref-field">
                    <label>Email</label>
                    <input type="email" name="contact_email" class="inp" value="{{ old('contact_email') }}" placeholder="you@company.ru">
                </div>
            </section>

            {{-- Дополнительные пожелания --}}
            <section class="ref-section">
                <h3>💬 Дополнительные пожелания</h3>
                <div class="ref-field">
                    <textarea name="requirements" class="inp" rows="4" placeholder="Расскажите, что хотите получить от продвижения…">{{ old('requirements') }}</textarea>
                </div>
            </section>

            {{-- Регистрация (опционально) --}}
            <section class="ref-section">
                <h3>🔐 Создать аккаунт (необязательно)</h3>
                <p class="ref-hint" style="margin-bottom:14px">
                    Создайте аккаунт, чтобы отслеживать статус заявки в личном кабинете
                </p>

                <div class="ref-row">
                    <div class="ref-field">
                        <label>Email для входа</label>
                        <input type="email" name="email" class="inp" value="{{ old('email') }}" placeholder="you@company.ru">
                    </div>
                    <div class="ref-field">
                        <label>Пароль</label>
                        <input type="password" name="password" class="inp" placeholder="Минимум 8 символов" minlength="8">
                    </div>
                </div>
            </section>

            <div class="ref-actions">
                <button type="submit" class="btn btn-grad btn-lg">Создать заявку</button>
                <p class="ref-hint">Рекламный партнёр свяжется с вами в течение 24 часов</p>
            </div>
        </form>
    </div>
</main>

<script>
    /* Маска для телефонов */
    document.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('8')) value = '7' + value.slice(1);
            if (!value.startsWith('7') && value.length > 0) value = '7' + value;
            value = value.slice(0, 11);

            let formatted = '';
            if (value.length > 0) formatted = '+7';
            if (value.length > 1) formatted += ' (' + value.slice(1, 4);
            if (value.length >= 5) formatted += ') ' + value.slice(4, 7);
            if (value.length >= 8) formatted += '-' + value.slice(7, 9);
            if (value.length >= 10) formatted += '-' + value.slice(9, 11);

            e.target.value = formatted;
        });
    });

    /* ===== МНОЖЕСТВЕННЫЙ ВЫБОР УСЛУГ ===== */
    const tariffs = document.querySelectorAll('.promo-tariff');
    const summary = document.getElementById('promoSummary');
    const summaryList = document.getElementById('summaryList');
    const summaryTotal = document.getElementById('summaryTotal');

    function updateSummary() {
        const selected = [];
        let total = 0;

        tariffs.forEach(tariff => {
            const checkbox = tariff.querySelector('input[type="checkbox"]');
            if (checkbox.checked) {
                const id = tariff.dataset.svcId;
                const price = parseInt(tariff.dataset.price);
                const title = tariff.querySelector('h3').textContent;
                const icon = tariff.querySelector('.promo-tariff__icon').textContent;

                selected.push({ id, title, icon, price });
                total += price;
            }
        });

        if (selected.length === 0) {
            summary.hidden = true;
            return;
        }

        summary.hidden = false;
        summaryList.innerHTML = selected.map(s => `
            <div class="promo-summary__item">
                <span class="promo-summary__icon">${s.icon}</span>
                <span class="promo-summary__title">${s.title}</span>
                <span class="promo-summary__price">${s.price.toLocaleString('ru-RU')} ₽</span>
            </div>
        `).join('');

        summaryTotal.textContent = total.toLocaleString('ru-RU') + ' ₽';
    }

    tariffs.forEach(tariff => {
        // Обработка клика мышью
        tariff.addEventListener('click', () => {
            const checkbox = tariff.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked; // Переключаем вручную
            tariff.classList.toggle('promo-tariff--selected', checkbox.checked);
            updateSummary();
        });

        // Обработка клавиатуры (Enter / Space) для доступности
        tariff.addEventListener('keydown', (e) => {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                tariff.click();
            }
        });
    });

    /* Валидация формы перед отправкой */
    document.querySelector('form').addEventListener('submit', (e) => {
        const checked = document.querySelectorAll('input[name="services[]"]:checked');
        if (checked.length === 0) {
            e.preventDefault();
            // Если у вас есть функция toast() в landing.js, лучше использовать её:
            // toast('Выберите хотя бы одну услугу');
            alert('⚠️ Пожалуйста, выберите хотя бы одну услугу для продвижения.');

            // Подсветим секцию, чтобы привлечь внимание
            document.querySelector('.promo-tariffs').style.outline = '2px solid var(--rose)';
            document.querySelector('.promo-tariffs').style.outlineOffset = '4px';
            setTimeout(() => {
                document.querySelector('.promo-tariffs').style.outline = 'none';
            }, 2000);

            return false;
        }
    });
</script>



</body>
</html>
