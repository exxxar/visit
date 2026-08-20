<section class="sec" id="districts">
    <div class="wrap">
        <div class="s-head reveal">
            <div class="kicker">04 · Районы</div>
            <h2>Исследуйте город<br><span class="grad">Выберите свой район</span></h2>
            <p class="s-sub">Все интересные места рядом с вами.</p>
        </div>

        <div class="dist-map-wrap reveal" id="distMapWrap">
            <svg viewBox="0 0 640 420" fill="none" aria-hidden="true">
                <defs>
                    <linearGradient id="drg" x1="0" y1="0" x2="1" y2="0">
                        <stop stop-color="#22d3ee"/><stop offset=".5" stop-color="#8b5cf6"/><stop offset="1" stop-color="#f050e0"/>
                    </linearGradient>
                    @foreach (['kui'=>'kuibyshevsky','kie'=>'kyivsky','kal'=>'kalininsky','kir'=>'kirovsky','vor'=>'voroshilovsky','bud'=>'budyonnovsky','pet'=>'petrovsky','len'=>'leninsky','pro'=>'proletarsky'] as $id => $file)
                        <pattern id="dg-{{ $id }}" width="640" height="420" patternUnits="userSpaceOnUse">
                            <image href="assets/districts/{{ $file }}.jpg" width="640" height="420" preserveAspectRatio="xMidYMid slice"/>
                        </pattern>
                    @endforeach
                </defs>

                <polygon class="d-area" data-d="Куйбышевский"  style="--dc:var(--blue)"    fill="url(#dg-kui)" points="60,60 210,45 235,140 150,170 55,150"/>
                <polygon class="d-area" data-d="Киевский"      style="--dc:var(--lime)"    fill="url(#dg-kie)" points="210,45 350,40 360,140 235,140"/>
                <polygon class="d-area" data-d="Калининский"   style="--dc:var(--cyan)"    fill="url(#dg-kal)" points="350,40 560,60 585,170 430,180 360,140"/>
                <polygon class="d-area" data-d="Кировский"     style="--dc:var(--yellow)"  fill="url(#dg-kir)" points="55,150 150,170 160,260 60,270"/>
                <polygon class="d-area" data-d="Ворошиловский" style="--dc:var(--magenta)" fill="url(#dg-vor)" points="150,170 235,140 360,140 370,230 250,260 160,260"/>
                <polygon class="d-area" data-d="Будённовский"  style="--dc:var(--orange)"  fill="url(#dg-bud)" points="360,140 430,180 585,170 570,260 430,270 370,230"/>
                <polygon class="d-area" data-d="Петровский"    style="--dc:var(--violet)"  fill="url(#dg-pet)" points="60,270 160,260 190,350 120,400 50,370"/>
                <polygon class="d-area" data-d="Ленинский"     style="--dc:var(--rose)"    fill="url(#dg-len)" points="160,260 250,260 370,230 380,320 300,395 190,350"/>
                <polygon class="d-area" data-d="Пролетарский"  style="--dc:var(--emerald)" fill="url(#dg-pro)" points="370,230 430,270 570,260 560,360 430,400 380,320"/>

                <g class="d-streets" pointer-events="none">
                    <path d="M402 -10 C 392 80, 424 160, 408 240 C 398 320, 428 380, 418 430" stroke="#22d3ee" stroke-width="9" opacity=".14"/>
                    <path d="M402 -10 C 392 80, 424 160, 408 240 C 398 320, 428 380, 418 430" stroke="#22d3ee" stroke-width="1.6" opacity=".5"/>
                    <g stroke="#9ec7ff" stroke-width="1" opacity=".28">
                        <path d="M-10 60 L650 46"/><path d="M-10 150 L650 128"/><path d="M-10 262 L650 240"/>
                        <path d="M-10 356 L650 336"/><path d="M238 -10 L252 430"/><path d="M486 -10 L474 430"/>
                    </g>
                    <path d="M96 372 C 140 180, 320 70, 470 96 C 580 116, 610 240, 586 352" stroke="#8b5cf6" stroke-width="1.2" stroke-dasharray="3 7" opacity=".45"/>
                    <path class="route" d="M60 330 C 180 260, 260 300, 330 210 C 400 130, 470 150, 560 110" stroke="url(#drg)" stroke-width="2"/>
                    <circle class="pt"  cx="262" cy="214" r="3"   fill="#f050e0"/>
                    <circle class="pt2" cx="470" cy="126" r="3"   fill="#22d3ee"/>
                    <circle class="pt3" cx="118" cy="300" r="2.5" fill="#a3e635"/>
                </g>

                <g class="d-labels" pointer-events="none">
                    <text class="d-lbl" x="148" y="100">КУЙБЫШЕВСКИЙ<tspan class="d-lbl-n" x="148" dy="14">{{ $districts->firstWhere('name','Куйбышевский')?->stats['places'] ?? 97 }} мест</tspan></text>
                    <text class="d-lbl" x="288" y="88">КИЕВСКИЙ<tspan class="d-lbl-n" x="288" dy="14">{{ $districts->firstWhere('name','Киевский')?->stats['places'] ?? 126 }} мест</tspan></text>
                    <text class="d-lbl" x="468" y="108">КАЛИНИНСКИЙ<tspan class="d-lbl-n" x="468" dy="14">{{ $districts->firstWhere('name','Калининский')?->stats['places'] ?? 184 }} места</tspan></text>
                    <text class="d-lbl" x="108" y="208">КИРОВСКИЙ<tspan class="d-lbl-n" x="108" dy="14">{{ $districts->firstWhere('name','Кировский')?->stats['places'] ?? 112 }} мест</tspan></text>
                    <text class="d-lbl" x="262" y="196">ВОРОШИЛОВСКИЙ<tspan class="d-lbl-n" x="262" dy="14">{{ $districts->firstWhere('name','Ворошиловский')?->stats['places'] ?? 248 }} мест</tspan></text>
                    <text class="d-lbl" x="472" y="214">БУДЁННОВСКИЙ<tspan class="d-lbl-n" x="472" dy="14">{{ $districts->firstWhere('name','Будённовский')?->stats['places'] ?? 83 }} места</tspan></text>
                    <text class="d-lbl" x="118" y="324">ПЕТРОВСКИЙ<tspan class="d-lbl-n" x="118" dy="14">{{ $districts->firstWhere('name','Петровский')?->stats['places'] ?? 64 }} места</tspan></text>
                    <text class="d-lbl" x="278" y="304">ЛЕНИНСКИЙ<tspan class="d-lbl-n" x="278" dy="14">{{ $districts->firstWhere('name','Ленинский')?->stats['places'] ?? 145 }} мест</tspan></text>
                    <text class="d-lbl" x="470" y="308">ПРОЛЕТАРСКИЙ<tspan class="d-lbl-n" x="470" dy="14">{{ $districts->firstWhere('name','Пролетарский')?->stats['places'] ?? 91 }} место</tspan></text>
                </g>
            </svg>
            <div class="d-tip" id="dTip"></div>
        </div>

        <div class="dist-grid" id="distGrid"></div>
    </div>
</section>
