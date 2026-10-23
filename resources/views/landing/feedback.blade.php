<!-- Кнопка обратной связи (без изменений) -->
<button class="fb-trigger" id="fbTrigger" aria-label="Обратная связь">
    <span class="fb-trigger__ring"></span>
    <span class="fb-trigger__ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
    </span>
    <span class="fb-trigger__label">Помощь</span>
</button>

<!-- Модалка -->
<div class="fb-modal" id="fbModal">
    <div class="fb-modal__backdrop" data-close-fb></div>
    <div class="fb-modal__panel">
        <div class="fb-modal__head">
            <div>
                <div class="fb-modal__kicker">Поддержка и ИИ</div>
                <h3 class="fb-modal__title">Чем можем помочь?</h3>
            </div>
            <button class="fb-modal__close" data-close-fb aria-label="Закрыть">✕</button>
        </div>

        <!-- Вкладки -->
        <div class="fb-tabs">
            <button class="fb-tab active" data-tab="form">✉️ Написать нам</button>
            <button class="fb-tab" data-tab="chat">🤖 ИИ-помощник</button>
        </div>

        <!-- Вкладка 1: Форма -->
        <div class="fb-tab-content active" id="tabForm">
            <form id="fbForm" class="fb-modal__body">
                <div class="fb-field">
                    <label class="fb-label">Ваше имя *</label>
                    <input type="text" name="name" class="fb-input" placeholder="Иван" required>
                </div>
                <div class="fb-field">
                    <label class="fb-label">Телефон или email *</label>
                    <input type="text" name="contact" class="fb-input" placeholder="+7 999 123-45-67" required>
                </div>
                <div class="fb-field">
                    <label class="fb-label">Тема обращения *</label>
                    <select name="subject" class="fb-input" required>
                        <option value="">Выберите тему…</option>
                        <option value="general">💬 Общее</option>
                        <option value="partnership">🤝 Сотрудничество</option>
                        <option value="bug">🐛 Ошибка на сайте</option>
                        <option value="suggestion">💡 Предложение</option>
                    </select>
                </div>
                <div class="fb-field">
                    <label class="fb-label">Сообщение *</label>
                    <textarea name="message" class="fb-input" rows="4" placeholder="Опишите ваш вопрос…" required></textarea>
                </div>
                <div class="fb-actions">
                    <button type="button" class="btn btn-ghost" data-close-fb>Отмена</button>
                    <button type="submit" class="fb-submit" id="fbSubmit">
                        <span class="fb-submit__label">Отправить</span>
                        <span class="fb-submit__loading" hidden><span class="fb-spinner"></span> Отправляем…</span>
                    </button>
                </div>
            </form>
            <div class="fb-success" id="fbSuccess" hidden>
                <div class="fb-success__ico">✓</div>
                <h3>Сообщение отправлено!</h3>
                <p>Мы свяжемся с вами в ближайшее время.</p>
                <button type="button" class="btn btn-grad" data-close-fb>Закрыть</button>
            </div>
        </div>

        <!-- Вкладка 2: ИИ Чат -->
        <div class="fb-tab-content" id="tabChat">
            <div class="ai-chat-container">
                <div class="ai-chat-messages" id="aiChatMessages">
                    <div class="ai-message ai-message--bot">
                        <div class="ai-message__ico">🤖</div>
                        <div class="ai-message__text">
                            Привет! Я виртуальный помощник «ВИЗИТ ДОНЕЦК».
                            Спросите меня: <br>
                            • <i>"Где вкусно поесть в центре?"</i><br>
                            • <i>"Посоветуй кофейню с Wi-Fi"</i><br>
                            • <i>"Где есть детские комнаты?"</i>
                        </div>
                    </div>
                </div>
                <form id="aiChatForm" class="ai-chat-input-area">
                    <input type="text" id="aiChatInput" class="fb-input" placeholder="Задайте вопрос о заведениях..." autocomplete="off">
                    <button type="submit" class="ai-send-btn" id="aiSendBtn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<div class="toast" id="toast"></div>
<script>
    (function() {
        const modal = document.getElementById('fbModal');
        const trigger = document.getElementById('fbTrigger');
        const toast = document.getElementById('toast');

        // --- ЛОГИКА ВКЛАДОК ---
        const tabs = document.querySelectorAll('.fb-tab');
        const contents = document.querySelectorAll('.fb-tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab === 'form' ? 'tabForm' : 'tabChat').classList.add('active');
            });
        });

        // --- ЛОГИКА ОТКРЫТИЯ/ЗАКРЫТИЯ ---
        trigger.addEventListener('click', () => {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
        document.querySelectorAll('[data-close-fb]').forEach(el => el.addEventListener('click', close));
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && modal.classList.contains('open')) close();
        });

        function close() {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }

        function showToast(msg, type = 'error') {
            toast.textContent = msg;
            toast.className = 'toast toast--' + type + ' show';
            setTimeout(() => toast.classList.remove('show'), 4000);
        }

        // --- ЛОГИКА ФОРМЫ ОБРАТНОЙ СВЯЗИ ---
        const form = document.getElementById('fbForm');
        const submit = document.getElementById('fbSubmit');
        const success = document.getElementById('fbSuccess');

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
                if (!r.ok) throw new Error(Object.values(data.errors || {}).flat().join(', ') || data.message);

                form.hidden = true;
                success.hidden = false;
                setTimeout(() => {
                    form.reset();
                    form.hidden = false;
                    success.hidden = true;
                    close();
                }, 3000);
            } catch (err) {
                showToast('✕ ' + err.message);
            } finally {
                setLoading(false);
            }
        });

        function setLoading(state) {
            submit.disabled = state;
            submit.querySelector('.fb-submit__label').hidden = state;
            submit.querySelector('.fb-submit__loading').hidden = !state;
        }

        // --- ЛОГИКА ИИ ЧАТА ---
        const chatForm = document.getElementById('aiChatForm');
        const chatInput = document.getElementById('aiChatInput');
        const chatMessages = document.getElementById('aiChatMessages');
        const sendBtn = document.getElementById('aiSendBtn');
        let chatHistory = [];

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;

            // 1. Добавляем сообщение пользователя
            appendMessage('user', message);
            chatHistory.push({ role: 'user', content: message });
            chatInput.value = '';
            sendBtn.disabled = true;

            // 2. Показываем индикатор набора
            const typingId = showTypingIndicator();

            try {
                // 3. Запрос к нашему API
                const r = await fetch('/api/v1/ai-chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message, history: chatHistory }),
                });

                const data = await r.json();
                removeTypingIndicator(typingId);

                if (data.success) {
                    appendMessage('bot', data.message);
                    chatHistory.push({ role: 'assistant', content: data.message });
                } else {
                    appendMessage('bot', '⚠️ ' + data.message);
                }
            } catch (err) {
                removeTypingIndicator(typingId);
                appendMessage('bot', '⚠️ Ошибка сети. Попробуйте позже или используйте форму обратной связи.');
            } finally {
                sendBtn.disabled = false;
                chatInput.focus();
            }
        });

        function appendMessage(role, text) {
            const div = document.createElement('div');
            div.className = `ai-message ai-message--${role}`;

            // Простая защита от XSS и поддержка переносов строк
            const safeText = text.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');

            div.innerHTML = `
            <div class="ai-message__ico">${role === 'user' ? '👤' : '🤖'}</div>
            <div class="ai-message__text">${safeText}</div>
        `;
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTypingIndicator() {
            const id = 'typing-' + Date.now();
            const div = document.createElement('div');
            div.id = id;
            div.className = 'ai-message ai-message--bot';
            div.innerHTML = `
            <div class="ai-message__ico">🤖</div>
            <div class="ai-message__text ai-typing"><span></span><span></span><span></span></div>
        `;
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return id;
        }

        function removeTypingIndicator(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }
    })();
</script>
