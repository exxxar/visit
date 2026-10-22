<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Добавить предприятие — КОД НОВОРОССИИ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/landing.js','resources/js/phone-mask.js'])
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
            <div class="ref-form-head__ico">🏢</div>
            <div>
                <h1>Добавить предприятие</h1>
                <p>Вас пригласил <b style="color:var(--cyan)">{{ $phoneFormatted }}</b></p>
            </div>
        </div>

        @if($errors->any())
            <div class="ref-error">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        {{-- ====== МУЛЬТИШАГОВАЯ ФОРМА ====== --}}
        <form method="POST" action="{{ route('ref.client.store') }}" enctype="multipart/form-data" id="clientMultiForm">
            @csrf

            {{-- прогресс-бар --}}
            <div class="m-steps" style="margin-bottom:8px">
                <div class="m-step on"></div>
                <div class="m-step"></div>
                <div class="m-step"></div>
                <div class="m-step"></div>
            </div>
            <div class="m-steps-lbl">
                <b id="stepLbl">Шаг 1 из 4</b>
                <span id="stepName">Основная информация</span>
            </div>

            {{-- ШАГ 1: Основная информация --}}
            <div class="m-pane on" data-pane="0">
                <div class="ref-section">
                    <h3>🏢 О заведении</h3>

                    <div class="f-row">
                        <label>Название организации *</label>
                        <input class="f-in" name="place_name" id="orgName" placeholder="Например: GASTRO BAR" value="{{ old('place_name') }}">
                    </div>

                    <div class="f-2">
                        <div class="f-row">
                            <label>Категория *</label>
                            <select class="f-in" name="category" id="orgCat">
                                <option value="">Выберите категорию</option>
                                <option value="Ресторан">Ресторан</option>
                                <option value="Кафе">Кафе</option>
                                <option value="Кофейня">Кофейня</option>
                                <option value="Бар">Бар</option>
                                <option value="Магазин">Магазин</option>
                                <option value="Салон красоты">Салон красоты</option>
                                <option value="SPA">SPA</option>
                                <option value="Фитнес">Фитнес</option>
                                <option value="Автосервис">Автосервис</option>
                                <option value="Медицинский центр">Медицинский центр</option>
                                <option value="Отель">Отель</option>
                                <option value="Развлечения">Развлечения</option>
                                <option value="Другое">Другое</option>
                            </select>
                        </div>
                        <div class="f-row">
                            <label>Район</label>
                            <select class="f-in" name="district_id">
                                <option value="">Выберите район</option>
                                @foreach($districts as $d)
                                    <option value="{{ $d->id }}" {{ old('district_id') == $d->id ? 'selected' : '' }}>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="f-row">
                        <label>Краткое описание</label>
                        <textarea class="f-in" name="description" placeholder="Пара предложений о вашем месте">{{ old('description') }}</textarea>
                    </div>

                    <div class="f-row">
                        <label>Адрес</label>
                        <input class="f-in" name="address" placeholder="Улица, дом" value="{{ old('address') }}">
                    </div>

                    <div class="f-2">
                        <div class="f-row">
                            <label>Телефон</label>
                            <input class="f-in" type="tel" name="place_phone" placeholder="+7 ___ ___-__-__" value="{{ old('place_phone') }}">
                        </div>
                        <div class="f-row">
                            <label>Email</label>
                            <input class="f-in" type="email" name="place_email" placeholder="info@place.ru" value="{{ old('place_email') }}">
                        </div>
                    </div>

                    <div class="f-row">
                        <label>Сайт / социальные сети</label>
                        <input class="f-in" name="site" placeholder="https://…" value="{{ old('site') }}">
                    </div>
                </div>
            </div>

            {{-- ШАГ 2: Контактное лицо + регистрация --}}
            <div class="m-pane" data-pane="1">
                <div class="ref-section">
                    <h3>👤 Контактное лицо</h3>

                    <div class="f-2">
                        <div class="f-row">
                            <label>Имя *</label>
                            <input class="f-in" name="contact_name" id="personName" placeholder="Как к вам обращаться" value="{{ old('contact_name') }}">
                        </div>
                        <div class="f-row">
                            <label>Должность</label>
                            <input class="f-in" name="contact_position" placeholder="Владелец, директор…" value="{{ old('contact_position') }}">
                        </div>
                    </div>

                    <div class="f-2">
                        <div class="f-row">
                            <label>Телефон *</label>
                            <input class="f-in" type="tel" name="contact_phone" placeholder="+7 ___ ___-__-__" value="{{ old('contact_phone') }}">
                        </div>
                        <div class="f-row">
                            <label>Email</label>
                            <input class="f-in" type="email" name="contact_email" placeholder="you@company.ru" value="{{ old('contact_email') }}">
                        </div>
                    </div>
                </div>

                <div class="ref-section">
                    <h3>🔐 Доступ к личному кабинету</h3>

                    <div class="f-row">
                        <label>Email для входа *</label>
                        <input class="f-in" type="email" name="email" placeholder="you@company.ru" value="{{ old('email') }}">
                    </div>

                    <div class="f-row">
                        <label>Пароль *</label>
                        <div class="pwd-wrap">
                            <input class="f-in" type="password" name="password" id="regPassword"
                                   placeholder="Минимум 8 символов" minlength="8" autocomplete="new-password">
                            <button type="button" class="pwd-toggle" data-target="regPassword" aria-label="Показать пароль">
                                <svg class="ico-eye" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="ico-eye-off" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" hidden>
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>

                        <!-- индикатор силы пароля -->
                        <div class="pwd-strength" id="pwdStrength" hidden>
                            <div class="pwd-strength__bars">
                                <span></span><span></span><span></span><span></span>
                            </div>
                            <span class="pwd-strength__label" id="pwdStrengthLabel"></span>
                        </div>
                    </div>

                    <div class="f-row">
                        <label>Повторите пароль *</label>
                        <div class="pwd-wrap">
                            <input class="f-in" type="password" name="password_confirmation" id="regPasswordConfirm"
                                   placeholder="Ещё раз тот же пароль" minlength="8" autocomplete="new-password">
                            <button type="button" class="pwd-toggle" data-target="regPasswordConfirm" aria-label="Показать пароль">
                                <svg class="ico-eye" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="ico-eye-off" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" hidden>
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <p class="pwd-match" id="pwdMatch" hidden></p>
                    </div>

                    <p class="ref-hint" style="margin-top:6px">
                        После отправки вы войдёте в личный кабинет, где сможете управлять карточкой заведения
                    </p>
                </div>
            </div>

            {{-- ШАГ 3: Медиа --}}
            <div class="m-pane" data-pane="2">
                <div class="ref-section">
                    <h3>📸 Фотографии и медиа</h3>

                    <div class="f-row">
                        <label>Логотип и фотографии</label>
                        <div class="drop" id="dropZone">📁<br><b>Перетащите фотографии сюда</b><br>или выберите файлы</div>
                        <input type="file" id="fileInput" name="media[]" multiple accept="image/*" hidden>
                        <div class="drop-files" id="fileChips"></div>
                    </div>

                    <div class="f-2">
                        <div class="f-row">
                            <label>Instagram / VK / Telegram</label>
                            <input class="f-in" name="social_handle" placeholder="@yourplace" value="{{ old('social_handle') }}">
                        </div>
                        <div class="f-row">
                            <label>Ссылка на сайт</label>
                            <input class="f-in" name="social_site" placeholder="https://…" value="{{ old('social_site') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ШАГ 4: Подтверждение + доп. услуги --}}
            <div class="m-pane" data-pane="3">

                {{-- Блок дополнительных услуг --}}
                <div class="ref-section">
                    <h3>⚡ Дополнительные услуги (необязательно)</h3>
                    <p class="ref-hint" style="margin-bottom:18px">
                        Усильте своё присутствие: закажите мобильное приложение или лендинг. Оплата после отправки заявки.
                    </p>

                    <div class="extras-grid" id="extrasGrid">
                        {{-- генерируется JS --}}
                    </div>

                    <div class="extras-summary" id="extrasSummary" hidden>
                        <div class="extras-summary__row">
                            <span>Выбрано услуг:</span>
                            <b id="extrasCount">0</b>
                        </div>
                        <div class="extras-summary__row extras-summary__row--total">
                            <span>Сумма к оплате:</span>
                            <b id="extrasTotal">0 ₽</b>
                        </div>
                        <button type="button" class="btn btn-grad" id="payExtrasBtn" hidden>
                            💳 Оплатить выбранные услуги
                        </button>
                    </div>
                </div>

                {{-- Скрытое поле с выбранными услугами --}}
                <input type="hidden" name="extra_services" id="extraServicesInput" value="[]">

                {{-- Подтверждение --}}
                <div class="ref-section">
                    <h3>✓ Подтверждение</h3>

                    <label class="chk">
                        <input type="checkbox" class="req-chk">
                        <i>✓</i>
                        <span>Я подтверждаю достоверность предоставленной информации</span>
                    </label>
                    <label class="chk">
                        <input type="checkbox" class="req-chk">
                        <i>✓</i>
                        <span>Я согласен на обработку персональных данных</span>
                    </label>
                    <label class="chk">
                        <input type="checkbox" class="req-chk">
                        <i>✓</i>
                        <span>Я принимаю условия размещения информации на платформе «КОД НОВОРОССИИ»</span>
                    </label>
                </div>
            </div>

            {{-- ===== МОДАЛКА ОПИСАНИЯ УСЛУГИ ===== --}}
            <div class="service-modal" id="serviceModal">
                <div class="service-modal__backdrop" data-close-service></div>
                <div class="service-modal__panel">
                    <button class="service-modal__close" data-close-service>✕</button>

                    <div class="service-modal__head">
                        <div class="service-modal__ico" id="svcIco">🍽</div>
                        <div>
                            <div class="service-modal__title" id="svcTitle">Название</div>
                            <div class="service-modal__desc" id="svcDesc">Описание</div>
                        </div>
                    </div>

                    <div class="service-modal__body">
                        <h4>Что включено</h4>
                        <ul class="service-includes" id="svcIncludes"></ul>

                        <h4 style="margin-top:22px">Примеры работ</h4>
                        <div class="service-examples" id="svcExamples"></div>
                    </div>

                    <div class="service-modal__foot">
                        <div class="service-modal__price">
                            <span>Стоимость:</span>
                            <b id="svcPrice">0 ₽</b>
                        </div>
                        <button class="btn btn-grad" id="svcChooseBtn">Выбрать услугу</button>
                    </div>
                </div>
            </div>
            {{-- ШАГ 4: Подтверждение + доп. услуги --}}
            <div class="m-pane" data-pane="3">

                {{-- Блок дополнительных услуг --}}
                <div class="ref-section">
                    <h3>⚡ Дополнительные услуги (необязательно)</h3>
                    <p class="ref-hint" style="margin-bottom:18px">
                        Усильте своё присутствие: закажите мобильное приложение или лендинг. Оплата после отправки заявки.
                    </p>

                    <div class="extras-grid" id="extrasGrid">
                        {{-- генерируется JS --}}
                    </div>

                    <div class="extras-summary" id="extrasSummary" hidden>
                        <div class="extras-summary__row">
                            <span>Выбрано услуг:</span>
                            <b id="extrasCount">0</b>
                        </div>
                        <div class="extras-summary__row extras-summary__row--total">
                            <span>Сумма к оплате:</span>
                            <b id="extrasTotal">0 ₽</b>
                        </div>
                        <button type="button" class="btn btn-grad" id="payExtrasBtn" hidden>
                            💳 Оплатить выбранные услуги
                        </button>
                    </div>
                </div>

                {{-- Скрытое поле с выбранными услугами --}}
                <input type="hidden" name="extra_services" id="extraServicesInput" value="[]">

                {{-- Подтверждение --}}
                <div class="ref-section">
                    <h3>✓ Подтверждение</h3>

                    <label class="chk">
                        <input type="checkbox" class="req-chk">
                        <i>✓</i>
                        <span>Я подтверждаю достоверность предоставленной информации</span>
                    </label>
                    <label class="chk">
                        <input type="checkbox" class="req-chk">
                        <i>✓</i>
                        <span>Я согласен на обработку персональных данных</span>
                    </label>
                    <label class="chk">
                        <input type="checkbox" class="req-chk">
                        <i>✓</i>
                        <span>Я принимаю условия размещения информации на платформе «КОД НОВОРОССИИ»</span>
                    </label>
                </div>
            </div>

            {{-- ===== МОДАЛКА ОПИСАНИЯ УСЛУГИ ===== --}}
            <div class="service-modal" id="serviceModal">
                <div class="service-modal__backdrop" data-close-service></div>
                <div class="service-modal__panel">
                    <button class="service-modal__close" data-close-service>✕</button>

                    <div class="service-modal__head">
                        <div class="service-modal__ico" id="svcIco">🍽</div>
                        <div>
                            <div class="service-modal__title" id="svcTitle">Название</div>
                            <div class="service-modal__desc" id="svcDesc">Описание</div>
                        </div>
                    </div>

                    <div class="service-modal__body">
                        <h4>Что включено</h4>
                        <ul class="service-includes" id="svcIncludes"></ul>

                        <h4 style="margin-top:22px">Примеры работ</h4>
                        <div class="service-examples" id="svcExamples"></div>
                    </div>

                    <div class="service-modal__foot">
                        <div class="service-modal__price">
                            <span>Стоимость:</span>
                            <b id="svcPrice">0 ₽</b>
                        </div>
                        <button class="btn btn-grad" id="svcChooseBtn">Выбрать услугу</button>
                    </div>
                </div>
            </div>
            {{-- Навигация --}}
            <div class="m-nav" style="margin-top:24px;max-width:820px;margin-left:auto;margin-right:auto">
                <button type="button" class="btn btn-ghost" id="mBack" hidden>← Назад</button>
                <button type="button" class="btn btn-grad" id="mNext">Далее →</button>
            </div>
        </form>

        <div class="toast" id="toast"></div>
    </div>
</main>

<script>
    (function() {
        const form = document.getElementById('clientMultiForm');
        const panes = form.querySelectorAll('.m-pane');
        const steps = form.querySelectorAll('.m-step');
        const stepLbl = document.getElementById('stepLbl');
        const stepName = document.getElementById('stepName');
        const backBtn = document.getElementById('mBack');
        const nextBtn = document.getElementById('mNext');

        const STEP_NAMES = ['Основная информация', 'Контактное лицо', 'Медиа', 'Подтверждение'];
        let step = 0;

        function goStep(n) {
            step = n;
            panes.forEach((p, i) => p.classList.toggle('on', i === n));
            steps.forEach((s, i) => s.classList.toggle('on', i <= n));
            stepLbl.textContent = `Шаг ${n + 1} из 4`;
            stepName.textContent = STEP_NAMES[n];
            backBtn.hidden = n === 0;
            nextBtn.textContent = n === 3 ? 'Отправить заявку' : 'Далее →';
            form.querySelector('.m-pane.on').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        backBtn.onclick = () => goStep(step - 1);

        nextBtn.onclick = () => {
            // валидация шага
            if (step === 0) {
                if (!form.orgName.value.trim() || !form.orgCat.value) {
                    showToast('Заполните название и категорию');
                    return;
                }
            }
            if (step === 1) {
                if (!form.contact_name.value.trim()) {
                    showToast('Укажите имя контактного лица');
                    return;
                }
                if (!form.email.value.trim()) {
                    showToast('Укажите email для входа');
                    return;
                }
                if (!form.password.value || form.password.value.length < 8) {
                    showToast('Пароль должен быть минимум 8 символов');
                    return;
                }
                // НОВОЕ: проверка совпадения паролей
                if (form.password.value !== form.password_confirmation.value) {
                    showToast('Пароли не совпадают');
                    pwdConfirm.focus();
                    updateMatch();
                    return;
                }
            }
            if (step === 3) {
                const unchecked = form.querySelectorAll('.req-chk:not(:checked)');
                if (unchecked.length) {
                    showToast('Подтвердите все условия размещения');
                    return;
                }
                nextBtn.disabled = true;
                nextBtn.textContent = 'Отправляем…';
                form.submit();
                return;
            }
            goStep(step + 1);
        };

        /* ====== drag-and-drop ====== */
        const dz = document.getElementById('dropZone');
        const fi = document.getElementById('fileInput');
        const fc = document.getElementById('fileChips');

        dz.onclick = () => fi.click();
        ['dragenter', 'dragover'].forEach(ev => dz.addEventListener(ev, e => {
            e.preventDefault();
            dz.classList.add('drag');
        }));
        ['dragleave', 'drop'].forEach(ev => dz.addEventListener(ev, e => {
            e.preventDefault();
            dz.classList.remove('drag');
        }));
        dz.addEventListener('drop', e => addFiles(e.dataTransfer.files));
        fi.onchange = () => addFiles(fi.files);

        function addFiles(list) {
            [...list].forEach(f => {
                const s = document.createElement('span');
                s.textContent = '🖼 ' + f.name;
                fc.appendChild(s);
            });
            if (list.length) showToast('Файлы добавлены: ' + list.length);
        }

        /* ====== toast ====== */
        function showToast(msg) {
            const t = document.getElementById('toast');
            if (!t) return;
            t.textContent = msg;
            t.classList.add('show');
            clearTimeout(t._t);
            t._t = setTimeout(() => t.classList.remove('show'), 2600);
        }

        /* ====== ДОП. УСЛУГИ ====== */
        const EXTRA_SERVICES = [
            {
                id: 'app_food',
                icon: '🍽',
                title: 'Приложение для общепита',
                desc: 'iOS + Android для ресторанов, кафе, кофеен',
                price: 45000,
                color: 'orange',
                includes: [
                    'Нативные приложения iOS и Android',
                    'Меню с фото, ценами и модификаторами',
                    'Онлайн-заказ столика и еды',
                    'Программа лояльности и бонусы',
                    'Push-уведомления об акциях',
                    'Лендинг-страница заведения',
                    'Админ-панель для управления меню',
                ],
                examples: [
                    { name: 'Gastro Bar Demo', url: '#demo1' },
                    { name: 'Coffee House', url: '#demo2' },
                    { name: 'Sushi Express', url: '#demo3' },
                ],
            },
            {
                id: 'app_beauty',
                icon: '💅',
                title: 'Приложение для бьюти-сферы',
                desc: 'Салоны красоты, барбершопы, SPA-центры',
                price: 48000,
                color: 'magenta',
                includes: [
                    'iOS + Android приложения',
                    'Онлайн-запись к мастерам',
                    'Портфолио работ с галереей',
                    'Карточки мастеров и услуги',
                    'Отзывы и рейтинги',
                    'Напоминания о записи',
                    'Программа лояльности',
                ],
                examples: [
                    { name: 'Beauty Studio', url: '#demo4' },
                    { name: 'Barber Shop', url: '#demo5' },
                    { name: 'SPA Center', url: '#demo6' },
                ],
            },
            {
                id: 'app_fitness',
                icon: '💪',
                title: 'Приложение для фитнеса',
                desc: 'Спортзалы, студии йоги, персональные тренеры',
                price: 52000,
                color: 'lime',
                includes: [
                    'iOS + Android приложения',
                    'Расписание занятий и тренеров',
                    'Онлайн-запись на тренировки',
                    'Абонементы и оплата',
                    'Трекер прогресса',
                    'Видео-уроки и программы',
                    'Push-напоминания',
                ],
                examples: [
                    { name: 'FitClub', url: '#demo7' },
                    { name: 'Yoga Studio', url: '#demo8' },
                    { name: 'CrossFit Box', url: '#demo9' },
                ],
            },
            {
                id: 'app_auto',
                icon: '🔧',
                title: 'Приложение для автосервисов',
                desc: 'СТО, автомойки, детейлинг, шиномонтаж',
                price: 42000,
                color: 'blue',
                includes: [
                    'iOS + Android приложения',
                    'Онлайн-запись на сервис',
                    'Каталог услуг с ценами',
                    'История обслуживания авто',
                    'Уведомления о ТО',
                    'Расчёт стоимости',
                    'Отзывы клиентов',
                ],
                examples: [
                    { name: 'Auto Service Pro', url: '#demo10' },
                    { name: 'Car Wash 24', url: '#demo11' },
                    { name: 'Detailing Lab', url: '#demo12' },
                ],
            },
            {
                id: 'app_blog',
                icon: '📸',
                title: 'Приложение для блогеров',
                desc: 'Личный бренд, онлайн-школа, контент-платформа',
                price: 55000,
                color: 'violet',
                includes: [
                    'iOS + Android приложения',
                    'Лента контента и сторис',
                    'Платная подписка и курсы',
                    'Чат с подписчиками',
                    'Push-уведомления',
                    'Аналитика и статистика',
                    'Интеграция с соцсетями',
                ],
                examples: [
                    { name: 'Creator Hub', url: '#demo13' },
                    { name: 'Online School', url: '#demo14' },
                    { name: 'Influencer App', url: '#demo15' },
                ],
            },
            {
                id: 'landing',
                icon: '🌐',
                title: 'Лендинг-страница',
                desc: 'Одностраничный сайт с высокой конверсией',
                price: 18000,
                color: 'cyan',
                includes: [
                    'Уникальный адаптивный дизайн',
                    'До 7 секций под ваш бизнес',
                    'Форма заявки и интеграция с CRM',
                    'SEO-оптимизация базовая',
                    'Подключение аналитики',
                    'Хостинг на 1 год в подарок',
                    'SSL-сертификат',
                ],
                examples: [
                    { name: 'Startup Landing', url: '#demo16' },
                    { name: 'Restaurant Page', url: '#demo17' },
                    { name: 'Event Page', url: '#demo18' },
                ],
            },
        ];

        const COLOR_MAP = {
            orange:  { grad: 'linear-gradient(135deg,#ff8a3c,#f050e0)', color: '#ff8a3c' },
            magenta: { grad: 'linear-gradient(135deg,#f050e0,#8b5cf6)', color: '#f050e0' },
            lime:    { grad: 'linear-gradient(135deg,#a3e635,#22d3ee)', color: '#a3e635' },
            blue:    { grad: 'linear-gradient(135deg,#4f7dff,#22d3ee)', color: '#4f7dff' },
            violet:  { grad: 'linear-gradient(135deg,#8b5cf6,#f050e0)', color: '#8b5cf6' },
            cyan:    { grad: 'linear-gradient(135deg,#22d3ee,#8b5cf6)', color: '#22d3ee' },
        };

        let selectedExtras = new Set();

        /* отрисовка карточек услуг */
        function renderExtras() {
            const grid = document.getElementById('extrasGrid');
            grid.innerHTML = EXTRA_SERVICES.map(svc => {
                const c = COLOR_MAP[svc.color];
                const selected = selectedExtras.has(svc.id);
                return `
            <div class="extra-card ${selected ? 'extra-card--selected' : ''}"
                 style="--ac:${c.color}" data-svc-id="${svc.id}">
                <div class="extra-card__ico" style="background:${c.grad}">${svc.icon}</div>
                <div class="extra-card__body">
                    <h4>${svc.title}</h4>
                    <p>${svc.desc}</p>
                    <div class="extra-card__foot">
                        <b class="extra-card__price">${svc.price.toLocaleString('ru-RU')} ₽</b>
                        <button type="button" class="extra-card__info" data-info="${svc.id}">
                            Подробнее →
                        </button>
                    </div>
                </div>
                <label class="extra-card__check">
                    <input type="checkbox" ${selected ? 'checked' : ''} data-check="${svc.id}">
                    <i>✓</i>
                </label>
            </div>
        `;
            }).join('');
        }

        /* обновление сводки */
        function updateSummary() {
            const sumEl = document.getElementById('extrasSummary');
            const countEl = document.getElementById('extrasCount');
            const totalEl = document.getElementById('extrasTotal');
            const payBtn = document.getElementById('payExtrasBtn');
            const input = document.getElementById('extraServicesInput');

            const total = [...selectedExtras].reduce((sum, id) => {
                const svc = EXTRA_SERVICES.find(s => s.id === id);
                return sum + (svc?.price ?? 0);
            }, 0);

            countEl.textContent = selectedExtras.size;
            totalEl.textContent = total.toLocaleString('ru-RU') + ' ₽';
            sumEl.hidden = selectedExtras.size === 0;
            payBtn.hidden = selectedExtras.size === 0;
            input.value = JSON.stringify([...selectedExtras]);

            saveExtras(); // ← добавь в конец
        }

        /* обработчики на карточках */
        function bindExtras() {
            const grid = document.getElementById('extrasGrid');

            grid.addEventListener('change', (e) => {
                const chk = e.target.closest('[data-check]');
                if (!chk) return;
                const id = chk.dataset.check;
                if (chk.checked) selectedExtras.add(id);
                else selectedExtras.delete(id);
                renderExtras();
                bindExtras();
                updateSummary();
            });

            grid.addEventListener('click', (e) => {
                const infoBtn = e.target.closest('[data-info]');
                if (!infoBtn) return;
                e.stopPropagation();
                openServiceModal(infoBtn.dataset.info);
            });
        }

        /* ===== модалка услуги ===== */
        function openServiceModal(svcId) {
            const svc = EXTRA_SERVICES.find(s => s.id === svcId);
            if (!svc) return;

            const c = COLOR_MAP[svc.color];
            document.getElementById('svcIco').textContent = svc.icon;
            document.getElementById('svcIco').style.background = c.grad;
            document.getElementById('svcTitle').textContent = svc.title;
            document.getElementById('svcDesc').textContent = svc.desc;
            document.getElementById('svcPrice').textContent = svc.price.toLocaleString('ru-RU') + ' ₽';

            document.getElementById('svcIncludes').innerHTML =
                svc.includes.map(i => `<li><span class="svc-check">✓</span><span>${i}</span></li>`).join('');

            document.getElementById('svcExamples').innerHTML =
                svc.examples.map(ex => `
            <a href="${ex.url}" target="_blank" rel="noopener" class="svc-example">
                <div class="svc-example__preview">🖼</div>
                <span>${ex.name}</span>
            </a>
        `).join('');

            const modal = document.getElementById('serviceModal');
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';

            const chooseBtn = document.getElementById('svcChooseBtn');
            chooseBtn.textContent = selectedExtras.has(svcId) ? '✓ Убрать из заказа' : 'Выбрать услугу';
            chooseBtn.onclick = () => {
                if (selectedExtras.has(svcId)) selectedExtras.delete(svcId);
                else selectedExtras.add(svcId);
                closeServiceModal();
                renderExtras();
                bindExtras();
                updateSummary();
            };
        }

        function closeServiceModal() {
            document.getElementById('serviceModal').classList.remove('open');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('[data-close-service]').forEach(el => {
            el.addEventListener('click', closeServiceModal);
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && document.getElementById('serviceModal').classList.contains('open')) {
                closeServiceModal();
            }
        });

        /* ===== оплата (заглушка) ===== */
        document.getElementById('payExtrasBtn').addEventListener('click', async () => {
            const btn = document.getElementById('payExtrasBtn');
            btn.disabled = true;
            btn.textContent = 'Генерируем счёт…';

            // Имитация генерации ссылки на оплату
            const services = [...selectedExtras];
            const total = services.reduce((sum, id) => {
                const s = EXTRA_SERVICES.find(x => x.id === id);
                return sum + (s?.price ?? 0);
            }, 0);

            // заглушка: формируем фейковую ссылку
            const fakeUrl = `https://pay.vizit-donetsk.ru/invoice?services=${services.join(',')}&amount=${total}&ref=${encodeURIComponent('{{ $phoneFormatted }}')}`;

            await new Promise(r => setTimeout(r, 800));
            btn.disabled = false;
            btn.textContent = '💳 Оплатить выбранные услуги';

            showToast('Перенаправляем на оплату…');

            // открываем в новой вкладке (сейчас — заглушка)
            setTimeout(() => {
                window.open(fakeUrl, '_blank');
            }, 600);
        });

        /* инициализация */
        renderExtras();
        bindExtras();

        /* ===== ПОКАЗАТЬ / СКРЫТЬ ПАРОЛЬ ===== */
        document.querySelectorAll('.pwd-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                if (!input) return;

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';

                btn.querySelector('.ico-eye').hidden = isHidden;
                btn.querySelector('.ico-eye-off').hidden = !isHidden;

                input.focus();
            });
        });

        /* ===== ИНДИКАТОР СИЛЫ ПАРОЛЯ ===== */
        const pwdInput = document.getElementById('regPassword');
        const pwdConfirm = document.getElementById('regPasswordConfirm');
        const pwdStrength = document.getElementById('pwdStrength');
        const pwdStrengthLabel = document.getElementById('pwdStrengthLabel');
        const pwdMatch = document.getElementById('pwdMatch');

        const STRENGTH_LABELS = ['', 'Слабый', 'Средний', 'Хороший', 'Надёжный'];
        const STRENGTH_COLORS = ['', 'var(--rose)', 'var(--orange)', 'var(--yellow)', 'var(--lime)'];

        function calcStrength(pwd) {
            if (!pwd) return 0;
            let score = 0;
            if (pwd.length >= 8) score++;
            if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++;
            if (/\d/.test(pwd)) score++;
            if (/[^A-Za-z0-9]/.test(pwd)) score++;
            return score;
        }

        function updateStrength() {
            const score = calcStrength(pwdInput.value);

            if (!pwdInput.value) {
                pwdStrength.hidden = true;
                return;
            }

            pwdStrength.hidden = false;
            pwdStrength.dataset.level = score;
            pwdStrengthLabel.textContent = STRENGTH_LABELS[score];
            pwdStrengthLabel.style.color = STRENGTH_COLORS[score];

            pwdStrength.querySelectorAll('.pwd-strength__bars span').forEach((bar, i) => {
                bar.classList.toggle('on', i < score);
                bar.style.background = i < score ? STRENGTH_COLORS[score] : '';
            });
        }

        function updateMatch() {
            if (!pwdConfirm.value) {
                pwdMatch.hidden = true;
                pwdConfirm.classList.remove('is-ok', 'is-err');
                return;
            }

            pwdMatch.hidden = false;
            const isMatch = pwdInput.value === pwdConfirm.value;

            if (isMatch) {
                pwdMatch.textContent = '✓ Пароли совпадают';
                pwdMatch.classList.add('ok');
                pwdMatch.classList.remove('err');
                pwdConfirm.classList.add('is-ok');
                pwdConfirm.classList.remove('is-err');
            } else {
                pwdMatch.textContent = '✕ Пароли не совпадают';
                pwdMatch.classList.add('err');
                pwdMatch.classList.remove('ok');
                pwdConfirm.classList.add('is-err');
                pwdConfirm.classList.remove('is-ok');
            }
        }

        pwdInput.addEventListener('input', () => { updateStrength(); updateMatch(); });
        pwdConfirm.addEventListener('input', updateMatch);

        /* ===== АВТОСОХРАНЕНИЕ ФОРМЫ ===== */
        const clientFormEl = document.getElementById('clientMultiForm');
        const clientAutosave = new FormAutoSave(clientFormEl, 'ref_client_form', {
            excludeSelectors: [
                '[type="password"]',
                '[type="file"]',
                'input[name="_token"]',
            ],
        });

        /* сохраняем выбранные доп. услуги отдельно */
        function saveExtras() {
            try {
                localStorage.setItem('ref_client_extras', JSON.stringify([...selectedExtras]));
            } catch (e) {}
        }

        function restoreExtras() {
            try {
                const raw = localStorage.getItem('ref_client_extras');
                if (!raw) return;
                const saved = JSON.parse(raw);
                if (Array.isArray(saved)) {
                    saved.forEach(id => selectedExtras.add(id));
                    renderExtras();
                    bindExtras();
                    updateSummary();
                }
            } catch (e) {}
        }

        restoreExtras();

        /* очищаем после успешной отправки */
        const originalSubmit = form.submit.bind(form);
        nextBtn.addEventListener('click', () => {
            // очистка произойдёт после успешного редиректа
            // помечаем флаг, что форма отправлена
            window.addEventListener('beforeunload', () => {
                if (nextBtn.disabled && nextBtn.textContent.includes('Отправляем')) {
                    clientAutosave.clear();
                    localStorage.removeItem('ref_client_extras');
                }
            });
        });
    })();
</script>

</body>
</html>
