<div class="viewer" id="viewer" role="dialog" aria-label="Просмотр журнала">
    <div class="v-top">
        <div class="v-brand">ВИЗИТ ДОНЕЦК<em>ПЕЧАТНАЯ ВЕРСИЯ</em></div>
        <span class="v-ind" id="vInd">01 / 10</span>
        <div class="v-nav" style="display:flex;gap:8px">
            <button class="v-btn" id="vPrev" aria-label="Предыдущая">←</button>
            <button class="v-btn" id="vNext" aria-label="Следующая">→</button>
        </div>
        <button class="v-btn" id="vThumbs" title="Миниатюры">▦</button>
        <button class="v-btn" id="vZoomOut" title="Уменьшить">−</button>
        <button class="v-btn" id="vZoomIn" title="Увеличить">+</button>
        <button class="v-btn" id="vFull" title="На весь экран">⛶</button>
        <button class="v-btn" id="vShare" title="Поделиться">↗</button>
        <button class="v-btn" id="vDownload" title="Скачать">↓</button>
        <button class="v-btn" id="vClose" title="Закрыть">✕</button>
    </div>

    <div class="v-body">
        <div class="v-thumbs" id="vThumbsRail"></div>
        <div class="v-stage" id="vStage">

            <div class="vpage cur" data-t="Обложка">
                <div class="pg"><img class="bg" src="assets/hero-palm.jpg" alt=""><div class="ov"></div>
                    <div class="pg-k" style="margin-top:auto">Городской гид · №1 · {{ now()->year }}</div>
                    <div class="pg-h" style="font-size:34px">ВИЗИТ<br><span class="grad">ДОНЕЦК</span></div>
                    <p class="pg-p">Главный путеводитель по заведениям, услугам и отдыху города</p>
                    <div class="pg-num">01</div>
                </div>
            </div>

            <div class="vpage" data-t="Содержание">
                <div class="pg"><div class="pg-k">Содержание</div><div class="pg-h">В этом номере</div>
                    <ul class="pg-list">
                        <li><b>Пульс города</b><span>03</span></li>
                        <li><b>Еда: рестораны и гастробары</b><span>04</span></li>
                        <li><b>Кофейная карта</b><span>05</span></li>
                        <li><b>Вечер в городе</b><span>06</span></li>
                        <li><b>Красота и SPA</b><span>07</span></li>
                        <li><b>Районы: гид</b><span>08</span></li>
                        <li><b>Афиша</b><span>09</span></li>
                    </ul>
                    <div class="pg-num">02</div>
                </div>
            </div>

            <div class="vpage" data-t="Пульс города">
                <div class="pg"><img class="bg" src="assets/city-night.jpg" alt=""><div class="ov"></div>
                    <div class="pg-k" style="margin-top:auto">Раздел 01</div><div class="pg-h">Пульс города</div>
                    <p class="pg-p">Донецк вечером — это свет проспектов, террасы кофеен и неон баров. Мы собрали места, в которых город звучит громче всего.</p>
                    <div class="pg-num">03</div>
                </div>
            </div>

            <div class="vpage" data-t="Еда">
                <div class="pg"><div class="pg-k">Раздел 02</div><div class="pg-h">Еда</div>
                    <div class="pg-imgbox"><img src="assets/restaurant.jpg" alt=""></div>
                    <ul class="pg-list">
                        @foreach($popular->take(3) as $p)
                            <li><b>{{ $p->name }}</b><span>★ {{ number_format($p->rating, 1, '.', '') }}</span></li>
                        @endforeach
                    </ul>
                    <div class="pg-num">04</div>
                </div>
            </div>

            <div class="vpage" data-t="Кофейная карта">
                <div class="pg"><div class="pg-k">Раздел 03</div><div class="pg-h">Кофейная карта</div>
                    <div class="pg-imgbox"><img src="assets/coffee.jpg" alt=""></div>
                    <p class="pg-p">Спешелти, авторские обжарщики и завтраки до вечера: кофейни, с которых стоит начать утро в центре.</p>
                    <div class="pg-num">05</div>
                </div>
            </div>

            <div class="vpage" data-t="Вечер">
                <div class="pg"><img class="bg" src="assets/bar.jpg" alt=""><div class="ov"></div>
                    <div class="pg-k" style="margin-top:auto">Раздел 04</div><div class="pg-h">Вечер в городе</div>
                    <p class="pg-p">Коктейльные карты, винные бары и караоке до полуночи — гид по ночному Донецку.</p>
                    <div class="pg-num">06</div>
                </div>
            </div>

            <div class="vpage" data-t="Красота и SPA">
                <div class="pg"><div class="pg-k">Раздел 05</div><div class="pg-h">Красота и SPA</div>
                    <div class="pg-imgbox"><img src="assets/spa.jpg" alt=""></div>
                    <p class="pg-p">Массаж, барбершопы и студии йоги: перезагрузка без выезда из города.</p>
                    <div class="pg-num">07</div>
                </div>
            </div>

            <div class="vpage" data-t="Районы">
                <div class="pg"><div class="pg-k">Раздел 06</div><div class="pg-h">9 районов — 9 характеров</div>
                    <ul class="pg-list">
                        @foreach($districts->sortByDesc(fn ($d) => $d->stats['places'] ?? 0)->take(4) as $d)
                            <li><b>{{ $d->name }}</b><span>{{ $d->stats['places'] ?? 0 }} мест</span></li>
                        @endforeach
                    </ul>
                    <div class="pg-num">08</div>
                </div>
            </div>

            <div class="vpage" data-t="Афиша">
                <div class="pg"><div class="pg-k">Раздел 07</div><div class="pg-h">Афиша</div>
                    <ul class="pg-list">
                        @foreach($events->take(4) as $e)
                            <li><b>{{ $e->title }}</b><span>{{ $e->starts_at->format('d M') }}</span></li>
                        @endforeach
                    </ul>
                    <div class="pg-num">09</div>
                </div>
            </div>

            <div class="vpage" data-t="Финал">
                <div class="pg"><div class="pg-k">Финал</div><div class="pg-h">Увидимся<br>в городе</div>
                    <p class="pg-p">ВИЗИТ ДОНЕЦК — digital-гид, печатный путеводитель и платформа для бизнеса.</p>
                    <div class="pg-num">10</div>
                </div>
            </div>

        </div>
    </div>
</div>
