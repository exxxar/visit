<section class="sec" id="map">
    <div class="wrap">
        <div class="s-head reveal">
            <div class="kicker">05 · Интерактивная карта</div>
            <h2>Открой Донецк на карте</h2>
            <p class="s-sub">Светящиеся точки — это места и объекты, которые уже любят горожане.</p>
        </div>

        {{-- Переключатель режима карты --}}
        <div class="map-mode reveal">
            <button class="chip on" id="modeScheme">🗺 Схема города</button>
            <button class="chip" id="modeReal">🌍 Реальная карта</button>
        </div>

        {{-- Инструменты: динамические фильтры + поиск --}}
        <div class="map-tools reveal">
            <!-- Сюда JavaScript будет рендерить кнопки слоёв -->
            <div id="mapFilters" style="display: contents;"></div>

            <!-- Новый блок поиска по карте -->
            <div class="map-search-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="mapSearchInput" placeholder="Название или категория..." autocomplete="off">
                <button type="button" id="mapSearchClear" class="map-search-clear" hidden>✕</button>
            </div>
        </div>

        <div class="map-view reveal" id="mapView">
            {{-- Схематичная карта (SVG) --}}
            <div class="map-world" id="mapWorld">
                <svg viewBox="0 0 1000 620" preserveAspectRatio="none" fill="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="sg" x1="0" y1="0" x2="1" y2="0">
                            <stop stop-color="#22d3ee"/>
                            <stop offset=".5" stop-color="#4f7dff"/>
                            <stop offset="1" stop-color="#f050e0"/>
                        </linearGradient>
                        <filter id="glow">
                            <feGaussianBlur stdDeviation="4" result="b"/>
                            <feMerge>
                                <feMergeNode in="b"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <path d="M618 -10 C 600 120, 660 220, 636 330 C 616 430, 676 520, 656 630" stroke="#22d3ee" stroke-width="16" opacity=".12"/>
                    <path d="M618 -10 C 600 120, 660 220, 636 330 C 616 430, 676 520, 656 630" stroke="#22d3ee" stroke-width="2" opacity=".45"/>
                    <g stroke="#7dd3fc" stroke-width="1" opacity=".16">
                        <path d="M-10 300 L1010 250"/><path d="M-10 160 L1010 200"/><path d="M-10 452 L1010 420"/>
                        <path d="M210 -10 L260 630"/><path d="M480 -10 L460 630"/><path d="M790 -10 L750 630"/>
                        <path d="M-10 80 L1010 60"/><path d="M-10 560 L1010 540"/><path d="M90 -10 L130 630"/><path d="M920 -10 L900 630"/>
                    </g>
                    <g stroke="url(#sg)" stroke-width="1.8" opacity=".6" filter="url(#glow)">
                        <path d="M-10 380 C 240 340, 480 400, 720 350 C 860 322, 950 340, 1010 330"/>
                        <path d="M340 -10 C 360 200, 320 420, 360 630"/>
                    </g>
                    <path d="M120 470 C 180 220, 420 90, 640 120 C 860 150, 940 330, 900 470" stroke="#8b5cf6" stroke-width="1.2" stroke-dasharray="3 7" opacity=".4"/>
                    <ellipse cx="340" cy="200" rx="95" ry="52" fill="#8b5cf6" opacity=".08"/>
                    <ellipse cx="720" cy="440" rx="115" ry="62" fill="#a3e635" opacity=".06"/>
                    <path class="route" d="M120 520 C 300 420, 420 470, 520 330 C 600 240, 700 260, 830 180" stroke="url(#sg)" stroke-width="2.4" filter="url(#glow)"/>
                </svg>
            </div>

            {{-- Реальная карта (MapLibre) --}}
            <div class="map-real" id="mapReal" hidden></div>

            {{-- Всплывающая карточка места --}}
            <div class="map-card" id="mapCard" hidden>
                <button class="mc-x" id="mcClose">✕</button>
                <img id="mcImg" src="" alt="">
                <div class="mc-body">
                    <span class="mc-cat" id="mcCat"></span>
                    <h4 id="mcName"></h4>
                    <span class="mc-rate" id="mcRate"></span>
                    <p id="mcDesc"></p>
                    <a href="#" class="mc-open">Открыть страницу →</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Передача ключа MapTiler в JS --}}
<script>window.MAPTILER_KEY = @json(config('services.maptiler.key'));</script>
