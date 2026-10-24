<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import * as maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import VButton from '@/Components/ui/VButton.vue'

// Стандартный воркер MapLibre отлично работает в Vite по умолчанию.
// Ручной импорт удалён, чтобы избежать ошибок сборки Rollup.

const props = defineProps(['layers', 'objects', 'places'])

const mapContainer = ref(null)
const mapCollapsed = ref(false)
let map = null
let markers = []

// Режим сортировки
const sortMode = ref(false)

// Форма быстрого создания слоя
const showLayerForm = ref(false)
const layerForm = useForm({
    name: '',
    color: '#22d3ee',
    icon: '📍',
    sort_order: props.layers.length
})

// Форма объекта
const formMode = ref('custom')
const activeLayerId = ref(props.layers[0]?.id || '')
const showObjectForm = ref(false)

const form = useForm({
    layer_id: activeLayerId.value,
    mode: 'custom',
    place_id: '',
    title: '',
    description: '',
    lat: '',
    lng: '',
    x: '',
    y: '',
    is_active: true
})

// Группировка объектов по слоям
const objectsByLayer = computed(() => {
    const grouped = {}
    props.layers.forEach(l => grouped[l.id] = [])
    props.objects.forEach(obj => {
        if (grouped[obj.layer_id]) grouped[obj.layer_id].push(obj)
    })
    return grouped
})

// Создание слоя
const createLayer = () => {
    layerForm.post('/admin/map/layers', {
        preserveScroll: true,
        onSuccess: () => {
            layerForm.reset()
            showLayerForm.value = false
            router.reload({ only: ['layers', 'objects'] })
        }
    })
}

// Удаление слоя
const deleteLayer = (id) => {
    if (!confirm('Удалить слой и все его объекты?')) return
    router.delete(`/admin/map/layers/${id}`, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['layers', 'objects'] })
    })
}

// Удаление объекта
const deleteObject = (id) => {
    if (!confirm('Удалить объект?')) return
    router.delete(`/admin/map/objects/${id}`, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['objects'] })
    })
}

// Перемещение слоя вверх/вниз
const moveLayer = (layer, direction) => {
    const idx = props.layers.findIndex(l => l.id === layer.id)
    if (direction === 'up' && idx === 0) return
    if (direction === 'down' && idx === props.layers.length - 1) return

    const newOrder = [...props.layers]
    const swapIdx = direction === 'up' ? idx - 1 : idx + 1;
    [newOrder[idx], newOrder[swapIdx]] = [newOrder[swapIdx], newOrder[idx]]

    const updates = newOrder.map((l, i) => ({ id: l.id, sort_order: i }))
    router.post('/admin/map/layers/reorder', { layers: updates }, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['layers'] })
    })
}

// Перемещение объекта вверх/вниз
const moveObject = (obj, direction) => {
    const layerObjects = objectsByLayer.value[obj.layer_id]
    const idx = layerObjects.findIndex(o => o.id === obj.id)
    if (direction === 'up' && idx === 0) return
    if (direction === 'down' && idx === layerObjects.length - 1) return

    const newOrder = [...layerObjects]
    const swapIdx = direction === 'up' ? idx - 1 : idx + 1;
    [newOrder[idx], newOrder[swapIdx]] = [newOrder[swapIdx], newOrder[idx]]

    const updates = newOrder.map((o, i) => ({ id: o.id, sort_order: i }))
    router.post('/admin/map/objects/reorder', { objects: updates }, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['objects'] })
    })
}

// Drag & Drop для слоёв
const draggedLayer = ref(null)
const onLayerDragStart = (e, layer) => {
    draggedLayer.value = layer
    e.dataTransfer.effectAllowed = 'move'
    e.dataTransfer.setData('text/plain', layer.id)
    e.target.classList.add('dragging')
}
const onLayerDragEnd = (e) => {
    e.target.classList.remove('dragging')
    draggedLayer.value = null
}
const onLayerDrop = (e, targetLayer) => {
    e.preventDefault()
    if (!draggedLayer.value || draggedLayer.value.id === targetLayer.id) return

    const fromIdx = props.layers.findIndex(l => l.id === draggedLayer.value.id)
    const toIdx = props.layers.findIndex(l => l.id === targetLayer.id)

    const newOrder = [...props.layers]
    const [moved] = newOrder.splice(fromIdx, 1)
    newOrder.splice(toIdx, 0, moved)

    const updates = newOrder.map((l, i) => ({ id: l.id, sort_order: i }))
    router.post('/admin/map/layers/reorder', { layers: updates }, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['layers'] })
    })
}

// Drag & Drop для объектов
const draggedObject = ref(null)
const onObjectDragStart = (e, obj) => {
    draggedObject.value = obj
    e.dataTransfer.effectAllowed = 'move'
    e.dataTransfer.setData('text/plain', obj.id)
    e.target.classList.add('dragging')
}
const onObjectDragEnd = (e) => {
    e.target.classList.remove('dragging')
    draggedObject.value = null
}
const onObjectDrop = (e, targetObj) => {
    e.preventDefault()
    e.stopPropagation()
    if (!draggedObject.value || draggedObject.value.id === targetObj.id) return
    if (draggedObject.value.layer_id !== targetObj.layer_id) return

    const layerObjects = objectsByLayer.value[targetObj.layer_id]
    const fromIdx = layerObjects.findIndex(o => o.id === draggedObject.value.id)
    const toIdx = layerObjects.findIndex(o => o.id === targetObj.id)

    const newOrder = [...layerObjects]
    const [moved] = newOrder.splice(fromIdx, 1)
    newOrder.splice(toIdx, 0, moved)

    const updates = newOrder.map((o, i) => ({ id: o.id, sort_order: i }))
    router.post('/admin/map/objects/reorder', { objects: updates }, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['objects'] })
    })
}

// Клик по карте
const onMapClick = (e) => {
    form.lat = e.lngLat.lat.toFixed(7)
    form.lng = e.lngLat.lng.toFixed(7)
    if (!showObjectForm.value) showObjectForm.value = true
}

// Сохранение объекта
const submitObject = () => {
    const payload = { ...form }
    if (payload.mode === 'place') {
        payload.title = ''
        payload.description = ''
        payload.x = ''
        payload.y = ''
    }
    payload.post('/admin/map/objects', {
        preserveScroll: true,
        onSuccess: () => {
            showObjectForm.value = false
            form.reset()
            form.layer_id = activeLayerId.value
            form.mode = 'custom'
            router.reload({ only: ['objects'] })
        }
    })
}

// Клик по маркеру — заполняем форму
const onMarkerClick = (obj) => {
    form.layer_id = obj.layer_id
    form.mode = obj.is_place ? 'place' : 'custom'
    form.place_id = obj.is_place ? obj.place_id : ''
    form.title = obj.is_place ? '' : obj.name
    form.lat = obj.lat
    form.lng = obj.lng
    form.x = obj.x || ''
    form.y = obj.y || ''
    showObjectForm.value = true
    map.flyTo({ center: [obj.lng, obj.lat], zoom: 15 })
}

// Рендер маркеров
const renderMarkers = () => {
    markers.forEach(m => m.remove())
    markers = []
    props.objects.forEach(obj => {
        const el = document.createElement('div')
        el.className = 'admin-map-pin'
        el.style.setProperty('--pc', obj.layer_color)
        el.innerHTML = `<span>${obj.layer_icon}</span>`
        el.title = obj.name
        el.addEventListener('click', (e) => {
            e.stopPropagation()
            onMarkerClick(obj)
        })
        const marker = new maplibregl.Marker({ element: el })
            .setLngLat([obj.lng, obj.lat])
            .addTo(map)
        markers.push(marker)
    })
}

// Инициализация карты
// Инициализация карты
onMounted(() => {
    if (!mapContainer.value) return;

    // ✅ БЕСПЛАТНЫЙ СТИЛЬ КАРТЫ (OpenStreetMap) БЕЗ API-КЛЮЧЕЙ
    const freeMapStyle = {
        version: 8,
        sources: {
            osm: {
                type: 'raster',
                // Бесплатные тайлы OpenStreetMap
                tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                tileSize: 256,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            },
        },
        layers: [
            {
                id: 'osm-layer',
                type: 'raster',
                source: 'osm',
            },
        ],
    };

    map = new maplibregl.Map({
        container: mapContainer.value,
        style: freeMapStyle, // <-- Используем наш бесплатный стиль
        center: [37.805, 48.008], // Координаты Донецка
        zoom: 12,
        attributionControl: { compact: true }
    });

    map.addControl(new maplibregl.NavigationControl(), 'top-right');
    map.on('click', onMapClick);

    // Рендерим маркеры только после полной загрузки карты
    map.on('load', () => {
        renderMarkers();
    });
});

// ВАЖНО: Пересчитываем размеры карты при разворачивании
watch(mapCollapsed, (newVal) => {
    if (!newVal && map) {
        setTimeout(() => {
            map.resize()
        }, 350) // Ждём окончания CSS-анимации
    }
})

watch(() => props.objects, renderMarkers, { deep: true })
</script>

<template>
    <div class="map-editor-layout" :class="{ 'map-collapsed': mapCollapsed }">
        <!-- ЛЕВАЯ ПАНЕЛЬ -->
        <aside class="editor-sidebar">
            <div class="sidebar-header">
                <h2>🗺 Редактор карты</h2>
                <div class="sidebar-actions">
                    <button
                        class="mode-btn"
                        :class="{ on: sortMode }"
                        @click="sortMode = !sortMode"
                        title="Режим сортировки"
                    >
                        {{ sortMode ? '✓ Готово' : '⇅ Сортировка' }}
                    </button>
                    <button class="icon-btn" @click="mapCollapsed = !mapCollapsed" :title="mapCollapsed ? 'Развернуть карту' : 'Свернуть карту'">
                        {{ mapCollapsed ? '▶' : '◀' }}
                    </button>
                </div>
            </div>

            <!-- КНОПКИ ДЕЙСТВИЙ -->
            <div class="sidebar-buttons">
                <VButton size="sm" @click="showLayerForm = !showLayerForm">
                    {{ showLayerForm ? 'Скрыть форму слоя' : '+ Создать слой' }}
                </VButton>
                <VButton size="sm" variant="ghost" @click="showObjectForm = !showObjectForm">
                    {{ showObjectForm ? 'Скрыть форму' : '+ Добавить объект' }}
                </VButton>
            </div>

            <!-- ФОРМА СОЗДАНИЯ СЛОЯ -->
            <div v-if="showLayerForm" class="editor-form glass-panel">
                <h3>Новый слой</h3>
                <form @submit.prevent="createLayer">
                    <div class="form-group">
                        <label class="lbl">Название *</label>
                        <input v-model="layerForm.name" class="inp" placeholder="Напр. Стрит-арт" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="lbl">Цвет</label>
                            <input v-model="layerForm.color" type="color" class="inp" style="height:42px;padding:4px">
                        </div>
                        <div class="form-group">
                            <label class="lbl">Иконка</label>
                            <input v-model="layerForm.icon" class="inp" placeholder="📍" style="text-align:center;font-size:20px" maxlength="2">
                        </div>
                    </div>
                    <div class="form-actions">
                        <VButton variant="ghost" type="button" @click="showLayerForm = false">Отмена</VButton>
                        <VButton type="submit" :busy="layerForm.processing">Создать</VButton>
                    </div>
                </form>
            </div>

            <!-- СПИСОК СЛОЁВ -->
            <div class="layers-list">
                <h4>Слои</h4>
                <div
                    v-for="(layer, idx) in layers"
                    :key="layer.id"
                    class="layer-item"
                    :class="{
                        active: activeLayerId === layer.id,
                        draggable: sortMode
                    }"
                    :draggable="sortMode"
                    @click="!sortMode && (activeLayerId = layer.id, form.layer_id = layer.id)"
                    @dragstart="onLayerDragStart($event, layer)"
                    @dragend="onLayerDragEnd"
                    @dragover.prevent
                    @drop="onLayerDrop($event, layer)"
                >
                    <div class="layer-main">
                        <span class="layer-color" :style="{ background: layer.color }"></span>
                        <span class="layer-icon">{{ layer.icon }}</span>
                        <span class="layer-name">{{ layer.name }}</span>
                        <span class="layer-count">{{ objectsByLayer[layer.id]?.length || 0 }}</span>
                    </div>

                    <!-- Кнопки управления в режиме сортировки -->
                    <div v-if="sortMode" class="sort-controls" @click.stop>
                        <button class="sort-btn" @click="moveLayer(layer, 'up')" :disabled="idx === 0" title="Вверх">↑</button>
                        <button class="sort-btn" @click="moveLayer(layer, 'down')" :disabled="idx === layers.length - 1" title="Вниз">↓</button>
                        <button class="sort-btn danger" @click="deleteLayer(layer.id)" title="Удалить слой">✕</button>
                    </div>

                    <!-- Объекты слоя -->
                    <div v-if="!sortMode && activeLayerId === layer.id" class="layer-objects">
                        <div
                            v-for="(obj, oidx) in objectsByLayer[layer.id]"
                            :key="obj.id"
                            class="object-item"
                        >
                            <span class="object-icon">{{ obj.is_place ? '🏢' : '⭐' }}</span>
                            <span class="object-name">{{ obj.name }}</span>
                            <button class="object-delete" @click.stop="deleteObject(obj.id)">✕</button>
                        </div>
                        <div v-if="!objectsByLayer[layer.id]?.length" class="empty-hint">
                            Объектов нет. Кликните по карте, чтобы добавить.
                        </div>
                    </div>

                    <!-- Объекты в режиме сортировки -->
                    <div v-if="sortMode && objectsByLayer[layer.id]?.length" class="layer-objects sortable">
                        <div
                            v-for="(obj, oidx) in objectsByLayer[layer.id]"
                            :key="obj.id"
                            class="object-item draggable"
                            :draggable="true"
                            @dragstart="onObjectDragStart($event, obj)"
                            @dragend="onObjectDragEnd"
                            @dragover.prevent
                            @drop="onObjectDrop($event, obj)"
                        >
                            <span class="object-icon">{{ obj.is_place ? '🏢' : '⭐' }}</span>
                            <span class="object-name">{{ obj.name }}</span>
                            <div class="sort-controls mini" @click.stop>
                                <button class="sort-btn mini" @click="moveObject(obj, 'up')" :disabled="oidx === 0">↑</button>
                                <button class="sort-btn mini" @click="moveObject(obj, 'down')" :disabled="oidx === objectsByLayer[layer.id].length - 1">↓</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ФОРМА ОБЪЕКТА -->
            <div v-if="showObjectForm" class="editor-form glass-panel">
                <h3>Новый объект</h3>

                <div class="form-group">
                    <label class="lbl">Режим</label>
                    <div class="mode-switch">
                        <button type="button" :class="{ on: form.mode === 'custom' }" @click="form.mode = 'custom'">⭐ Кастомный</button>
                        <button type="button" :class="{ on: form.mode === 'place' }" @click="form.mode = 'place'">🏢 Заведение</button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="lbl">Слой *</label>
                    <select v-model="form.layer_id" class="inp">
                        <option v-for="l in layers" :key="l.id" :value="l.id">{{ l.icon }} {{ l.name }}</option>
                    </select>
                </div>

                <div v-if="form.mode === 'place'" class="form-group">
                    <label class="lbl">Выберите заведение *</label>
                    <select v-model="form.place_id" class="inp">
                        <option value="">-- Выберите --</option>
                        <option v-for="p in places" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>

                <template v-if="form.mode === 'custom'">
                    <div class="form-group">
                        <label class="lbl">Название *</label>
                        <input v-model="form.title" class="inp" placeholder="Название объекта">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="lbl">Схема X (%)</label>
                            <input v-model="form.x" type="number" step="any" class="inp" placeholder="45.5">
                        </div>
                        <div class="form-group">
                            <label class="lbl">Схема Y (%)</label>
                            <input v-model="form.y" type="number" step="any" class="inp" placeholder="62.0">
                        </div>
                    </div>
                </template>

                <div class="form-row">
                    <div class="form-group">
                        <label class="lbl">Широта (Lat) *</label>
                        <input v-model="form.lat" class="inp" placeholder="Кликните по карте" readonly style="background:var(--panel2);cursor:pointer">
                    </div>
                    <div class="form-group">
                        <label class="lbl">Долгота (Lng) *</label>
                        <input v-model="form.lng" class="inp" placeholder="Кликните по карте" readonly style="background:var(--panel2);cursor:pointer">
                    </div>
                </div>
                <p class="hint">💡 Кликните по карте, чтобы заполнить координаты.</p>

                <div class="form-actions">
                    <VButton variant="ghost" @click="showObjectForm = false">Отмена</VButton>
                    <VButton @click="submitObject" :busy="form.processing">Сохранить</VButton>
                </div>
            </div>
        </aside>

        <!-- ПРАВАЯ ПАНЕЛЬ: КАРТА -->
        <div class="editor-map-container">
            <div ref="mapContainer" class="maplibre-map"></div>
            <div v-if="mapCollapsed" class="map-collapsed-hint" @click="mapCollapsed = false">
                ▶ Развернуть карту
            </div>
        </div>
    </div>
</template>

<style scoped>
.map-editor-layout {
    display: flex;
    height: calc(100vh - 64px);
    background: var(--bg);
    transition: all .3s ease;
}

/* ===== СОСТОЯНИЕ: КАРТА СВЁРНУТА (ИСПРАВЛЕНО) ===== */
.map-editor-layout.map-collapsed .editor-sidebar {
    flex: 1 !important;
    width: 100% !important;
    min-width: 0 !important; /* КРИТИЧЕСКИ ВАЖНО: сбрасываем ограничение */
    max-width: 100% !important;
    border-right: none;
}

.map-editor-layout.map-collapsed .editor-map-container {
    flex: 0 !important;
    width: 0 !important;
    min-width: 0 !important;
    opacity: 0;
    overflow: hidden;
    pointer-events: none;
}

/* ===== СОСТОЯНИЕ: КАРТА РАЗВЁРНУТА ===== */
.editor-sidebar {
    width: 420px;
    min-width: 420px;
    border-right: 1px solid var(--line);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    background: var(--bg2);
    transition: all .3s ease;
}

.editor-map-container {
    flex: 1;
    position: relative;
    background: #0b101d;
    transition: all .3s ease;
}

/* ===== ОБЩИЕ СТИЛИ ===== */
.sidebar-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--line);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.sidebar-header h2 {
    font-family: var(--disp);
    font-size: 18px;
    margin: 0;
}

.sidebar-actions {
    display: flex;
    gap: 8px;
}

.mode-btn {
    padding: 6px 12px;
    border-radius: 8px;
    background: var(--panel);
    border: 1px solid var(--line);
    color: var(--mut);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
}

.mode-btn:hover {
    border-color: var(--cyan);
    color: var(--txt);
}

.mode-btn.on {
    background: linear-gradient(90deg, var(--cyan), var(--violet));
    border-color: transparent;
    color: #fff;
}

.icon-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--panel);
    border: 1px solid var(--line);
    color: var(--mut);
    cursor: pointer;
    transition: .2s;
    display: grid;
    place-items: center;
}

.icon-btn:hover {
    border-color: var(--cyan);
    color: var(--txt);
}

.sidebar-buttons {
    padding: 12px 20px;
    display: flex;
    gap: 8px;
    border-bottom: 1px solid var(--line);
}

.sidebar-buttons .btn {
    flex: 1;
}

/* ===== СПИСОК СЛОЁВ ===== */
.layers-list {
    padding: 16px 20px;
    flex: 1;
    overflow-y: auto;
}

.layers-list h4 {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--mut);
    margin: 0 0 12px;
}

.layer-item {
    border: 1px solid var(--line);
    border-radius: 12px;
    margin-bottom: 8px;
    overflow: hidden;
    transition: .2s;
    background: var(--panel);
}

.layer-item.active {
    border-color: rgba(34, 211, 238, 0.4);
    background: rgba(34, 211, 238, 0.05);
}

.layer-item.draggable {
    cursor: grab;
}

.layer-item.draggable:active {
    cursor: grabbing;
}

.layer-item.dragging {
    opacity: 0.5;
    transform: scale(0.98);
}

.layer-main {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    cursor: pointer;
}

.layer-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.layer-icon {
    font-size: 18px;
    width: 24px;
    text-align: center;
}

.layer-name {
    flex: 1;
    font-size: 14px;
    font-weight: 600;
}

.layer-count {
    font-size: 11px;
    color: var(--mut);
    background: var(--bg);
    padding: 2px 8px;
    border-radius: 99px;
}

/* ===== КОНТРОЛЫ СОРТИРОВКИ ===== */
.sort-controls {
    display: flex;
    gap: 4px;
    padding: 0 14px 10px;
}

.sort-controls.mini {
    padding: 0;
    gap: 2px;
}

.sort-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: var(--bg);
    border: 1px solid var(--line);
    color: var(--mut);
    cursor: pointer;
    transition: .2s;
    display: grid;
    place-items: center;
    font-size: 14px;
    font-weight: 700;
}

.sort-btn:hover:not(:disabled) {
    border-color: var(--cyan);
    color: var(--txt);
}

.sort-btn.mini {
    width: 22px;
    height: 22px;
    font-size: 12px;
}

.sort-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.sort-btn.danger:hover {
    border-color: var(--rose);
    color: var(--rose);
}

/* ===== ОБЪЕКТЫ СЛОЯ ===== */
.layer-objects {
    border-top: 1px solid var(--line);
    padding: 8px 14px;
    background: rgba(0, 0, 0, 0.15);
}

.layer-objects.sortable {
    padding: 8px 10px;
}

.object-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    border-radius: 8px;
    font-size: 13px;
    transition: .2s;
}

.object-item:hover {
    background: rgba(255, 255, 255, 0.04);
}

.object-item.draggable {
    cursor: grab;
}

.object-item.draggable:active {
    cursor: grabbing;
}

.object-item.dragging {
    opacity: 0.5;
}

.object-icon {
    font-size: 14px;
}

.object-name {
    flex: 1;
    color: var(--txt);
}

.object-delete {
    background: none;
    border: none;
    color: var(--mut);
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    transition: .2s;
}

.object-delete:hover {
    color: var(--rose);
    background: rgba(255, 92, 122, 0.1);
}

.empty-hint {
    padding: 12px;
    text-align: center;
    color: var(--mut);
    font-size: 12px;
    font-style: italic;
}

/* ===== ФОРМЫ ===== */
.editor-form {
    padding: 16px 20px;
    margin: 12px 20px;
    border-radius: 14px;
    border: 1px solid var(--line);
    background: var(--panel);
}

.editor-form h3 {
    margin: 0 0 14px;
    font-size: 15px;
}

.form-group {
    margin-bottom: 12px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.lbl {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--mut);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 6px;
}

.inp {
    width: 100%;
    padding: 9px 12px;
    background: var(--bg);
    border: 1px solid var(--line);
    border-radius: 10px;
    color: var(--txt);
    font-size: 14px;
    outline: none;
}

.inp:focus {
    border-color: var(--cyan);
}

.mode-switch {
    display: flex;
    gap: 6px;
    background: var(--bg);
    padding: 3px;
    border-radius: 10px;
}

.mode-switch button {
    flex: 1;
    padding: 7px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: var(--mut);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.mode-switch button.on {
    background: var(--cyan);
    color: #000;
}

.hint {
    font-size: 12px;
    color: var(--cyan);
    background: rgba(34, 211, 238, 0.08);
    padding: 8px 10px;
    border-radius: 8px;
    margin: 10px 0;
    line-height: 1.4;
}

.form-actions {
    display: flex;
    gap: 8px;
    margin-top: 16px;
}

.form-actions .btn {
    flex: 1;
}

/* ===== КАРТА ===== */
.maplibre-map {
    width: 100%;
    height: 100%;
}

.map-collapsed-hint {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 12px 24px;
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 12px;
    color: var(--cyan);
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
}

.map-collapsed-hint:hover {
    border-color: var(--cyan);
    background: rgba(34, 211, 238, 0.1);
}

/* ===== МАРКЕРЫ НА КАРТЕ ===== */
:deep(.admin-map-pin) {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--pc);
    border: 2px solid #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    transition: transform 0.2s;
}

:deep(.admin-map-pin:hover) {
    transform: scale(1.2);
    z-index: 10 !important;
}

/* ===== АДАПТИВ ===== */
@media (min-width: 1600px) {
    .editor-sidebar {
        width: 480px;
        min-width: 480px;
    }
    .layer-item {
        margin-bottom: 10px;
    }
    .editor-form {
        margin: 16px 24px;
        padding: 20px 24px;
    }
}

@media (max-width: 1200px) {
    .editor-sidebar {
        width: 360px;
        min-width: 360px;
    }
    .form-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 900px) {
    .map-editor-layout {
        flex-direction: column;
    }
    .editor-sidebar {
        width: 100%;
        min-width: 100%;
        max-height: 50vh;
        border-right: none;
        border-bottom: 1px solid var(--line);
    }
    .editor-map-container {
        flex: 1;
        min-height: 400px;
    }
    .map-editor-layout.map-collapsed .editor-sidebar {
        max-height: none;
    }
}
</style>
