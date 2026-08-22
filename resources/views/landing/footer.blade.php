<footer>
    <div class="wrap">
        <div class="f-grid">
            <div>
                <a class="logo" href="#top">ВИЗИТ<em>ДОНЕЦК</em></a>
                <p class="f-tag">Главный путеводитель по заведениям, услугам и отдыху города.</p>
                <div class="f-soc">
                    <a href="{{ $settings['socials']['telegram'] ?? '#' }}" class="demo">Telegram</a>
                    <a href="{{ $settings['socials']['vk'] ?? '#' }}" class="demo">VK</a>
                    <a href="{{ $settings['socials']['instagram'] ?? '#' }}" class="demo">Instagram</a>
                </div>
            </div>
            <div>
                <h4>Проект</h4>
                <a href="#" class="demo">О проекте</a>
                <a href="{{ route('catalog') }}">Все заведения</a>
                <a href="#business">Для бизнеса</a>
                <a href="#print">Печатная версия</a>
                <a href="#" class="demo">Реклама</a>
                <a href="{{ $settings['contacts']['email'] ?? '#' }}" class="demo">Контакты</a>
            </div>
            <div>
                <h4>Документы</h4>
                <a href="#" class="demo">Пользовательское соглашение</a>
                <a href="#" class="demo">Политика конфиденциальности</a>
            </div>
        </div>

        <div class="f-part">
            <span class="part-label">При поддержке</span>
            <span class="part-logo">
                <img src="assets/logo-moy-biznes.png" alt="Мой бизнес ДНР"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                <span class="part-fallback">МОЙ БИЗНЕС · ДНР</span>
            </span>
            <span class="part-logo">
                <img src="assets/logo-minec.png" alt="Минэкономразвития ДНР"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                <span class="part-fallback">МИНЭКОНОМРАЗВИТИЯ ДНР</span>
            </span>
        </div>

        <div class="f-bottom"><span>© {{ now()->year }} ВИЗИТ ДОНЕЦК</span><span>Сделано с любовью к городу</span></div>
    </div>
</footer>
