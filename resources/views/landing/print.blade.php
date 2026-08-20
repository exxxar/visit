<section class="sec" id="print">
    <div class="wrap print-grid">
        <div class="reveal">
            <div class="kicker">09 · Печатная версия</div>
            <h2 style="font-size:clamp(28px,4vw,46px)">ВИЗИТ ДОНЕЦК<br><span class="grad">Теперь и в печатном формате</span></h2>
            <p class="s-sub">Листайте главный путеводитель по заведениям, услугам и отдыху города прямо на сайте.</p>

            <div class="mag-stats">
                <div class="mag-stat"><b class="grad">{{ $settings['counters']['places'] ?? '150' }}+</b><span>мест</span></div>
                <div class="mag-stat"><b class="grad">{{ $settings['counters']['categories'] ?? '30' }}+</b><span>категорий</span></div>
                <div class="mag-stat"><b class="grad">{{ $settings['counters']['pages'] ?? '64' }}</b><span>страницы</span></div>
            </div>

            <div class="mag-cats">
                <span class="chip">Еда</span><span class="chip">Отдых</span><span class="chip">Красота</span>
                <span class="chip">Покупки</span><span class="chip">Авто</span><span class="chip">Здоровье</span><span class="chip">Развлечения</span>
            </div>

            <div class="mag-btns">
                <button class="btn btn-grad" id="openViewer">Листать онлайн →</button>
                <button class="btn btn-ghost" id="magDownload">Скачать PDF ↓</button>
            </div>
        </div>

        <div class="mag-scene reveal">
            <div class="fpage fp1"></div><div class="fpage fp2"></div><div class="fpage fp3"></div>
            <div class="mag">
                <div class="mag-cover">
                    <img src="assets/hero-palm.jpg" alt="">
                    <div class="mag-in">
                        <div class="mag-top"><span>ГОРОДСКОЙ ГИД</span><span>№1 · {{ now()->year }}</span></div>
                        <div class="mag-title">ВИЗИТ<span>ДОНЕЦК</span></div>
                        <div class="mag-sub">Главный путеводитель по заведениям, услугам и отдыху города</div>
                        <div class="mag-foot"><i>{{ $settings['counters']['places'] ?? '150' }}+ мест</i><i>{{ $settings['counters']['categories'] ?? '30' }}+ категорий</i><i>9 районов</i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('landing.viewer')
