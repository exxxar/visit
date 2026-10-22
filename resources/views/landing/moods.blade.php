<section class="sec" id="moods">
    <div class="wrap">
        <div class="s-head reveal">
            <div class="kicker">03 · Настроение</div>
            <h2>Какой сегодня план?</h2>
            <p class="s-sub">Выберите настроение — мы подскажем маршрут.</p>
        </div>
        <div class="moods">
            <div class="m-card reveal" data-mood="bars" data-msg="Вечер в городе: бары, рестораны, клубы" style="--ac:var(--violet)">
                <span class="m-emo"></span><h3>Вечер в городе</h3><p>Бары, рестораны, караоке и клубы.</p>
            </div>
            <div class="m-card reveal" data-mood="coffee" data-msg="Медленное утро: кофейни и завтраки" style="--ac:var(--orange)">
                <span class="m-emo">☕</span><h3>Медленное утро</h3><p>Кофейни, пекарни и завтраки.</p>
            </div>
            <div class="m-card reveal" data-mood="food" data-msg="Вкусные места: рестораны и гастробары" style="--ac:var(--rose)">
                <span class="m-emo">🍽</span><h3>Хочу вкусно поесть</h3><p>Лучшие рестораны и гастробары.</p>
            </div>
            <div class="m-card reveal" data-mood="beauty" data-msg="Время для себя: красота и релакс" style="--ac:var(--magenta)">
                <span class="m-emo">💆</span><h3>Время для себя</h3><p>SPA, массаж и красота.</p>
            </div>
            <div class="m-card reveal" data-mood="leisure" data-msg="Куда пойти: кино, концерты, развлечения" style="--ac:var(--cyan)">
                <span class="m-emo">🎉</span><h3>Пойти куда-нибудь</h3><p>Кино, концерты, развлечения.</p>
            </div>
            <div class="m-card reveal" data-mood="leisure" data-msg="Семейный день: отдых и развлечения" style="--ac:var(--lime)">
                <span class="m-emo">👨‍👩</span><h3>Семейный день</h3><p>Детские центры, отдых и развлечения.</p>
            </div>
            <div class="m-card reveal" data-mood="auto" data-msg="Для автомобиля: сервис и уход" style="--ac:var(--blue)">
                <span class="m-emo">🚗</span><h3>Позаботиться об автомобиле</h3><p>Сервис, мойки и детейлинг.</p>
            </div>
        </div>

        {{-- НОВЫЙ БЛОК: Призыв к регистрации для туристов --}}
        @guest
            <div class="mood-auth-prompt reveal">
                <div class="mood-auth-prompt__content">
                    <div class="mood-auth-prompt__ico">🎒</div>
                    <h3>Стань исследователем города</h3>
                    <p>Создай аккаунт, чтобы сохранять избранные места, оставлять отзывы и получать персональные рекомендации от ИИ-помощника.</p>
                    <div class="mood-auth-prompt__actions">
                        <a href="/register" class="btn btn-grad">Создать аккаунт</a>
                        <a href="/login" class="btn btn-ghost">Уже есть аккаунт</a>
                    </div>
                </div>
                <div class="mood-auth-prompt__features">
                    <div class="feature">
                        <span class="feature__ico">❤️</span>
                        <span>Избранное на всех устройствах</span>
                    </div>
                    <div class="feature">
                        <span class="feature__ico">⭐</span>
                        <span>Оставляй отзывы и оценки</span>
                    </div>
                    <div class="feature">
                        <span class="feature__ico">🤖</span>
                        <span>Персональные рекомендации ИИ</span>
                    </div>
                </div>
            </div>
        @endguest
    </div>
</section>
