/* ============ ВИЗИТ ДОНЕЦК · landing.js ============ */
import * as maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

const $ = s => document.querySelector(s), $$ = s => [...document.querySelectorAll(s)];

/* данные из Blade + безопасный фолбэк */
const DATA = window.LANDING ?? {places: [], districts: []};

/* ---------- API helper ---------- */
async function postJSON(url, body) {
    try {
        const r = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', Accept: 'application/json'},
            body: JSON.stringify(body),
        });
        const data = await r.json().catch(() => ({}));
        if (!r.ok) {
            const first = data.errors ? Object.values(data.errors)[0][0] : (data.message ?? 'Ошибка сервера');
            return {ok: false, error: first};
        }
        return {ok: true, data};
    } catch (e) {
        return {ok: false, error: 'Сеть недоступна, попробуйте ещё раз'};
    }
}

/* ---------- toast ---------- */
let toastT;

function toast(m) {
    const t = $('#toast');
    t.textContent = m;
    t.classList.add('show');
    clearTimeout(toastT);
    toastT = setTimeout(() => t.classList.remove('show'), 2600);
}

/* ---------- reveal on scroll ---------- */
const io = new IntersectionObserver(es => es.forEach(e => {
    if (e.isIntersecting) {
        e.target.classList.add('on');
        io.unobserve(e.target);
    }
}), {threshold: .12});
$$('.reveal').forEach(el => io.observe(el));

/* ---------- header + parallax ---------- */
addEventListener('scroll', () => {
    $('#hdr').classList.toggle('scrolled', scrollY > 10);
    const a = $('#heroImg');
    if (a && innerWidth > 860) a.style.transform = `translateY(${scrollY * 0.08}px)`;
}, {passive: true});

/* ---------- carousel ---------- */
$('#railPrev').onclick = () => $('#rail').scrollBy({left: -340, behavior: 'smooth'});
$('#railNext').onclick = () => $('#rail').scrollBy({left: 340, behavior: 'smooth'});

/* ---------- demo links ---------- */
document.addEventListener('click', e => {
    const d = e.target.closest('.demo');
    if (d) {
        e.preventDefault();
        toast('Раздел в разработке — демо-версия гида ✨');
    }
});

/* ============ ИНТЕРАКТИВНАЯ КАРТА ============ */
const CATS = {
    food: {l: 'Еда', c: '#ff8a3c'},
    coffee: {l: 'Кофе', c: '#a3e635'},
    bars: {l: 'Бары', c: '#f050e0'},
    beauty: {l: 'Красота', c: '#8b5cf6'},
    sport: {l: 'Спорт', c: '#22d3ee'},
    auto: {l: 'Авто', c: '#4f7dff'},
    leisure: {l: 'Отдых', c: '#ff5c7a'},
    med: {l: 'Медицина', c: '#34d399'},
    shop: {l: 'Покупки', c: '#facc15'},
};

/* пины — только реальные заведения из БД (xy считает Place::mapXY()) */
const PLACES = (DATA.places ?? [])
    .filter(p => p.xy)
    .map(p => ({...p, x: p.xy.x, y: p.xy.y, img: p.cover || 'assets/restaurant.jpg'}));

const world = $('#mapWorld'), card = $('#mapCard');
let filter = 'all', district = 'all', searchSet = null;
let mode = 'scheme', realMap = null;

const MAPTILER_KEY = window.MAPTILER_KEY ?? null;
const MAPTILER_STYLE = 'openstreetmap';

PLACES.forEach(p => {
    const b = document.createElement('button');
    b.className = 'pin';
    b.style.left = p.x + '%';
    b.style.top = p.y + '%';
    b.style.setProperty('--pc', p.color || CATS[p.cat]?.c || '#22d3ee');
    b.innerHTML = '<span class="pin-dot"></span>';
    b.title = p.name;
    b.onclick = e => {
        e.stopPropagation();
        openCard(p);
    };
    world.appendChild(b);
    p.el = b;
});

function applyState() {
    PLACES.forEach(p => {
        const byFilter = filter === 'all' || p.cat === filter;
        const bySearch = !searchSet || searchSet.has(p.id);
        const dim = !bySearch || (district !== 'all' && p.d !== district);

        // схема
        p.el.style.display = byFilter ? '' : 'none';
        p.el.classList.toggle('dim', dim);

        // реальная карта
        if (p.marker) {
            const el = p.marker.getElement();
            el.style.display = byFilter ? '' : 'none';
            el.classList.toggle('dim', dim);
        }
    });
}

function openCard(p) {
    $('#mcImg').src = p.img;
    $('#mcName').textContent = p.name;
    $('#mcCat').textContent = (p.label || CATS[p.cat]?.l || '') + ' · ' + p.d;
    $('#mcCat').style.color = p.color || CATS[p.cat]?.c || '#22d3ee';
    $('#mcRate').textContent = '★ ' + Number(p.r || 0).toFixed(1);
    $('#mcDesc').textContent = p.desc || '';
    const open = card.querySelector('.mc-open');
    if (open) open.href = '/place/' + p.slug;

    if (mode === 'real') {
        card.classList.add('fixed');
    } else {
        card.classList.remove('fixed');
        card.classList.toggle('fx', p.x > 58);
        card.classList.toggle('fy', p.y > 55);
        card.style.left = p.x + '%';
        card.style.top = p.y + '%';
    }
    card.hidden = false;
}

function closeCard() {
    card.hidden = true;
}

$('#mcClose').onclick = closeCard;
$('#mapView').addEventListener('click', e => {
    if (!e.target.closest('.pin,.map-card,.rpin')) closeCard();
});

/* ---------- сброс поиска при смене фильтров ---------- */
function clearSearch() {
    searchSet = null;
    const si = $('#searchInput');
    if (si) si.value = '';
    const qi = $('#quickForm input');
    if (qi) qi.value = '';
}

function setFilter(f) {
    filter = f;
    clearSearch();
    $$('#mapFilters .chip[data-f]').forEach(c => c.classList.toggle('on', c.dataset.f === f));
    applyState();
    closeCard();
}

$$('#mapFilters .chip[data-f]').forEach(ch => ch.onclick = () => setFilter(ch.dataset.f));

/* ---------- реальная карта (MapLibre + MapTiler/OSM) ---------- */
$('#modeScheme').onclick = () => setMode('scheme');
$('#modeReal').onclick = () => setMode('real');

function setMode(m) {
    mode = m;
    $('#modeScheme').classList.toggle('on', m === 'scheme');
    $('#modeReal').classList.toggle('on', m === 'real');
    world.style.display = m === 'scheme' ? '' : 'none';
    const real = $('#mapReal');

    if (m === 'real') {
        real.hidden = false;
        if (!realMap) initRealMap();
        else realMap.resize();
    } else {
        real.hidden = true;
    }
    closeCard();
}

function initRealMap() {
    console.log('[MAP] initRealMap called');
    const container = document.getElementById('mapReal');
    console.log('[MAP] container:', container);
    console.log('[MAP] dimensions:', container.offsetWidth, 'x', container.offsetHeight);

   /* const style = MAPTILER_KEY && MAPTILER_STYLE
        ? `https://api.maptiler.com/maps/${MAPTILER_STYLE}/style.json?key=${MAPTILER_KEY}`
        : {
            version: 8,
            sources: {
                osm: {
                    type: 'raster',
                    tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                    tileSize: 256,
                    attribution: '© OpenStreetMap contributors',
                },
            },
            layers: [
                {id: 'bg', type: 'background', paint: {'background-color': '#0b101d'}},  // ← добавили
                {id: 'osm', type: 'raster', source: 'osm'},
            ],
        };*/

    const style = {
        version: 8,
        sources: {
            carto: {
                type: 'raster',
                tiles: ['https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'],
                tileSize: 256,
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OSM</a> © <a href="https://carto.com/attributions">CARTO</a>',
            },
        },
        layers: [
            { id: 'carto', type: 'raster', source: 'carto' },
        ],
    };

/*
    const style = {
        version: 8,
        sources: {
            osm: {
                type: 'raster',
                tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                tileSize: 256,
                attribution: '© OpenStreetMap contributors',
                // важно для OSM: не использовать при большом трафике
                maxzoom: 19,
            },
        },
        layers: [
            { id: 'osm', type: 'raster', source: 'osm' },
        ],
    };
*/


/*
    const style = {
        version: 8,
        sources: {
            osm: {
                type: 'raster',
                tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                tileSize: 256,
                attribution: '© OpenStreetMap contributors',
                // важно для OSM: не использовать при большом трафике
                maxzoom: 19,
            },
        },
        layers: [
            { id: 'osm', type: 'raster', source: 'osm' },
        ],
    };*/



    console.log('[MAP] style:', MAPTILER_KEY ? 'MapTiler' : 'OSM fallback');

    try {
        realMap = new maplibregl.Map({
            container: 'mapReal',
            style,
            center: [37.805, 48.008],
            zoom: 12,
            attributionControl: {compact: true},
        });

        console.log('[MAP] map instance created:', realMap);

        if (!MAPTILER_KEY) $('#mapReal').classList.add('osm');

        realMap.addControl(new maplibregl.NavigationControl(), 'bottom-right');
        realMap.on('click', e => {
            if (!e.originalEvent.target.closest('.rpin')) closeCard();
        });

        realMap.on('load', () => {
            loadDistrictBoundaries();
            console.log('[MAP] style loaded, layers:', realMap.getStyle().layers.length);
        });
        realMap.on('error', (e) => console.error('[MAP] error:', e.error?.message || e));
        realMap.on('sourcedata', (e) => {
            if (e.isSourceLoaded) console.log('[MAP] source loaded:', e.sourceId);
        });


        PLACES.filter(p => p.lat && p.lng).forEach(p => {
            const el = document.createElement('button');
            el.className = 'rpin';
            el.style.setProperty('--pc', p.color || CATS[p.cat]?.c || '#22d3ee');
            el.title = p.name;
            el.onclick = e => {
                e.stopPropagation();
                openCard(p);
            };

            p.marker = new maplibregl.Marker({element: el})
                .setLngLat([p.lng, p.lat])
                .addTo(realMap);
        });

        console.log('[MAP] markers added:', PLACES.filter(p => p.lat && p.lng).length);

    } catch (err) {
        console.error('[MAP] Error initializing map:', err);
    }
}

function loadDistrictBoundaries() {
    fetch('/data/districts.geojson')
        .then(r => r.json())
        .then(geojson => addDistrictLayers(geojson))
        .catch(err => console.error('[MAP] GeoJSON load error:', err));
}

function addDistrictLayers(geojson) {
    console.log("Test1")
    if (realMap.getSource('districts')) {
        // уже добавлено — выходим
        return;
    }
    console.log("Test2")
    realMap.addSource('districts', { type: 'geojson', data: geojson });

    // Полупрозрачная заливка районов
    realMap.addLayer({
        id: 'districts-fill',
        type: 'fill',
        source: 'districts',
        paint: {
            'fill-color': '#22d3ee',
            'fill-opacity': 0.10,
        },
    });

    // Неоновые границы
    realMap.addLayer({
        id: 'districts-outline',
        type: 'line',
        source: 'districts',
        paint: {
            'line-color': '#22d3ee',
            'line-width': 2,
            'line-opacity': 0.85,
        },
    });



    // Подписи через DOM — центроид каждого полигона
    geojson.features.forEach(f => {
        const coords = f.geometry.coordinates[0];
        const centroid = coords.reduce(
            (acc, [lng, lat]) => ({ lng: acc.lng + lng, lat: acc.lat + lat }),
            { lng: 0, lat: 0 }
        );
        centroid.lng /= coords.length;
        centroid.lat /= coords.length;

        const label = document.createElement('div');
        label.className = 'district-label';
        label.textContent = f.properties.name;

        new maplibregl.Marker({ element: label, anchor: 'center' })
            .setLngLat([centroid.lng, centroid.lat])
            .addTo(realMap);
    });

    // Hover-эффект
    realMap.on('mouseenter', 'districts-fill', () => {
        realMap.getCanvas().style.cursor = 'pointer';
        realMap.setPaintProperty('districts-fill', 'fill-opacity', 0.25);
        realMap.setPaintProperty('districts-outline', 'line-width', 3);
    });
    realMap.on('mouseleave', 'districts-fill', () => {
        realMap.getCanvas().style.cursor = '';
        realMap.setPaintProperty('districts-fill', 'fill-opacity', 0.10);
        realMap.setPaintProperty('districts-outline', 'line-width', 2);
    });

    // Клик по району = фильтр + скролл
    realMap.on('click', 'districts-fill', (e) => {
        if (e.features.length) {
            const name = e.features[0].properties.name;
            goDistrict(name);
        }
    });
}

$('#districtSel').onchange = e => {
    district = e.target.value;
    clearSearch();
    applyState();
};
applyState();

/* ---------- поиск: подсветка совпадений на карте ---------- */
function runSearch(q) {
    q = (q || '').trim().toLowerCase();
    if (!q) {
        searchSet = null;
        applyState();
        toast('Введите запрос — например, «кофейня»');
        return;
    }
    searchSet = new Set(PLACES.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.label ?? '').toLowerCase().includes(q) ||
        (CATS[p.cat]?.l ?? '').toLowerCase().includes(q) ||
        (p.d ?? '').toLowerCase().includes(q)
    ).map(p => p.id));
    applyState();
    toast(searchSet.size
        ? `Нашли ${searchSet.size} мест — подсвечены на карте`
        : `По запросу «${q}» пока пусто — посмотрите карту`);
    $('#map').scrollIntoView({behavior: 'smooth'});
}

$('#searchForm').addEventListener('submit', e => {
    e.preventDefault();
    runSearch($('#searchInput').value);
});
$('#quickForm').addEventListener('submit', e => {
    e.preventDefault();
    runSearch(e.target.querySelector('input').value);
});
$('#navSearch').onclick = () => {
    location.hash = '#top';
    setTimeout(() => $('#searchInput').focus({preventScroll: true}), 600);
};
$('#mSearch').onclick = () => setTimeout(() => $('#searchInput').focus({preventScroll: true}), 600);

/* ---------- быстрые сценарии ---------- */
function jumpCat(f, msg) {
    setFilter(f);
    $('#map').scrollIntoView({behavior: 'smooth'});
    toast(msg || ('Показываем на карте: ' + (CATS[f]?.l ?? f)));
}

$$('.hero .quick .chip').forEach(ch => ch.onclick = () => jumpCat(ch.dataset.cat));
$$('#quick .chip[data-sc]').forEach(ch => ch.onclick = () => {
    const s = ch.dataset.sc;
    if (s === 'near') {
        $('#nearby').scrollIntoView({behavior: 'smooth'});
        if ($('#nearGrid').hidden) $('#geoBtn').click();
        return;
    }
    jumpCat(s);
});

/* ---------- карточки настроения ---------- */
$$('#moods .m-card').forEach(card => {
    card.style.cursor = 'pointer';
    card.onclick = () => {
        const mood = card.dataset.mood;
        const msg = card.dataset.msg;
        if (!mood) return;
        jumpCat(mood, msg);
    };
});

/* ============ РАЙОНЫ ============ */
const D_META = {
    'Ворошиловский': {c: 'var(--magenta)', t: ['🍽 Рестораны', '☕ Кофейни', '🌙 Ночная жизнь'], lx: 262, ly: 200},
    'Киевский': {c: 'var(--lime)', t: ['🍽 Рестораны', '🛍 Магазины', '☕ Кофейни'], lx: 288, ly: 92},
    'Калининский': {c: 'var(--cyan)', t: ['🍽 Рестораны', '🏋 Спорт', '🛍 Магазины'], lx: 468, ly: 112},
    'Куйбышевский': {c: 'var(--blue)', t: ['🚗 Авто', '🍽 Рестораны', '🛍 Магазины'], lx: 148, ly: 105},
    'Будённовский': {c: 'var(--orange)', t: ['🍽 Рестораны', '🚗 Авто', '💆 Красота'], lx: 472, ly: 218},
    'Ленинский': {c: 'var(--rose)', t: ['🍽 Рестораны', '🛍 Магазины', '🚗 Авто'], lx: 278, ly: 308},
    'Кировский': {c: 'var(--yellow)', t: ['🍽 Рестораны', '🚗 Авто', '☕ Кофейни'], lx: 108, ly: 212},
    'Петровский': {c: 'var(--violet)', t: ['🍽 Рестораны', '🚗 Авто', '🛍 Магазины'], lx: 118, ly: 328},
    'Пролетарский': {c: 'var(--emerald)', t: ['🍽 Рестораны', '🚗 Авто', '💆 Красота'], lx: 470, ly: 312},
};
const DISTRICTS = {};
(DATA.districts ?? []).forEach(d => {
    const m = D_META[d.name] ?? {c: 'var(--cyan)', t: [], lx: 320, ly: 210};
    DISTRICTS[d.name] = {
        n: d.stats?.places ?? 0,
        cats: d.stats?.cats ?? [],
        img: d.image || ('assets/districts/' + d.slug + '.jpg'),
        ...m,
    };
});

const dGrid = $('#distGrid'), dTip = $('#dTip');
Object.entries(DISTRICTS).forEach(([name, d], i) => {
    const el = document.createElement('article');
    el.className = 'd-card reveal' + (i < 2 ? ' w2' : '');
    el.style.setProperty('--dc', d.c);
    el.dataset.d = name;
    el.innerHTML = `<div class="d-bg" style="background-image:url('${d.img}')"></div>
    <h3>${name}</h3><div class="dc">${d.n} <small>мест</small></div>
    <div class="b-tags">${d.t.map(t => `<span>${t}</span>`).join('')}</div>
    <div class="b-link">Исследовать район <i>→</i></div>`;
    el.onclick = () => goDistrict(name);
    el.onmouseenter = () => {
        const p = $(`.d-area[data-d="${name}"]`);
        p && p.classList.add('hot');
    };
    el.onmouseleave = () => {
        const p = $(`.d-area[data-d="${name}"]`);
        p && p.classList.remove('hot');
    };
    dGrid.appendChild(el);
    io.observe(el);
});

function showTip(name) {
    const d = DISTRICTS[name];
    if (!d) return;
    dTip.innerHTML = `<img class="d-tip-img" src="${d.img}" alt="">
    <h4>${name} район</h4><div class="dn">${d.n} мест</div>
    <ul>${d.cats.map(c => `<li>${c}</li>`).join('')}</ul>`;
    dTip.style.left = (d.lx / 640 * 100) + '%';
    dTip.style.top = (d.ly / 420 * 100) + '%';
    dTip.classList.add('show');
}

$$('.d-area').forEach(a => {
    a.addEventListener('mouseenter', () => showTip(a.dataset.d));
    a.addEventListener('mouseleave', () => dTip.classList.remove('show'));
    a.addEventListener('click', () => goDistrict(a.dataset.d));
});

function goDistrict(name) {
    $('#districtSel').value = name;
    district = name;
    applyState();
    $('#map').scrollIntoView({behavior: 'smooth'});
    toast(name + ' район: показываем места на карте');
}

/* ============ МОДАЛКИ ============ */
function openModal(id) {
    $('#' + id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(m) {
    m.classList.remove('open');
    if (!$$('.modal.open').length && !$('#viewer').classList.contains('open')) document.body.style.overflow = '';
}

$$('[data-open]').forEach(b => b.onclick = () => openModal(b.dataset.open));
$$('.modal').forEach(m => {
    m.addEventListener('click', e => {
        if (e.target === m) closeModal(m);
    });
    m.querySelectorAll('[data-close]').forEach(b => b.onclick = () => closeModal(m));
});
addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        $$('.modal.open').forEach(closeModal);
        closeViewer();
    }
});

/* ---------- заявка «Добавить предприятие»: 4 шага + POST ---------- */
let step = 0;
const panes = $$('#addModal .m-pane'), steps = $$('#addModal .m-step');
const stepNames = ['Основная информация', 'Контактное лицо', 'Медиа', 'Подтверждение'];

function goStep(n) {
    step = n;
    panes.forEach((p, i) => p.classList.toggle('on', i === n));
    steps.forEach((s, i) => s.classList.toggle('on', i <= n));
    $('#stepLbl').textContent = `Шаг ${n + 1} из 4`;
    $('#stepName').textContent = stepNames[n];
    $('#mBack').hidden = n === 0;
    $('#mNext').textContent = n === 3 ? 'Отправить заявку' : 'Далее →';
}

$('#mBack').onclick = () => goStep(step - 1);

function applicationPayload() {
    const p0 = $('#addModal .m-pane[data-pane="0"]');
    const p1 = $('#addModal .m-pane[data-pane="1"]');
    const selects = p0.querySelectorAll('select');
    return {
        org_name: $('#orgName').value.trim(),
        category: $('#orgCat').value,
        district: selects[1]?.value ?? null,
        address: p0.querySelector('input[placeholder="Улица, дом"]')?.value ?? null,
        phone: p0.querySelector('input[type="tel"]')?.value ?? null,
        email: p0.querySelector('input[type="email"]')?.value ?? null,
        site: p0.querySelector('input[placeholder="https://…"]')?.value ?? null,
        description: p0.querySelector('textarea')?.value ?? null,
        contact_name: p1.querySelector('input[placeholder="Как к вам обращаться"]')?.value.trim(),
        contact_position: p1.querySelectorAll('input')[1]?.value ?? null,
        contact_phone: p1.querySelector('input[type="tel"]')?.value,
        contact_email: p1.querySelector('input[type="email"]')?.value ?? null,
        media: $$('#fileChips span').map(s => s.textContent.replace('🖼 ', '')),
        socials: {handle: p0.nextElementSibling ? null : null, ...collectSocials()},
    };
}

function collectSocials() {
    const p2 = $('#addModal .m-pane[data-pane="2"]');
    const handle = p2.querySelectorAll('input')[0]?.value ?? '';
    const site = p2.querySelectorAll('input')[1]?.value ?? '';
    return {handle, site};
}

$('#mNext').onclick = async () => {
    if (step === 0 && (!$('#orgName').value.trim() || !$('#orgCat').value)) {
        toast('Заполните название и категорию');
        return;
    }
    if (step === 1 && !$('#personName').value.trim()) {
        toast('Укажите имя контактного лица');
        return;
    }
    if (step === 3) {
        if ($$('#addModal .req-chk').filter(c => !c.checked).length) {
            toast('Подтвердите все условия размещения');
            return;
        }

        const btn = $('#mNext');
        btn.disabled = true;
        btn.textContent = 'Отправляем…';
        const res = await postJSON('/api/v1/applications', applicationPayload());
        btn.disabled = false;

        if (!res.ok) {
            toast(res.error);
            goStep(0);
            return;
        }
        $('#addFlow').hidden = true;
        $('#addSuccess').hidden = false;
        return;
    }
    goStep(step + 1);
};

/* ---------- drag-and-drop ---------- */
const dz = $('#dropZone'), fi = $('#fileInput'), fc = $('#fileChips');
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
    if (list.length) toast('Файлы добавлены: ' + list.length);
}

/* ---------- lead-модалка презентации + POST ---------- */
const INTEREST = {
    'Размещение в путеводителе': 'placement',
    'Партнерство': 'partnership',
    'Реклама': 'ads',
    'Добавление предприятия': 'add_place',
    'Другое': 'other',
};
$('#leadSubmit').onclick = async () => {
    const chks = $$('#leadModal .req-chk');
    if (chks.filter(c => !c.checked).length) {
        toast('Отметьте согласие на обработку данных и политику');
        return;
    }

    const btn = $('#leadSubmit');
    btn.disabled = true;
    btn.textContent = 'Отправляем…';
    const res = await postJSON('/api/v1/leads', {
        name: $('#leadFlow input[placeholder="Ваше имя"]').value.trim(),
        company: $('#leadFlow input[placeholder="Название компании"]').value.trim() || null,
        position: $('#leadFlow input[placeholder="Ваша должность"]').value.trim() || null,
        phone: $('#leadFlow input[type="tel"]').value,
        email: $('#leadFlow input[type="email"]').value,
        interest: INTEREST[$('#leadFlow select').value] ?? 'other',
        consent_data: chks[0].checked,
        consent_policy: chks[1].checked,
        consent_news: $$('#leadModal .chk input')[2]?.checked ?? false,
    });
    btn.disabled = false;

    if (!res.ok) {
        toast(res.error);
        return;
    }
    $('#leadFlow').hidden = true;
    $('#leadSuccess').hidden = false;
    downloadPDF();
};
$('#dlOnce').onclick = downloadPDF;
$('#dlAgain').onclick = downloadPDF;

/* ---------- генератор демо-PDF ---------- */
function downloadPDF() {
    const L = ["BT /F1 26 Tf 60 780 Td (VIZIT DONETSK) Tj ET", "BT /F1 12 Tf 60 750 Td (Glavnyy putevoditel po gorodu - demo preview) Tj ET", "BT /F1 10 Tf 60 715 Td (Partnery proekta: MOY BIZNES DPR) Tj ET", "BT /F1 10 Tf 60 699 Td (Ministerstvo ekonomicheskogo razvitiya DNR) Tj ET"].join("\n");
    const objs = ["<</Type/Catalog/Pages 2 0 R>>", "<</Type/Pages/Kids[3 0 R]/Count 1>>", "<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Resources<</Font<</F1 4 0 R>>>>/Contents 5 0 R>>", "<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>", `<</Length ${L.length}>>\nstream\n${L}\nendstream`];
    let pdf = "%PDF-1.4\n";
    const off = [];
    objs.forEach((o, i) => {
        off.push(pdf.length);
        pdf += `${i + 1} 0 obj\n${o}\nendobj\n`;
    });
    const x = pdf.length;
    pdf += `xref\n0 ${objs.length + 1}\n0000000000 65535 f \n` + off.map(o => String(o).padStart(10, '0') + ' 00000 n \n').join('');
    pdf += `trailer\n<</Size ${objs.length + 1}/Root 1 0 R>>\nstartxref\n${x}\n%%EOF`;
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([pdf], {type: 'application/pdf'}));
    a.download = 'VIZIT_DONETSK_presentation.pdf';
    a.click();
    toast('⬇ Скачивание PDF началось');
}

$('#magDownload').onclick = downloadPDF;

/* ============ PDF VIEWER ============ */
const pages = $$('#vStage .vpage'), vInd = $('#vInd'), thumbsRail = $('#vThumbsRail');
let cur = 0, zoom = 1;
pages.forEach((p, i) => {
    const t = document.createElement('button');
    t.className = 'v-thumb';
    t.textContent = (i + 1) + ' · ' + p.dataset.t;
    t.onclick = () => goPage(i);
    thumbsRail.appendChild(t);
});

function goPage(i, dir) {
    if (i < 0 || i >= pages.length) return;
    cur = i;
    zoom = 1;
    $('#vStage').style.setProperty('--zoom', 1);
    pages.forEach((p, k) => {
        p.classList.toggle('cur', k === cur);
        p.classList.remove('anim-n', 'anim-p');
    });
    const c = pages[cur];
    void c.offsetWidth;
    c.classList.add(dir === 'p' ? 'anim-p' : 'anim-n');
    vInd.textContent = String(cur + 1).padStart(2, '0') + ' / ' + pages.length;
    $$('.v-thumb').forEach((t, k) => t.classList.toggle('on', k === cur));
}

$('#vPrev').onclick = () => goPage(cur - 1, 'p');
$('#vNext').onclick = () => goPage(cur + 1, 'n');
$('#vThumbs').onclick = () => thumbsRail.classList.toggle('hide');
$('#vZoomIn').onclick = () => {
    zoom = Math.min(1.6, zoom + .15);
    $('#vStage').style.setProperty('--zoom', zoom);
};
$('#vZoomOut').onclick = () => {
    zoom = Math.max(.7, zoom - .15);
    $('#vStage').style.setProperty('--zoom', zoom);
};
$('#vFull').onclick = () => {
    document.fullscreenElement ? document.exitFullscreen() : $('#viewer').requestFullscreen();
};
$('#vShare').onclick = async () => {
    try {
        await navigator.share({title: 'ВИЗИТ ДОНЕЦК — печатная версия', url: location.href});
    } catch (e) {
        toast('Ссылка скопирована: визит-донецк.рф');
    }
};
$('#vDownload').onclick = downloadPDF;
$('#vClose').onclick = closeViewer;

function closeViewer() {
    $('#viewer').classList.remove('open');
    if (!$$('.modal.open').length) document.body.style.overflow = '';
}

$('#openViewer').onclick = () => {
    $('#viewer').classList.add('open');
    document.body.style.overflow = 'hidden';
    goPage(0);
};

/* ---------- геолокация ---------- */
$('#geoBtn').onclick = function () {
    this.textContent = 'Определяем…';
    this.style.opacity = .7;
    if (navigator.geolocation) {
        try {
            navigator.geolocation.getCurrentPosition(() => {
            }, () => {
            }, {timeout: 1200});
        } catch (e) {
        }
    }
    setTimeout(() => {
        this.hidden = true;
        $('#nearGrid').hidden = false;
        toast('📍 Вы в центре Донецка — вот что рядом');
    }, 900);
};

/* ---------- подписка + POST ---------- */
$('#subForm').addEventListener('submit', async e => {
    e.preventDefault();
    const input = e.target.querySelector('input');
    const res = await postJSON('/api/v1/subscribe', {email: input.value});
    if (res.ok) {
        e.target.innerHTML = '<div class="sub-ok">✓ Готово! Вы подписаны на новости города.</div>';
    } else {
        toast(res.error);
    }
});

/* ---------- карточки категорий (секция "Город в одном месте") ---------- */
$$('.quick-card').forEach(card => {
    card.onclick = () => {
        const cat = card.dataset.category;
        if (!cat) return;

        // применяем фильтр
        filter = cat;
        applyState();

        // скроллим к карте
        $('#map').scrollIntoView({ behavior: 'smooth', block: 'start' });

        // показываем уведомление
        const catName = card.querySelector('h3')?.textContent || cat;
        toast(`Показаны заведения: ${catName}`);
    };
});

/* ---------- карточки категорий (секция «Город в одном месте») ---------- */
const CAT_MAP = {
    'cat-food':    'food',
    'cat-leisure': 'leisure',
    'cat-beauty':  'beauty',
    'cat-shop':    'shop',
    'cat-auto':    'auto',
};

// привязываем по id
Object.entries(CAT_MAP).forEach(([id, cat]) => {
    const card = document.getElementById(id);
    if (!card) return;
    card.onclick = () => jumpToCategory(cat, card.querySelector('h3')?.textContent);
});

// привязываем по data-cat (для карточек без id)
$$('.b-card[data-cat]').forEach(card => {
    card.onclick = () => jumpToCategory(card.dataset.cat, card.querySelector('h3')?.textContent);
});

function jumpToCategory(cat, label) {
    filter = cat;
    applyState();
    $('#map').scrollIntoView({ behavior: 'smooth', block: 'start' });
    toast(`Показаны заведения: ${label || cat}`);
}


/* ============ ИСТОРИИ ============ */
let storiesActual = [], storiesArchive = [], storyIndex = 0, storyQueue = [];
let storyTimer = null, storyProgressRaf = null;

const storiesModal  = $('#storiesModal');
const storyViewer   = $('#storyViewer');
const storyMediaEl  = $('#storyMedia');
const storyProgress = $('#storyProgress');

/* ---------- загрузка ---------- */
async function fetchStories(kind) {
    const url = kind === 'archive' ? '/api/v1/stories/archive' : '/api/v1/stories';
    try {
        const r = await fetch(url, { headers: { Accept: 'application/json' } });
        const d = await r.json();
        return d.data ?? [];
    } catch (e) {
        return [];
    }
}

function renderStoryGrid(list, container, isArchive) {
    container.innerHTML = '';
    const grid = document.createElement('div');
    grid.className = 'stories-grid';

    list.forEach((s, i) => {
        const card = document.createElement('div');
        card.className = 'story-card' + (isArchive ? ' story-card--archived' : '');
        card.innerHTML = `
            ${s.media_type === 'video'
            ? `<video src="${s.media_url}" muted preload="metadata"></video><span class="story-card__video-ico">▶</span>`
            : `<img src="${s.media_url}" alt="${s.title ?? ''}" loading="lazy">`}
            <div class="story-card__overlay">
                ${s.place ? `<div class="story-card__place">${s.place.name}</div>` : ''}
                ${s.title ? `<div class="story-card__title">${s.title}</div>` : ''}
            </div>`;
        card.onclick = () => openStoryViewer(list, i);
        grid.appendChild(card);
    });

    container.appendChild(grid);
}

async function loadStories() {
    $('#storiesLoading').hidden = false;
    $('#storiesEmpty').hidden = true;

    const [actual, archive] = await Promise.all([fetchStories('actual'), fetchStories('archive')]);
    storiesActual = actual;
    storiesArchive = archive;

    $('#storiesLoading').hidden = true;

    renderStoryGrid(actual, $('#storiesActual'), false);
    renderStoryGrid(archive, $('#storiesArchive'), true);

    if (!actual.length && !archive.length) $('#storiesEmpty').hidden = false;

    // бейдж на триггере
    const badge = $('#storiesBadge');
    if (actual.length) {
        badge.textContent = actual.length;
        badge.hidden = false;
    } else {
        badge.hidden = true;
    }
}

/* ---------- модалка ---------- */
$('#storiesTrigger').onclick = () => {
    storiesModal.classList.add('open');
    document.body.style.overflow = 'hidden';
    if (!storiesActual.length && !storiesArchive.length) loadStories();
};

$$('[data-close-stories]').forEach(el => el.onclick = closeStoriesModal);

function closeStoriesModal() {
    storiesModal.classList.remove('open');
    if (!storyViewer.classList.contains('open')) document.body.style.overflow = '';
}

/* вкладки */
$$('.stories-tab').forEach(tab => {
    tab.onclick = () => {
        $$('.stories-tab').forEach(t => t.classList.toggle('on', t === tab));
        const target = tab.dataset.stab;
        $('#storiesActual').hidden = target !== 'actual';
        $('#storiesArchive').hidden = target !== 'archive';
        $('#storiesEmpty').hidden = !((target === 'actual' && !storiesActual.length) || (target === 'archive' && !storiesArchive.length));
    };
});

/* ---------- просмотрщик ---------- */
function openStoryViewer(queue, index) {
    storyQueue = queue;
    storyIndex = index;
    storyViewer.classList.add('open');
    document.body.style.overflow = 'hidden';
    showStory();
}

function showStory() {
    const s = storyQueue[storyIndex];
    if (!s) return closeStoryViewer();

    // регистрируем просмотр
    fetch(`/api/v1/stories/${s.id}/view`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' } }).catch(() => {});

    // медиа
    storyMediaEl.innerHTML = '';
    let mediaEl;
    if (s.media_type === 'video') {
        mediaEl = document.createElement('video');
        mediaEl.src = s.media_url;
        mediaEl.controls = true;
        mediaEl.autoplay = true;
        mediaEl.muted = true;
        mediaEl.playsInline = true;
        mediaEl.onended = () => nextStory();
        mediaEl.ontimeupdate = () => updateProgress(mediaEl.currentTime / (mediaEl.duration || 1));
    } else {
        mediaEl = document.createElement('img');
        mediaEl.src = s.media_url;
        mediaEl.alt = s.title ?? '';
        startAutoProgress(7000); // фото — 7 сек
    }
    storyMediaEl.appendChild(mediaEl);

    // текст справа
    $('#storyPlace').textContent = s.place?.name ?? 'ВИЗИТ ДОНЕЦК';
    $('#storyTitle').textContent = s.title ?? '';
    $('#storyText').textContent = s.text ?? '';
    $('#storySide').style.display = (s.title || s.text) ? '' : 'none';
    // если текста нет — растягиваем медиа на всю ширину
    $('.story-viewer__body').style.gridTemplateColumns = (s.title || s.text) ? '' : '1fr';
}

function updateProgress(ratio) {
    storyProgress.style.width = Math.min(100, ratio * 100) + '%';
}

function startAutoProgress(duration) {
    cancelProgress();
    const start = performance.now();
    const tick = (now) => {
        const ratio = (now - start) / duration;
        updateProgress(ratio);
        if (ratio >= 1) { nextStory(); return; }
        storyProgressRaf = requestAnimationFrame(tick);
    };
    storyProgressRaf = requestAnimationFrame(tick);
}

function cancelProgress() {
    if (storyProgressRaf) { cancelAnimationFrame(storyProgressRaf); storyProgressRaf = null; }
    if (storyTimer) { clearTimeout(storyTimer); storyTimer = null; }
}

function nextStory() {
    cancelProgress();
    if (storyIndex < storyQueue.length - 1) { storyIndex++; showStory(); }
    else closeStoryViewer();
}
function prevStory() {
    cancelProgress();
    if (storyIndex > 0) { storyIndex--; showStory(); }
}

function closeStoryViewer() {
    cancelProgress();
    storyViewer.classList.remove('open');
    storyMediaEl.innerHTML = '';
    if (!storiesModal.classList.contains('open')) document.body.style.overflow = '';
}

/* навигация */
$$('.story-viewer__nav').forEach(btn => {
    btn.onclick = () => btn.dataset.nav === 'next' ? nextStory() : prevStory();
});
$$('[data-close-viewer]').forEach(el => el.onclick = closeStoryViewer);

/* клавиатура */
addEventListener('keydown', e => {
    if (!storyViewer.classList.contains('open')) return;
    if (e.key === 'ArrowRight') nextStory();
    if (e.key === 'ArrowLeft')  prevStory();
    if (e.key === 'Escape')     closeStoryViewer();
});

/* клик по краям медиа для навигации */
storyMediaEl.addEventListener('click', e => {
    const rect = storyMediaEl.getBoundingClientRect();
    const x = e.clientX - rect.left;
    if (x < rect.width / 3) prevStory();
    else if (x > rect.width * 2 / 3) nextStory();
});

/* ---------- предзагрузка историй при старте ---------- */
loadStories();

goPage(0);
