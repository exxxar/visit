<!-- Кнопка обратной связи -->
<button class="fb-trigger" id="fbTrigger" aria-label="Обратная связь">
    <span class="fb-trigger__ring"></span>
    <span class="fb-trigger__ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
    </span>
    <span class="fb-trigger__label">Написать</span>
</button>

<!-- Модалка -->
<div class="fb-modal" id="fbModal">
    <div class="fb-modal__backdrop" data-close-fb></div>
    <div class="fb-modal__panel">
        <div class="fb-modal__head">
            <div>
                <div class="fb-modal__kicker">Обратная связь</div>
                <h3 class="fb-modal__title">Напишите нам</h3>
                <p class="fb-modal__sub">Ответим в течение рабочего дня</p>
            </div>
            <button class="fb-modal__close" data-close-fb aria-label="Закрыть">✕</button>
        </div>

        <form id="fbForm" class="fb-modal__body">
            <div class="fb-field">
                <label class="fb-label">Ваше имя *</label>
                <input type="text" name="name" class="fb-input" placeholder="Иван" required>
            </div>

            <div class="fb-field">
                <label class="fb-label">Телефон или email *</label>
                <input type="text" name="contact" class="fb-input" placeholder="+7 999 123-45-67 или mail@example.com" required>
            </div>

            <div class="fb-field">
                <label class="fb-label">Тема обращения *</label>
                <select name="subject" class="fb-input" required>
                    <option value="">Выберите тему…</option>
                    <option value="general">💬 Общее</option>
                    <option value="partnership">🤝 Сотрудничество</option>
                    <option value="ads">📣 Реклама</option>
                    <option value="bug">🐛 Ошибка на сайте</option>
                    <option value="suggestion">💡 Предложение</option>
                    <option value="complaint">⚠ Жалоба</option>
                </select>
            </div>

            <div class="fb-field">
                <label class="fb-label">Сообщение *</label>
                <textarea name="message" class="fb-input" rows="5" placeholder="Опишите ваш вопрос…" required></textarea>
                <div class="fb-counter"><span id="fbCounter">0</span> / 2000</div>
            </div>

            <div class="fb-actions">
                <button type="button" class="btn btn-ghost" data-close-fb>Отмена</button>
                <button type="submit" class="fb-submit" id="fbSubmit">
                    <span class="fb-submit__label">Отправить</span>
                    <span class="fb-submit__loading" hidden>
                        <span class="fb-spinner"></span>
                        Отправляем…
                    </span>
                </button>
            </div>
        </form>

        <!-- экран успеха -->
        <div class="fb-success" id="fbSuccess" hidden>
            <div class="fb-success__ico">✓</div>
            <h3>Сообщение отправлено!</h3>
            <p>Мы свяжемся с вами в ближайшее время.</p>
            <button type="button" class="btn btn-grad" data-close-fb>Закрыть</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
    (function() {
        const modal    = document.getElementById('fbModal');
        const trigger  = document.getElementById('fbTrigger');
        const form     = document.getElementById('fbForm');
        const submit   = document.getElementById('fbSubmit');
        const success  = document.getElementById('fbSuccess');
        const toast    = document.getElementById('toast');
        const counter  = document.getElementById('fbCounter');
        const message  = form.querySelector('[name="message"]');

        trigger.addEventListener('click', open);
        document.querySelectorAll('[data-close-fb]').forEach(el => el.addEventListener('click', close));
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && modal.classList.contains('open')) close();
        });

        message.addEventListener('input', () => {
            counter.textContent = message.value.length;
        });

        function open() {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function close() {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            setLoading(true);

            const formData = new FormData(form);
            try {
                const r = await fetch('/api/v1/feedback', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await r.json();
                if (!r.ok) {
                    const errors = Object.values(data.errors || {}).flat().join(', ');
                    throw new Error(errors || data.message || 'Ошибка отправки');
                }
                showSuccess();
            } catch (err) {
                showToast('✕ ' + err.message, 'error');
            } finally {
                setLoading(false);
            }
        });

        function setLoading(state) {
            submit.disabled = state;
            submit.querySelector('.fb-submit__label').hidden = state;
            submit.querySelector('.fb-submit__loading').hidden = !state;
        }

        function showSuccess() {
            form.hidden = true;
            success.hidden = false;
            setTimeout(() => {
                form.reset();
                counter.textContent = '0';
                form.hidden = false;
                success.hidden = true;
                close();
            }, 3000);
        }

        function showToast(msg, type) {
            toast.textContent = msg;
            toast.className = 'toast toast--' + type + ' show';
            setTimeout(() => toast.classList.remove('show'), 4000);
        }
    })();
</script>
