/* ==========================================================================
   ОЧИСТКА СТАРЫХ SERVICE WORKERS
   ========================================================================== */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(registrations => {
        registrations.forEach(registration => {
            // Удаляем все старые регистрации
            registration.unregister().then(() => {
                console.log('✅ Старый Service Worker удалён');
            });
        });
    });

    // Очищаем кэши Workbox, если они есть
    if ('caches' in window) {
        caches.keys().then(names => {
            names.forEach(name => {
                if (name.includes('workbox') || name.includes('precache')) {
                    caches.delete(name);
                    console.log(`✅ Кэш "${name}" удалён`);
                }
            });
        });
    }
}
/* ==========================================================================
   PWA INSTALLATION LOGIC
   ========================================================================== */
let deferredPrompt = null;
const installBtn = document.getElementById('pwaInstallBtn');

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;

    console.log('✅ PWA: Событие beforeinstallprompt перехвачено.');

    if (installBtn) {
        installBtn.style.display = 'inline-flex';
        console.log('✅ PWA: Кнопка установки отображена в хедере.');
    }
});

if (installBtn) {
    installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) {
            console.warn('⚠️ PWA: Промпт недоступен.');
            installBtn.style.display = 'none';
            return;
        }

        deferredPrompt.prompt();
        console.log('🚀 PWA: Вызов prompt() выполнен.');

        const { outcome } = await deferredPrompt.userChoice;
        console.log(`👤 PWA: Выбор пользователя: ${outcome}`);

        deferredPrompt = null;
        installBtn.style.display = 'none';
    });
}

window.addEventListener('appinstalled', () => {
    console.log('🎉 PWA: Приложение успешно установлено!');
    if (installBtn) installBtn.style.display = 'none';
    deferredPrompt = null;
});

/* ============ landing.js ============ */
import * as maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
/* ============ PWA UPDATE LOGIC ============ */
import { registerSW } from 'virtual:pwa-register';
import FormAutoSave from './form-autosave.js';
window.FormAutoSave = FormAutoSave;

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
    if (!t) return;
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
    const hdr = $('#hdr');
    if (hdr) hdr.classList.toggle('scrolled', scrollY > 10);
    const a = $('#heroImg');
    if (a && innerWidth > 860) a.style.transform = `translateY(${scrollY * 0.08}px)`;
}, {passive: true});

/* ---------- carousel ---------- */
const railPrev = $('#railPrev'), railNext = $('#railNext'), rail = $('#rail');
if (railPrev && rail) railPrev.onclick = () => rail.scrollBy({left: -340, behavior: 'smooth'});
if (railNext && rail) railNext.onclick = () => rail.scrollBy({left: 340, behavior: 'smooth'});

/* ---------- demo links ---------- */
document.addEventListener('click', e => {
    const d = e.target.closest('.demo');
    if (d) {
        e.preventDefault();
        toast('Раздел в разработке — демо-версия гида ✨');
    }
});

/* ========================================================================
   ИНТЕРАКТИВНАЯ КАРТА (НОВАЯ АРХИТЕКТУРА)
   ======================================================================== */
let mapLayersData = [];
let activeLayerSlugs = new Set(['all']);
let mapMarkers = [];
let mode = 'scheme';
let realMap = null;
let mapSearchQuery = ''; // <-- ДОБАВЛЕНО: текущий поисковый запрос

const world = $('#mapWorld');
const card = $('#mapCard');
const MAPTILER_KEY = window.MAPTILER_KEY ?? null;

/* ---------- СОСТОЯНИЕ ПОИСКА И ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ---------- */
let activeSearchIds = new Set(); // Хранит ID объектов, найденных при поиске

// Получаем плоский массив всех объектов со всех слоев для удобного поиска
function getAllMapObjects() {
    const all = [];
    mapLayersData.forEach(layer => {
        layer.objects.forEach(obj => {
            all.push({
                ...obj,
                layerColor: layer.color,
                layerIcon: layer.icon,
                layerName: layer.name
            });
        });
    });
    return all;
}

// Формула гаверсинуса для расчета расстояния в метрах
function getDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // радиус Земли в метрах
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function formatDistance(meters) {
    if (meters < 1000) return Math.round(meters) + ' м от вас';
    return (meters / 1000).toFixed(1).replace('.', ',') + ' км от вас';
}


// 1. Загрузка данных
async function loadMapData() {
    try {
        const res = await fetch('/api/v1/map-data'); // ИСПРАВЛЕНО: добавлен /v1/
        if (!res.ok) throw new Error('API error');
        const data = await res.json();
        mapLayersData = data.layers;
        renderLayerFilters();
        renderMapObjects();
    } catch (e) {
        console.error('Ошибка загрузки данных карты:', e);
    }
}

// 2. Рендер фильтров
function renderLayerFilters() {
    const container = $('#mapFilters');
    if (!container) return;

    let html = `<button class="chip ${activeLayerSlugs.has('all') ? 'on' : ''}" data-layer="all">Все</button>`;
    mapLayersData.forEach(layer => {
        html += `<button class="chip ${activeLayerSlugs.has(layer.slug) ? 'on' : ''}" data-layer="${layer.slug}" style="--ac:${layer.color}">
            ${layer.icon} ${layer.name}
        </button>`;
    });
    container.innerHTML = html;

    container.querySelectorAll('.chip').forEach(btn => {
        btn.onclick = () => {
            const layerSlug = btn.dataset.layer;
            if (layerSlug === 'all') {
                activeLayerSlugs.clear();
                activeLayerSlugs.add('all');
            } else {
                activeLayerSlugs.delete('all');
                if (activeLayerSlugs.has(layerSlug)) {
                    activeLayerSlugs.delete(layerSlug);
                } else {
                    activeLayerSlugs.add(layerSlug);
                }
            }
            container.querySelectorAll('.chip').forEach(b => {
                b.classList.toggle('on', activeLayerSlugs.has(b.dataset.layer));
            });
            renderMapObjects();
        };
    });
}

// 3. Рендер объектов на карте (и схема, и реальная)
function renderMapObjects() {
    if (world) {
        world.querySelectorAll('.pin, .cluster').forEach(el => el.remove());
    }

    // Очищаем старую реальную карту
    if (realMap) {
        mapMarkers.forEach(marker => marker.remove());
        mapMarkers = [];
    }

    const visibleObjects = [];

    // Собираем объекты для отображения
    mapLayersData.forEach(layer => {
        if (activeLayerSlugs.has('all') || activeLayerSlugs.has(layer.slug)) {
            layer.objects.forEach(obj => {
                // <-- НОВАЯ ЛОГИКА ФИЛЬТРАЦИИ ПО ТЕКСТУ -->
                const matchesSearch = !mapSearchQuery ||
                    obj.name.toLowerCase().includes(mapSearchQuery) ||
                    (obj.description || '').toLowerCase().includes(mapSearchQuery) ||
                    layer.name.toLowerCase().includes(mapSearchQuery);

                if (matchesSearch) {
                    visibleObjects.push({
                        ...obj,
                        layerColor: layer.color,
                        layerIcon: layer.icon
                    });
                }
            });
        }
    });

    // Рендерим только отфильтрованные объекты
    visibleObjects.forEach(obj => {
        // А) Схема (SVG)
        if (world && obj.x !== undefined && obj.y !== undefined) {
            const b = document.createElement('button');
            b.className = 'pin';
            b.style.left = obj.x + '%';
            b.style.top = obj.y + '%';
            b.style.setProperty('--pc', obj.layerColor);
            b.innerHTML = '<span class="pin-dot"></span>';
            b.title = obj.name;
            b.onclick = e => { e.stopPropagation(); openCard(obj); };
            world.appendChild(b);
        }

        // Б) Реальная карта (MapLibre)
        if (mode === 'real' && realMap && obj.lat && obj.lng) {
            const el = document.createElement('button');
            el.className = 'rpin';
            el.style.setProperty('--pc', obj.layerColor);
            el.title = obj.name;
            if (obj.place_id) el.classList.add('rpin--place');

            el.onclick = e => { e.stopPropagation(); openCard(obj); };

            const marker = new maplibregl.Marker({ element: el })
                .setLngLat([obj.lng, obj.lat])
                .addTo(realMap);

            mapMarkers.push(marker);
        }
    });
}

/* ---------- ПОИСК ПО КАРТЕ ---------- */
function applySearchState() {
    // Обновляем SVG пины
    $$('.pin').forEach(pin => {
        const id = parseInt(pin.dataset.id);
        const isMatch = activeSearchIds.has(id);

        if (activeSearchIds.size > 0) {
            pin.style.opacity = isMatch ? '1' : '0.2';
            pin.style.transform = isMatch ? 'scale(1.3)' : 'scale(1)';
            pin.style.zIndex = isMatch ? '10' : '1';
        } else {
            pin.style.opacity = '1';
            pin.style.transform = 'scale(1)';
            pin.style.zIndex = '1';
        }
    });

    // Обновляем маркеры MapLibre
    $$('.rpin').forEach(markerEl => {
        const id = parseInt(markerEl.dataset.id);
        const isMatch = activeSearchIds.has(id);

        if (activeSearchIds.size > 0) {
            markerEl.style.opacity = isMatch ? '1' : '0.2';
            markerEl.style.transform = isMatch ? 'scale(1.3)' : 'scale(1)';
        } else {
            markerEl.style.opacity = '1';
            markerEl.style.transform = 'scale(1)';
        }
    });
}

function runSearch(q) {
    q = (q || '').trim().toLowerCase();
    activeSearchIds.clear();

    if (!q) {
        applySearchState();
        toast('Введите запрос — например, «кофейня» или название места');
        return;
    }

    const allObjects = getAllMapObjects();
    const matches = allObjects.filter(obj =>
        obj.name.toLowerCase().includes(q) ||
        (obj.description || '').toLowerCase().includes(q) ||
        obj.layerName.toLowerCase().includes(q)
    );

    matches.forEach(obj => activeSearchIds.add(obj.id));
    applySearchState();

    if (matches.length > 0) {
        toast(`Найдено ${matches.length} мест — они подсвечены на карте`);
        $('#map')?.scrollIntoView({behavior: 'smooth'});
    } else {
        toast(`По запросу «${q}» ничего не найдено`);
    }
}

/* ---------- ГЕОЛОКАЦИЯ И БЛИЖАЙШИЕ МЕСТА ---------- */
function renderNearby(userLat, userLng) {
    const grid = $('#nearGrid');
    if (!grid) return;

    grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--mut)">Вычисляем расстояние...</p>';

    const allObjects = getAllMapObjects().filter(obj => obj.lat && obj.lng);

    const nearby = allObjects.map(obj => ({
        ...obj,
        distance: getDistance(userLat, userLng, obj.lat, obj.lng)
    }))
        .sort((a, b) => a.distance - b.distance)
        .slice(0, 6); // Берем топ-6 ближайших

    grid.innerHTML = '';

    if (!nearby.length) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--mut)">Рядом нет объектов с указанными координатами</p>';
        return;
    }

    nearby.forEach(obj => {
        const card = document.createElement('div');
        card.className = 'nr-card';
        card.innerHTML = `
            <span class="nr-dist">📍 ${formatDistance(obj.distance)}</span>
            <h4>${escapeHtml(obj.name)}</h4>
            <p>${obj.layerIcon} ${escapeHtml(obj.layerName)}</p>
            ${obj.slug ? `<a href="/place/${obj.slug}" class="nr-link">Подробнее →</a>` : ''}
        `;
        card.onclick = (e) => {
            if (!e.target.closest('.nr-link')) {
                openCard(obj);
                $('#map')?.scrollIntoView({ behavior: 'smooth' });
            }
        };
        grid.appendChild(card);
    });
}

// 4. Открытие карточки
function openCard(obj) {
    if (!card) return;
    const mcImg = $('#mcImg');
    if (mcImg) mcImg.src = obj.image || 'assets/restaurant.jpg';

    const mcName = $('#mcName');
    if (mcName) mcName.textContent = obj.name;

    const categoryText = obj.place_id ? (obj.category_label || 'Заведение') : (obj.layerIcon + ' Объект');
    const mcCat = $('#mcCat');
    if (mcCat) {
        mcCat.textContent = categoryText;
        mcCat.style.color = obj.layerColor;
    }

    const mcRate = $('#mcRate');
    if (mcRate) mcRate.textContent = obj.rating ? `★ ${Number(obj.rating).toFixed(1)}` : '';

    const mcDesc = $('#mcDesc');
    if (mcDesc) mcDesc.textContent = obj.description || '';

    const openBtn = card.querySelector('.mc-open');
    if (openBtn) {
        if (obj.slug) {
            openBtn.href = '/place/' + obj.slug;
            openBtn.style.display = 'inline-block';
        } else {
            openBtn.style.display = 'none';
        }
    }

    if (mode === 'real') {
        card.classList.add('fixed');
    } else {
        card.classList.remove('fixed');
        if (obj.x !== undefined && obj.y !== undefined) {
            card.classList.toggle('fx', obj.x > 58);
            card.classList.toggle('fy', obj.y > 55);
            card.style.left = obj.x + '%';
            card.style.top = obj.y + '%';
            card.style.transform = 'none';
        } else {
            card.style.left = '50%';
            card.style.top = '50%';
            card.style.transform = 'translate(-50%, -50%)';
        }
    }
    card.hidden = false;
}

function closeCard() {
    if (card) card.hidden = true;
}

if ($('#mcClose')) $('#mcClose').onclick = closeCard;
if ($('#mapView')) {
    $('#mapView').addEventListener('click', e => {
        if (!e.target.closest('.pin, .map-card, .rpin')) closeCard();
    });
}

// 5. Переключение режимов и инициализация
function setMode(m) {
    mode = m;
    const modeSchemeBtn = $('#modeScheme');
    const modeRealBtn = $('#modeReal');
    if (modeSchemeBtn) modeSchemeBtn.classList.toggle('on', m === 'scheme');
    if (modeRealBtn) modeRealBtn.classList.toggle('on', m === 'real');

    if (world) world.style.display = m === 'scheme' ? '' : 'none';
    const real = $('#mapReal');
    if (real) {
        if (m === 'real') {
            real.hidden = false;
            if (!realMap) initRealMap();
            else setTimeout(() => realMap.resize(), 100);
        } else {
            real.hidden = true;
        }
    }
    closeCard();
    renderMapObjects();
}

if ($('#modeScheme')) $('#modeScheme').onclick = () => setMode('scheme');
if ($('#modeReal')) $('#modeReal').onclick = () => setMode('real');

function initRealMap() {
    const container = document.getElementById('mapReal');
    if (!container) return;

    const style = {
        version: 8,
        sources: {
            carto: {
                type: 'raster',
                tiles: ['https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'],
                tileSize: 256,
                attribution: '© Esri',
            },
        },
        layers: [
            { id: 'carto', type: 'raster', source: 'carto' },
        ],
    };

    try {
        realMap = new maplibregl.Map({
            container: 'mapReal',
            style,
            center: [37.805, 48.008],
            zoom: 12,
            attributionControl: {compact: true},
        });

        realMap.addControl(new maplibregl.NavigationControl(), 'bottom-right');
        realMap.on('click', e => {
            if (!e.originalEvent.target.closest('.rpin')) closeCard();
        });

        realMap.on('load', () => {
            loadDistrictBoundaries();
        });
    } catch (err) {
        console.error('[MAP] Error initializing map:', err);
    }
}

function loadDistrictBoundaries() {
    fetch('/data/districts.geojson')
        .then(r => r.json())
        .then(geojson => addDistrictLayers(geojson))
        .catch(err => console.log('[MAP] GeoJSON not found or error:', err));
}

function addDistrictLayers(geojson) {
    if (!realMap || realMap.getSource('districts')) return;
    realMap.addSource('districts', { type: 'geojson', data: geojson });

    realMap.addLayer({
        id: 'districts-fill',
        type: 'fill',
        source: 'districts',
        paint: { 'fill-color': '#22d3ee', 'fill-opacity': 0.10 },
    });

    realMap.addLayer({
        id: 'districts-outline',
        type: 'line',
        source: 'districts',
        paint: { 'line-color': '#22d3ee', 'line-width': 2, 'line-opacity': 0.85 },
    });

    geojson.features.forEach(f => {
        const coords = f.geometry.coordinates[0];
        const centroid = coords.reduce((acc, [lng, lat]) => ({ lng: acc.lng + lng, lat: acc.lat + lat }), { lng: 0, lat: 0 });
        centroid.lng /= coords.length;
        centroid.lat /= coords.length;

        const label = document.createElement('div');
        label.className = 'district-label';
        label.textContent = f.properties.name;

        new maplibregl.Marker({ element: label, anchor: 'center' })
            .setLngLat([centroid.lng, centroid.lat])
            .addTo(realMap);
    });

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

    realMap.on('click', 'districts-fill', (e) => {
        if (e.features.length) {
            const name = e.features[0].properties.name;
            goDistrict(name);
        }
    });
}

/* ---------- ОБРАБОТЧИКИ ПОИСКА ---------- */
const searchForm = $('#searchForm');
if (searchForm) {
    searchForm.addEventListener('submit', e => {
        e.preventDefault();
        runSearch($('#searchInput')?.value);
    });
}

const quickForm = $('#quickForm');
if (quickForm) {
    quickForm.addEventListener('submit', e => {
        e.preventDefault();
        runSearch(e.target.querySelector('input')?.value);
    });
}

// Очистка поиска при смене фильтров слоев (опционально)
// Можно добавить вызов runSearch('') при клике на чипы слоев, если нужно сбрасывать поиск

/* ---------- ОБРАБОТЧИК ГЕОЛОКАЦИИ ---------- */
const geoBtn = $('#geoBtn');
if (geoBtn) {
    geoBtn.onclick = function () {
        const btn = this;
        btn.textContent = 'Определяем…';
        btn.disabled = true;

        if (!navigator.geolocation) {
            toast('Геолокация недоступна в вашем браузере');
            btn.textContent = 'Определить местоположение';
            btn.disabled = false;
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                renderNearby(pos.coords.latitude, pos.coords.longitude);
                btn.hidden = true;

                const nearGrid = $('#nearGrid');
                if (nearGrid) nearGrid.hidden = false;

                toast('📍 Показаны ближайшие заведения');
                $('#nearby')?.scrollIntoView({behavior: 'smooth'});
            },
            (err) => {
                console.error('Geo error:', err);
                toast('Не удалось определить местоположение. Проверьте разрешения браузера.');
                btn.textContent = 'Попробовать снова';
                btn.disabled = false;
            },
            { timeout: 10000, maximumAge: 60000 }
        );
    };
}

// Запуск загрузки данных карты
loadMapData();
/* ========================================================================
   РАЙОНЫ И КАТЕГОРИИ (ПЕРЕАДРЕСАЦИЯ В КАТАЛОГ ПО SLUG)
   ======================================================================== */

// Функция перехода в каталог с фильтром по району (теперь принимает slug)
function goDistrict(slug) {
    if (!slug) return;
    window.location.href = `/places?districts[]=${encodeURIComponent(slug)}`;
}

// 1. Обработка кликов по SVG-карте районов
$$('.d-area').forEach(area => {
    area.style.cursor = 'pointer';
    area.addEventListener('click', (e) => {
        e.stopPropagation();
        // <-- ИЗМЕНЕНО: берем slug вместо названия
        const districtSlug = area.dataset.slug;
        if (districtSlug) {
            goDistrict(districtSlug);
        }
    });
});

function renderDistrictGrid() {
    const grid = $('#distGrid');
    if (!grid) return;

    const districts = window.LANDING?.districts || [];

    if (districts.length > 0) {
        const colorMap = {
            'Куйбышевский': 'var(--blue)',
            'Киевский': 'var(--lime)',
            'Калининский': 'var(--cyan)',
            'Кировский': 'var(--yellow)',
            'Ворошиловский': 'var(--magenta)',
            'Будённовский': 'var(--orange)',
            'Петровский': 'var(--violet)',
            'Ленинский': 'var(--rose)',
            'Пролетарский': 'var(--emerald)'
        };

        grid.innerHTML = districts.map(d => {
            const color = colorMap[d.name] || 'var(--cyan)';
            const placesCount = d.stats?.places || 0;
            const topCats = (d.stats?.cats || []).slice(0, 3);

            // ✅ ИСПРАВЛЕНО: используем d.slug для ссылки
            const districtSlug = d.slug || d.name;

            return `
                <a href="/places?districts[]=${encodeURIComponent(districtSlug)}" class="d-card" style="--dc: ${color}">
                    ${d.image ? `<div class="d-bg" style="background-image:url('${d.image}')"></div>` : ''}
                    <h3>${d.name}</h3>
                    <div class="dc">${placesCount} <small>мест</small></div>
                    <div class="b-tags">
                        ${topCats.map(c => `<span>${c}</span>`).join('')}
                    </div>
                    <div class="b-link">Все места →</div>
                </a>
            `;
        }).join('');
    }
}

// Запускаем рендер сетки районов при загрузке страницы
renderDistrictGrid();

// Обработка кликов по карточкам категорий (Bento grid)
const CAT_MAP = {
    'cat-food': 'food', 'cat-leisure': 'leisure', 'cat-beauty': 'beauty',
    'cat-shop': 'shop', 'cat-auto': 'auto',
};

Object.entries(CAT_MAP).forEach(([id, cat]) => {
    const card = document.getElementById(id);
    if (card) {
        card.onclick = () => {
            window.location.href = `/places?categories[]=${cat}`;
        };
    }
});

$$('.b-card[data-cat]').forEach(card => {
    card.onclick = () => {
        const cat = card.dataset.cat;
        if (cat) window.location.href = `/places?categories[]=${cat}`;
    };
});

$$('.quick-card').forEach(card => {
    card.onclick = () => {
        const cat = card.dataset.category;
        if (cat) window.location.href = `/places?categories[]=${cat}`;
    };
});

/* ========================================================================
   МОДАЛКИ (Заведение, Лид, Viewer)
   ======================================================================== */
function openModal(id) {
    const m = $('#' + id);
    if (m) {
        m.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(m) {
    m.classList.remove('open');
    if (!$$('.modal.open').length && !$('#viewer')?.classList.contains('open')) {
        document.body.style.overflow = '';
    }
}

$$('[data-open]').forEach(b => b.onclick = () => openModal(b.dataset.open));
$$('.modal').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) closeModal(m); });
    m.querySelectorAll('[data-close]').forEach(b => b.onclick = () => closeModal(m));
});
addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        $$('.modal.open').forEach(closeModal);
        closeViewer();
    }
});

/* ---------- заявка «Добавить предприятие» ---------- */
let step = 0;
const panes = $$('#addModal .m-pane'), steps = $$('#addModal .m-step');
const stepNames = ['Основная информация', 'Контактное лицо', 'Медиа', 'Подтверждение'];

function goStep(n) {
    step = n;
    panes.forEach((p, i) => p.classList.toggle('on', i === n));
    steps.forEach((s, i) => s.classList.toggle('on', i <= n));
    const lbl = $('#stepLbl'), name = $('#stepName'), back = $('#mBack'), next = $('#mNext');
    if (lbl) lbl.textContent = `Шаг ${n + 1} из 4`;
    if (name) name.textContent = stepNames[n];
    if (back) back.hidden = n === 0;
    if (next) next.textContent = n === 3 ? 'Отправить заявку' : 'Далее →';
}

const mBack = $('#mBack'), mNext = $('#mNext');
if (mBack) mBack.onclick = () => goStep(step - 1);

if (mNext) {
    mNext.onclick = async () => {
        if (step === 0 && (!$('#orgName')?.value.trim() || !$('#orgCat')?.value)) {
            toast('Заполните название и категорию'); return;
        }
        if (step === 1 && !$('#personName')?.value.trim()) {
            toast('Укажите имя контактного лица'); return;
        }
        if (step === 3) {
            if ($$('#addModal .req-chk').filter(c => !c.checked).length) {
                toast('Подтвердите все условия размещения'); return;
            }
            mNext.disabled = true; mNext.textContent = 'Отправляем…';
            // TODO: Адаптировать payload под новый API
            mNext.disabled = false;
            $('#addFlow').hidden = true;
            const success = $('#addSuccess');
            if (success) success.hidden = false;
            return;
        }
        goStep(step + 1);
    };
}

/* ---------- drag-and-drop ---------- */
const dz = $('#dropZone'), fi = $('#fileInput'), fc = $('#fileChips');
if (dz && fi && fc) {
    dz.onclick = () => fi.click();
    ['dragenter', 'dragover'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('drag'); }));
    ['dragleave', 'drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('drag'); }));
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
}

/* ---------- lead-модалка ---------- */
const leadSubmit = $('#leadSubmit');
if (leadSubmit) {
    leadSubmit.onclick = async () => {
        const chks = $$('#leadModal .req-chk');
        if (chks.filter(c => !c.checked).length) {
            toast('Отметьте согласие на обработку данных'); return;
        }
        leadSubmit.disabled = true; leadSubmit.textContent = 'Отправляем…';
        // TODO: Адаптировать под новый API
        setTimeout(() => {
            leadSubmit.disabled = false;
            $('#leadFlow').hidden = true;
            const ls = $('#leadSuccess');
            if (ls) ls.hidden = false;
        }, 500);
    };
}
const dlOnce = $('#dlOnce'), dlAgain = $('#dlAgain');
if (dlOnce) dlOnce.onclick = downloadPDF;
if (dlAgain) dlAgain.onclick = downloadPDF;
const magDownload = $('#magDownload');
if (magDownload) magDownload.onclick = downloadPDF;

function downloadPDF() {
    toast('⬇ Скачивание PDF началось (демо)');
}

/* ---------- PDF Viewer ---------- */
const pages = $$('#vStage .vpage'), vInd = $('#vInd'), thumbsRail = $('#vThumbsRail');
let cur = 0, zoom = 1;
if (pages.length) {
    pages.forEach((p, i) => {
        const t = document.createElement('button');
        t.className = 'v-thumb';
        t.textContent = (i + 1) + ' · ' + (p.dataset.t || '');
        t.onclick = () => goPage(i);
        thumbsRail.appendChild(t);
    });
}

function goPage(i, dir) {
    if (i < 0 || i >= pages.length) return;
    cur = i; zoom = 1;
    const vStage = $('#vStage');
    if (vStage) vStage.style.setProperty('--zoom', 1);
    pages.forEach((p, k) => {
        p.classList.toggle('cur', k === cur);
        p.classList.remove('anim-n', 'anim-p');
    });
    const c = pages[cur];
    void c.offsetWidth;
    c.classList.add(dir === 'p' ? 'anim-p' : 'anim-n');
    if (vInd) vInd.textContent = String(cur + 1).padStart(2, '0') + ' / ' + pages.length;
    $$('.v-thumb').forEach((t, k) => t.classList.toggle('on', k === cur));
}

const vPrev = $('#vPrev'), vNext = $('#vNext'), vThumbs = $('#vThumbs');
const vZoomIn = $('#vZoomIn'), vZoomOut = $('#vZoomOut'), vFull = $('#vFull');
const vShare = $('#vShare'), vDownload = $('#vDownload'), vClose = $('#vClose');
const openViewer = $('#openViewer');

if (vPrev) vPrev.onclick = () => goPage(cur - 1, 'p');
if (vNext) vNext.onclick = () => goPage(cur + 1, 'n');
if (vThumbs) vThumbs.onclick = () => thumbsRail?.classList.toggle('hide');
if (vZoomIn) vZoomIn.onclick = () => { zoom = Math.min(1.6, zoom + .15); $('#vStage')?.style.setProperty('--zoom', zoom); };
if (vZoomOut) vZoomOut.onclick = () => { zoom = Math.max(.7, zoom - .15); $('#vStage')?.style.setProperty('--zoom', zoom); };
if (vFull) vFull.onclick = () => { document.fullscreenElement ? document.exitFullscreen() : $('#viewer')?.requestFullscreen(); };
if (vShare) vShare.onclick = async () => {
    try { await navigator.share({title: 'ВИЗИТ ДОНЕЦК', url: location.href}); }
    catch (e) { toast('Ссылка скопирована'); }
};
if (vDownload) vDownload.onclick = downloadPDF;
if (vClose) vClose.onclick = closeViewer;

function closeViewer() {
    const viewer = $('#viewer');
    if (viewer) {
        viewer.classList.remove('open');
        if (!$$('.modal.open').length) document.body.style.overflow = '';
    }
}

if (openViewer) {
    openViewer.onclick = () => {
        const viewer = $('#viewer');
        if (viewer) {
            viewer.classList.add('open');
            document.body.style.overflow = 'hidden';
            goPage(0);
        }
    };
}

/* ========================================================================
   ГЕОЛОКАЦИЯ (TODO: АДАПТИРОВАТЬ ПОД НОВЫЙ API)
   ======================================================================== */
// Закомментировано, так как зависит от удаленного массива PLACES и CATS
/*
$('#geoBtn').onclick = function () { ... }
function getDistance(...) { ... }
function renderNearby(...) { ... }
*/

/* ---------- подписка ---------- */
const subForm = $('#subForm');
if (subForm) {
    subForm.addEventListener('submit', async e => {
        e.preventDefault();
        const input = e.target.querySelector('input');
        const res = await postJSON('/api/v1/subscribe', {email: input.value});
        if (res.ok) {
            e.target.innerHTML = '<div class="sub-ok">✓ Готово! Вы подписаны.</div>';
        } else {
            toast(res.error);
        }
    });
}

/* ========================================================================
   ИСТОРИИ
   ======================================================================== */
let storiesActual = [], storiesArchive = [], storyIndex = 0, storyQueue = [];
let storyTimer = null, storyProgressRaf = null;

const storiesModal = $('#storiesModal');
const storyViewer = $('#storyViewer');
const storyMediaEl = $('#storyMedia');
const storyProgress = $('#storyProgress');

async function fetchStories(kind) {
    const url = kind === 'archive' ? '/api/v1/stories/archive' : '/api/v1/stories';
    try {
        const r = await fetch(url, { headers: { Accept: 'application/json' } });
        const d = await r.json();
        return d.data ?? [];
    } catch (e) { return []; }
}

function renderStoryGrid(list, container, isArchive) {
    if (!container) return;
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
    const loading = $('#storiesLoading'), empty = $('#storiesEmpty');
    if (loading) loading.hidden = false;
    if (empty) empty.hidden = true;

    const [actual, archive] = await Promise.all([fetchStories('actual'), fetchStories('archive')]);
    storiesActual = actual;
    storiesArchive = archive;

    if (loading) loading.hidden = true;

    renderStoryGrid(actual, $('#storiesActual'), false);
    renderStoryGrid(archive, $('#storiesArchive'), true);

    if (!actual.length && !archive.length && empty) empty.hidden = false;

    const badge = $('#storiesBadge');
    if (actual.length && badge) {
        badge.textContent = actual.length;
        badge.hidden = false;
    } else if (badge) {
        badge.hidden = true;
    }
}

const storiesTrigger = $('#storiesTrigger');
if (storiesTrigger && storiesModal) {
    storiesTrigger.onclick = () => {
        storiesModal.classList.add('open');
        document.body.style.overflow = 'hidden';
        if (!storiesActual.length && !storiesArchive.length) loadStories();
    };
}

$$('[data-close-stories]').forEach(el => el.onclick = () => {
    storiesModal.classList.remove('open');
    if (!storyViewer.classList.contains('open')) document.body.style.overflow = '';
});

$$('.stories-tab').forEach(tab => {
    tab.onclick = () => {
        $$('.stories-tab').forEach(t => t.classList.toggle('on', t === tab));
        const target = tab.dataset.stab;
        const actualEl = $('#storiesActual'), archiveEl = $('#storiesArchive'), emptyEl = $('#storiesEmpty');
        if (actualEl) actualEl.hidden = target !== 'actual';
        if (archiveEl) archiveEl.hidden = target !== 'archive';
        if (emptyEl) emptyEl.hidden = !((target === 'actual' && !storiesActual.length) || (target === 'archive' && !storiesArchive.length));
    };
});

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

    fetch(`/api/v1/stories/${s.id}/view`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' } }).catch(() => {});

    if (storyMediaEl) {
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
            startAutoProgress(7000);
        }
        storyMediaEl.appendChild(mediaEl);
    }

    const sp = $('#storyPlace'), st = $('#storyTitle'), sx = $('#storyText'), ss = $('#storySide');
    if (sp) sp.textContent = s.place?.name ?? 'ВИЗИТ ДОНЕЦК';
    if (st) st.textContent = s.title ?? '';
    if (sx) sx.textContent = s.text ?? '';
    if (ss) ss.style.display = (s.title || s.text) ? '' : 'none';

    const body = $('.story-viewer__body');
    if (body) body.style.gridTemplateColumns = (s.title || s.text) ? '' : '1fr';
}

function updateProgress(ratio) {
    if (storyProgress) storyProgress.style.width = Math.min(100, ratio * 100) + '%';
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
    if (storyMediaEl) storyMediaEl.innerHTML = '';
    if (!storiesModal.classList.contains('open')) document.body.style.overflow = '';
}

$$('.story-viewer__nav').forEach(btn => {
    btn.onclick = () => btn.dataset.nav === 'next' ? nextStory() : prevStory();
});
$$('[data-close-viewer]').forEach(el => el.onclick = closeStoryViewer);

addEventListener('keydown', e => {
    if (!storyViewer.classList.contains('open')) return;
    if (e.key === 'ArrowRight') nextStory();
    if (e.key === 'ArrowLeft')  prevStory();
    if (e.key === 'Escape')     closeStoryViewer();
});

if (storyMediaEl) {
    storyMediaEl.addEventListener('click', e => {
        const rect = storyMediaEl.getBoundingClientRect();
        const x = e.clientX - rect.left;
        if (x < rect.width / 3) prevStory();
        else if (x > rect.width * 2 / 3) nextStory();
    });
}

/* ========================================================================
   МЕНЮ ЗАВЕДЕНИЯ
   ======================================================================== */
const menuModal = document.getElementById('menuModal');
const menuBody = menuModal?.querySelector('#menuBody');
const menuSearch = document.getElementById('menuSearch');
const menuSearchClear = document.getElementById('menuSearchClear');
const openMenuBtn = document.getElementById('openMenuBtn');

let menuItems = [];
let menuLoaded = false;

if (openMenuBtn && menuModal) {
    openMenuBtn.addEventListener('click', async () => {
        openMenu();
        if (!menuLoaded) await loadMenu(openMenuBtn.dataset.placeId);
    });

    document.querySelectorAll('[data-close-menu]').forEach(el => el.addEventListener('click', closeMenu));

    if (menuSearch) {
        menuSearch.addEventListener('input', (e) => {
            const q = e.target.value.trim().toLowerCase();
            if (menuSearchClear) menuSearchClear.hidden = !q;
            renderMenuItems(q);
        });
    }
    if (menuSearchClear) {
        menuSearchClear.addEventListener('click', () => {
            menuSearch.value = '';
            menuSearchClear.hidden = true;
            renderMenuItems('');
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menuModal.classList.contains('open')) closeMenu();
    });
}

function openMenu() {
    menuModal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeMenu() {
    menuModal.classList.remove('open');
    document.body.style.overflow = '';
}

async function loadMenu(placeId) {
    if (!menuBody) return;
    menuBody.innerHTML = `<div class="menu-modal__state"><div class="menu-modal__spinner"></div><p>Загружаем меню…</p></div>`;
    try {
        const res = await fetch(`/api/v1/places/${placeId}/menu`, { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        menuItems = Array.isArray(data.data) ? data.data : [];
        menuLoaded = true;
        renderMenuItems('');
    } catch (err) {
        menuBody.innerHTML = `<div class="menu-modal__state menu-modal__state--error"><div class="menu-modal__ico">⚠</div><h4>Не удалось загрузить меню</h4></div>`;
    }
}

function renderMenuItems(query) {
    if (!menuBody) return;
    if (!menuItems.length) {
        menuBody.innerHTML = `<div class="menu-modal__state"><div class="menu-modal__ico">📋</div><h4>Меню пока не добавлено</h4></div>`;
        return;
    }
    const q = (query || '').trim().toLowerCase();
    const filtered = q ? menuItems.filter(it => (it.name || '').toLowerCase().includes(q) || (it.description || '').toLowerCase().includes(q)) : menuItems;

    if (!filtered.length) {
        menuBody.innerHTML = `<div class="menu-modal__state"><div class="menu-modal__ico">🔍</div><h4>Ничего не найдено</h4></div>`;
        return;
    }

    const grouped = {};
    filtered.forEach(it => {
        const cat = it.category || 'Без категории';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(it);
    });

    menuBody.innerHTML = Object.entries(grouped).map(([cat, items]) => `
        <section class="menu-modal__group">
            <h4 class="menu-modal__group-title">${escapeHtml(cat)}</h4>
            <div class="menu-modal__grid">${items.map(renderItem).join('')}</div>
        </section>
    `).join('');
}

function renderItem(it) {
    const price = typeof it.price === 'number' ? new Intl.NumberFormat('ru-RU').format(it.price) + ' ₽' : '—';
    return `
        <article class="menu-item">
            ${it.image ? `<div class="menu-item__img"><img src="${escapeAttr(it.image)}" alt="${escapeAttr(it.name)}" loading="lazy"></div>` : `<div class="menu-item__img menu-item__img--placeholder">🍽</div>`}
            <div class="menu-item__body">
                <h5 class="menu-item__name">${escapeHtml(it.name)}</h5>
                ${it.description ? `<p class="menu-item__desc">${escapeHtml(it.description)}</p>` : ''}
                <div class="menu-item__foot">
                    <span class="menu-item__price">${price}</span>
                    ${it.weight ? `<span class="menu-item__weight">${escapeHtml(it.weight)}</span>` : ''}
                </div>
            </div>
        </article>`;
}

function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
}
function escapeAttr(str) { return escapeHtml(str); }

/* ========================================================================
   ЛОГИРОВАНИЕ ВНЕШНИХ ССЫЛОК & PWA
   ======================================================================== */
(function() {
    const OWN_HOST = location.hostname.replace(/^www\./, '');

    function isExternal(url) {
        try {
            const u = new URL(url, location.origin);
            return u.hostname.replace(/^www\./, '') !== OWN_HOST;
        } catch { return false; }
    }

    function guessContext(pageUrl) {
        if (pageUrl.includes('/place/')) return 'place';
        if (pageUrl.includes('/news/')) return 'news';
        if (pageUrl.includes('/event/')) return 'event';
        if (pageUrl.includes('/story')) return 'story';
        if (pageUrl.includes('/category')) return 'category';
        if (pageUrl.includes('/ref/')) return 'referral';
        return null;
    }

    function extractPlaceId(pageUrl) {
        const m = pageUrl.match(/\/place\/(\d+)/);
        return m ? m[1] : null;
    }

    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href]');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return;
        if (!isExternal(href)) return;

        const payload = {
            url: href,
            context: guessContext(location.pathname),
            place_id: extractPlaceId(location.pathname),
            page_url: location.href,
        };

        if (navigator.sendBeacon) {
            navigator.sendBeacon('/api/v1/out', JSON.stringify(payload));
        } else {
            fetch('/api/v1/out', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload), keepalive: true }).catch(() => {});
        }
    }, true);

    let updateServiceWorker;
    const registerPWA = registerSW({
        onNeedRefresh() { showUpdatePrompt(); },
        onOfflineReady() { console.log('PWA готов к работе офлайн'); },
    });
    updateServiceWorker = registerPWA;

    function checkBackendVersion() {
        const metaVersion = document.querySelector('meta[name="app-version"]')?.content;
        const storedVersion = localStorage.getItem('pwa_app_version');
        if (metaVersion && storedVersion && metaVersion !== storedVersion) {
            console.log(`Версия изменена: ${storedVersion} -> ${metaVersion}`);
            showUpdatePrompt();
        }
        if (metaVersion) localStorage.setItem('pwa_app_version', metaVersion);
    }

    function showUpdatePrompt() {
        const toastEl = document.getElementById('pwa-update-toast');
        if (!toastEl || toastEl.classList.contains('show')) return;
        toastEl.hidden = false;
        setTimeout(() => toastEl.classList.add('show'), 50);
    }

    document.getElementById('pwa-update-btn')?.addEventListener('click', async () => {
        const toastEl = document.getElementById('pwa-update-toast');
        if (toastEl) toastEl.classList.remove('show');
        if (typeof updateServiceWorker === 'function') await updateServiceWorker(true);
        if ('caches' in window) {
            const cacheNames = await caches.keys();
            await Promise.all(cacheNames.map(name => caches.delete(name)));
        }
        setTimeout(() => window.location.reload(true), 300);
    });

    checkBackendVersion();
    setInterval(checkBackendVersion, 5 * 60 * 1000);


    // ==========================================================================
// ОБРАБОТЧИКИ ПОИСКА ПО КАРТЕ
// ==========================================================================
    const mapSearchInput = document.getElementById('mapSearchInput');
    const mapSearchClear = document.getElementById('mapSearchClear');

    if (mapSearchInput && mapSearchClear) {
        // Живой поиск при вводе
        mapSearchInput.addEventListener('input', (e) => {
            mapSearchQuery = e.target.value.trim().toLowerCase();
            mapSearchClear.hidden = !mapSearchQuery;
            renderMapObjects(); // Мгновенно перерисовываем карту
        });

        // Кнопка очистки
        mapSearchClear.addEventListener('click', () => {
            mapSearchInput.value = '';
            mapSearchQuery = '';
            mapSearchClear.hidden = true;
            renderMapObjects();
            mapSearchInput.focus();
        });
    }

    /* ==========================================================================
   ПЛАВАЮЩЕЕ МЕНЮ (FAB) И ИЗБРАННОЕ
   ========================================================================== */
    const fabContainer = document.getElementById('fabContainer');
    const fabMain = document.getElementById('fabMain');
    const fabStories = document.getElementById('fabStories');
    const fabFavorites = document.getElementById('fabFavorites');

// 1. Переключение меню
    if (fabMain && fabContainer) {
        fabMain.addEventListener('click', () => {
            fabContainer.classList.toggle('is-open');
            fabMain.classList.toggle('is-active');
        });

        // Закрытие при клике вне меню
        document.addEventListener('click', (e) => {
            if (!fabContainer.contains(e.target)) {
                fabContainer.classList.remove('is-open');
                fabMain.classList.remove('is-active');
            }
        });
    }

// 2. Логика кнопки "Истории"
    if (fabStories && storiesModal) {
        fabStories.addEventListener('click', () => {
            // Скрываем FAB-меню
            fabContainer.classList.remove('is-open');
            fabMain.classList.remove('is-active');

            // Открываем модалку историй
            storiesModal.classList.add('open');
            document.body.style.overflow = 'hidden';

            // Загружаем истории, если они еще не загружены
            if (!storiesActual.length && !storiesArchive.length) {
                loadStories();
            }
        });
    }

// 3. Логика кнопки "Избранное"
    if (fabFavorites) {
        fabFavorites.addEventListener('click', async () => {
            fabContainer.classList.remove('is-open');
            fabMain.classList.remove('is-active');
            await openFavoritesModal();
        });
    }

    async function openFavoritesModal() {
        const modal = document.getElementById('favoritesModal');
        if (!modal) {
            console.error('❌ Модалка #favoritesModal не найдена в DOM');
            return;
        }

        const localSection = document.getElementById('favLocalSection');
        const globalSection = document.getElementById('favGlobalSection');
        const localList = document.getElementById('favLocalList');
        const globalList = document.getElementById('favGlobalList');
        const emptyState = document.getElementById('favEmptyState');
        const authPrompt = document.getElementById('favAuthPrompt');

        // Очистка перед рендером
        localList.innerHTML = '';
        globalList.innerHTML = '';
        localSection.hidden = true;
        globalSection.hidden = true;
        emptyState.hidden = true;
        authPrompt.hidden = true;

        const LOCAL_FAV_KEY = 'vizit_local_favorites';
        const currentUserId = window.PLACE_DATA?.currentUserId || null;
        let hasAnyFavs = false;

        // --- А. Локальные избранные (ЧИТАЕМ ПОЛНЫЕ ДАННЫЕ ИЗ LOCALSTORAGE) ---
        let localFavs = [];
        try {
            const raw = localStorage.getItem(LOCAL_FAV_KEY);
            console.log(' Raw localStorage data:', raw);

            if (raw) {
                localFavs = JSON.parse(raw);
                // Поддержка старого формата (массив ID) — конвертируем
                if (Array.isArray(localFavs) && localFavs.length > 0 && typeof localFavs[0] === 'number') {
                    console.warn('⚠️ Старый формат localStorage (только ID). Мигрируем...');
                    localFavs = localFavs.map(id => ({ id, name: `Место #${id}`, slug: '', saved_at: new Date().toISOString() }));
                }
            }
        } catch (e) {
            console.error('❌ Ошибка чтения localStorage:', e);
            localFavs = [];
        }

        console.log('📋 Локальных избранных:', localFavs.length, localFavs);

        if (localFavs.length > 0) {
            localSection.hidden = false;
            hasAnyFavs = true;

            localFavs.forEach(place => {
                const item = createFavItem(place, true);
                if (item) localList.appendChild(item);
            });
        }

        // --- Б. Глобальные избранные (только если авторизован) ---
        if (currentUserId) {
            authPrompt.hidden = true;
            try {
                const res = await fetch('/api/v1/account/favorites', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    const globalPlaces = data.data || [];

                    if (globalPlaces.length > 0) {
                        globalSection.hidden = false;
                        hasAnyFavs = true;
                        globalPlaces.forEach(place => {
                            globalList.appendChild(createFavItem(place, false));
                        });
                    }
                } else {
                    console.warn('⚠️ API вернул статус:', res.status);
                }
            } catch (e) {
                console.error('❌ Ошибка загрузки глобального избранного:', e);
            }
        } else {
            // Не авторизован -> показываем призыв к действию
            authPrompt.hidden = false;
        }

        // --- В. Пустое состояние ---
        if (!hasAnyFavs) {
            emptyState.hidden = false;
        }

        // Показ модалки
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function createFavItem(place, isLocal) {
        if (!place) {
            console.warn('⚠️ createFavItem вызван без данных места');
            return null;
        }

        const div = document.createElement('a');

        // Если slug есть — делаем рабочую ссылку
        if (place.slug && place.slug !== 'unknown' && place.slug !== '#') {
            div.href = `/place/${place.slug}`;
            div.className = `fav-item ${isLocal ? 'fav-item--local' : ''}`;
        } else {
            // Фолбэк для старых битых записей
            console.warn('⚠️ У места отсутствует slug. Данные:', place);
            div.href = '#';
            div.className = `fav-item ${isLocal ? 'fav-item--local' : ''}`;
            div.style.opacity = '0.6';
            div.style.cursor = 'not-allowed';
            div.title = 'Пересохраните это место, чтобы ссылка заработала';

            div.onclick = (e) => {
                e.preventDefault();
                showToast('⚠️ Ссылка устарела. Удалите и сохраните место заново.');
            };
        }

        const badgeHtml = isLocal ? `<span class="fav-item__badge">Локально</span>` : '';
        const removeBtnHtml = isLocal ?
            `<button class="fav-item__remove" data-remove-local="${place.id}" title="Удалить">✕</button>` :
            `<button class="fav-item__remove" data-remove-global="${place.id}" title="Удалить">✕</button>`;

        const coverUrl = place.cover_url || '/assets/placeholder.jpg';
        const name = place.name || 'Неизвестное место';
        const category = place.category?.name || place.category || 'Место';
        const district = place.district?.name || place.district || '';
        const metaText = district ? `${category} · ${district}` : category;

        div.innerHTML = `
        <img src="${coverUrl}" alt="${name}" class="fav-item__img"
             onerror="this.src='/assets/placeholder.jpg'">
        <div class="fav-item__info">
            <div class="fav-item__name">${escapeHtml(name)}</div>
            <div class="fav-item__meta">
                ${badgeHtml}
                <span>${escapeHtml(metaText)}</span>
            </div>
        </div>
        ${removeBtnHtml}
    `;

        // Логика удаления
        const removeBtn = div.querySelector('.fav-item__remove');
        removeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (isLocal) {
                const LOCAL_FAV_KEY = 'vizit_local_favorites';
                let localFavs = JSON.parse(localStorage.getItem(LOCAL_FAV_KEY) || '[]');
                localFavs = localFavs.filter(f => f.id !== place.id);
                localStorage.setItem(LOCAL_FAV_KEY, JSON.stringify(localFavs));

                div.remove();

                if (localFavs.length === 0) {
                    document.getElementById('favLocalSection').hidden = true;
                    checkEmptyState();
                }

                if (typeof showToast === 'function') showToast('Удалено из локального избранного');
                else if (typeof toast === 'function') toast('Удалено из локального избранного');
            } else {
                div.remove();
                checkEmptyState();
                if (typeof showToast === 'function') showToast('Удалено из избранного');
            }
        });

        return div;
    }

// Защита от XSS
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, s => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[s]));
    }

    function checkEmptyState() {
        const localList = document.getElementById('favLocalList');
        const globalList = document.getElementById('favGlobalList');
        const emptyState = document.getElementById('favEmptyState');

        if (localList && globalList && emptyState) {
            if (localList.children.length === 0 && globalList.children.length === 0) {
                document.getElementById('favLocalSection').hidden = true;
                document.getElementById('favGlobalSection').hidden = true;
                emptyState.hidden = false;
            }
        }
    }


// Закрытие модалки избранного
    document.querySelectorAll('[data-close-fav]').forEach(el => {
        el.addEventListener('click', () => {
            document.getElementById('favoritesModal').classList.remove('open');
            document.body.style.overflow = '';
        });
    });

    /* ==========================================================================
   ЛОГИКА КАРТОЧЕК НАСТРОЕНИЯ (MOODS) + ИИ-ПОМОЩНИК
   ========================================================================== */

// Ссылки на элементы панели ИИ
    const aiPanel = document.getElementById('aiPanel');
    const aiBackdrop = document.getElementById('aiBackdrop');
    const aiPanelTitle = document.getElementById('aiPanelTitle');
    const aiMessages = document.getElementById('aiMessages');
    const aiForm = document.getElementById('aiForm');
    const aiInput = document.getElementById('aiInput');
    const aiSendBtn = document.getElementById('aiSendBtn');
    const closeAiPanel = document.getElementById('closeAiPanel');

    let aiChatHistory = [];

// 1. Открытие/закрытие панели ИИ
    function toggleAiPanel(isOpen) {
        if (!aiPanel) return;
        if (isOpen) {
            aiPanel.classList.add('is-open');
            aiBackdrop.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            setTimeout(() => aiInput?.focus(), 350);
        } else {
            aiPanel.classList.remove('is-open');
            aiBackdrop.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }

    if (closeAiPanel) closeAiPanel.addEventListener('click', () => toggleAiPanel(false));
    if (aiBackdrop) aiBackdrop.addEventListener('click', () => toggleAiPanel(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && aiPanel?.classList.contains('is-open')) {
            toggleAiPanel(false);
        }
    });

// 2. Обработчики карточек настроения
    $$('.m-card').forEach(card => {
        card.addEventListener('click', () => {
            const mood = card.dataset.mood;
            const msg = card.dataset.msg;
            const title = card.querySelector('h3')?.textContent || 'Подборка';

            if (!mood) return;

            // Открываем панель ИИ
            toggleAiPanel(true);

            // Сбрасываем предыдущий диалог
            aiMessages.innerHTML = '';
            aiChatHistory = [];
            aiPanelTitle.textContent = title;

            // Добавляем приветственное сообщение бота
            appendAiMessage('bot', `Отличный выбор! Подбираю для вас лучшие места...`);

            // Формируем умный промпт для ИИ
            const prompt = buildMoodPrompt(mood, msg);

            // Автоматически отправляем запрос к ИИ
            sendAiRequest(prompt);
        });
    });

// 3. Формирование промпта на основе настроения
    function buildMoodPrompt(mood, msg) {
        const prompts = {
            bars: `Пользователь хочет провести вечер в городе. ${msg}. Посоветуй 3-5 лучших баров, ресторанов или клубов из базы данных. Укажи название, район и коротко опиши атмосферу.`,
            coffee: `Пользователь ищет место для медленного утра. ${msg}. Посоветуй 3-5 лучших кофеен или пекарен из базы данных. Укажи название, район и что там стоит попробовать.`,
            food: `Пользователь хочет вкусно поесть. ${msg}. Посоветуй 3-5 лучших ресторанов или гастробаров из базы данных. Укажи название, район и фирменные блюда.`,
            beauty: `Пользователь хочет уделить время себе. ${msg}. Посоветуй 3-5 лучших SPA, салонов красоты или мест для релакса из базы данных. Укажи название и район.`,
            leisure: `Пользователь ищет, куда пойти развлечься. ${msg}. Посоветуй 3-5 мест для отдыха и развлечений из базы данных. Укажи название и район.`,
            auto: `Пользователю нужен уход за автомобилем. ${msg}. Посоветуй 3-5 лучших автосервисов, моек или детейлингов из базы данных. Укажи название и район.`,
        };

        return prompts[mood] || `Пользователь выбрал категорию: ${mood}. ${msg}. Посоветуй лучшие места из базы данных.`;
    }

// 4. Отправка запроса к ИИ
    async function sendAiRequest(message) {
        if (!message) return;

        // Добавляем сообщение пользователя в историю
        aiChatHistory.push({ role: 'user', content: message });
        aiSendBtn.disabled = true;

        // Показываем индикатор набора
        const typingId = showAiTyping();

        try {
            const response = await fetch('/api/v1/ai-chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    history: aiChatHistory.slice(-6) // Последние 6 сообщений для контекста
                })
            });

            const data = await response.json();
            removeAiTyping(typingId);

            if (data.success) {
                appendAiMessage('bot', data.message);
                aiChatHistory.push({ role: 'assistant', content: data.message });
            } else {
                appendAiMessage('bot', '⚠️ ' + (data.message || 'Не удалось получить ответ. Попробуйте позже.'));
            }
        } catch (error) {
            console.error('AI Chat Error:', error);
            removeAiTyping(typingId);
            appendAiMessage('bot', '⚠️ Ошибка сети. Проверьте подключение к интернету.');
        } finally {
            aiSendBtn.disabled = false;
            aiInput?.focus();
        }
    }

// 5. Обработка отправки формы (ручной ввод)
    if (aiForm) {
        aiForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const message = aiInput.value.trim();
            if (!message) return;

            appendAiMessage('user', message);
            aiInput.value = '';
            sendAiRequest(message);
        });
    }

// 6. Вспомогательные функции для рендера сообщений
    function appendAiMessage(role, text) {
        if (!aiMessages) return;

        const div = document.createElement('div');
        div.className = `ai-message ai-message--${role}`;

        // Защита от XSS + поддержка переносов строк
        const safeText = text
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');

        div.innerHTML = `
        <div class="ai-message__ico">${role === 'user' ? '👤' : '🤖'}</div>
        <div class="ai-message__text">${safeText}</div>
    `;

        aiMessages.appendChild(div);
        aiMessages.scrollTop = aiMessages.scrollHeight;
    }

    function showAiTyping() {
        if (!aiMessages) return null;
        const id = 'ai-typing-' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = 'ai-message ai-message--bot';
        div.innerHTML = `
        <div class="ai-message__ico">🤖</div>
        <div class="ai-message__text ai-typing"><span></span><span></span><span></span></div>
    `;
        aiMessages.appendChild(div);
        aiMessages.scrollTop = aiMessages.scrollHeight;
        return id;
    }

    function removeAiTyping(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

})();

/* ---------- предзагрузка историй при старте ---------- */
loadStories();
if (pages.length) goPage(0);
