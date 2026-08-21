<section class="sec" id="map">
    <div class="wrap">
        <div class="s-head reveal">
            <div class="kicker">05 · Интерактивная карта</div>
            <h2>Открой Донецк на карте</h2>
            <p class="s-sub">Светящиеся точки — это места, которые уже любят горожане.</p>
        </div>

        {{-- переключатель режима --}}
        <div class="map-mode reveal">
            <button class="chip on" id="modeScheme">🗺 Схема города</button>
            <button class="chip" id="modeReal">🌍 Реальная карта</button>
        </div>

        <div class="map-tools reveal" id="mapFilters">
            <button class="chip on" data-f="all">Все</button>
            <button class="chip" data-f="food">Еда</button>
            <button class="chip" data-f="coffee">Кофе</button>
            <button class="chip" data-f="bars">Бары</button>
            <button class="chip" data-f="beauty">Красота</button>
            <button class="chip" data-f="sport">Спорт</button>
            <button class="chip" data-f="auto">Авто</button>
            <button class="chip" data-f="leisure">Отдых</button>
            <button class="chip" data-f="med">Медицина</button>
            <button class="chip" data-f="shop">Покупки</button>
            <select id="districtSel" aria-label="Район">
                <option value="all">Все районы</option>
                @foreach ($districts as $d)
                    <option>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="map-view reveal" id="mapView">
            {{-- схема --}}
            <div class="map-world" id="mapWorld">
                <svg viewBox="0 0 1000 620" preserveAspectRatio="none" fill="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="sg" x1="0" y1="0" x2="1" y2="0"><stop stop-color="#22d3ee"/><stop offset=".5" stop-color="#4f7dff"/><stop offset="1" stop-color="#f050e0"/></linearGradient>
                        <filter id="glow"><feGaussianBlur stdDeviation="4" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
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

            {{-- реальная карта --}}
            <div class="map-real" id="mapReal" hidden></div>

            <div class="map-card" id="mapCard" hidden>
                <button class="mc-x" id="mcClose">✕</button>
                <img id="mcImg" src="" alt="">
                <div class="mc-body">
                    <span class="mc-cat" id="mcCat"></span>
                    <h4 id="mcName"></h4>
                    <span class="mc-rate" id="mcRate"></span>
                    <p id="mcDesc"></p>
                    <a href="#" class="mc-open">Открыть →</a>
                </div>
            </div>

            <div class="map-legend">
                <span><i style="background:var(--orange)"></i>Еда</span><span><i style="background:var(--lime)"></i>Кофе</span>
                <span><i style="background:var(--magenta)"></i>Бары</span><span><i style="background:var(--violet)"></i>Красота</span>
                <span><i style="background:var(--cyan)"></i>Спорт</span><span><i style="background:var(--blue)"></i>Авто</span>
            </div>
        </div>
    </div>
</section>

<script>window.MAPTILER_KEY = @json(config('services.maptiler.key'));</script>
