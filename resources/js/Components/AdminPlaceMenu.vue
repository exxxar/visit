<script setup>
import { ref } from 'vue'
import VButton from '@/Components/ui/VButton.vue'

const props = defineProps({ place: Object })
const show = ref(false)
const loading = ref(false)
const items = ref([])
const query = ref('')
const loaded = ref(false)

const open = async () => {
    show.value = true
    if (loaded.value) return
    loading.value = true
    try {
        const r = await fetch(`/api/v1/places/${props.place.id}/menu`, {
            headers: { Accept: 'application/json' },
        })
        const data = await r.json()
        items.value = data.data ?? []
        loaded.value = true
    } catch (e) {
        items.value = []
    } finally {
        loading.value = false
    }
}

const close = () => {
    show.value = false
    query.value = ''
}

const filtered = () => {
    const q = query.value.trim().toLowerCase()
    if (!q) return items.value
    return items.value.filter(it =>
        (it.name || '').toLowerCase().includes(q) ||
        (it.description || '').toLowerCase().includes(q) ||
        (it.category || '').toLowerCase().includes(q)
    )
}

const grouped = () => {
    const map = {}
    filtered().forEach(it => {
        const cat = it.category || 'Без категории'
        if (!map[cat]) map[cat] = []
        map[cat].push(it)
    })
    return map
}

const fmtPrice = (p) => typeof p === 'number'
    ? new Intl.NumberFormat('ru-RU').format(p) + ' ₽'
    : '—'

const cleanDesc = (d) => (d || '')
    .replace(/Цена:\s*\d+\s*(?:руб(?:лей)?|₽)\.?/gi, '')
    .replace(/Вес:\s*\d+\s*(?:грамм|г|гр|мл|ml)[^.]*/gi, '')
    .replace(/#[a-zA-Zа-яА-ЯёЁ]+/g, '')
    .trim()
</script>

<template>
    <span>
        <button
            v-if="place.external_source === 'mypwa.ru'"
            class="tlink"
            title="Просмотр меню / прайс-листа"
            @click="open"
        >📋</button>

        <Teleport to="body">
            <Transition name="mfade">
                <div v-if="show" class="vmodal" @click.self="close">
                    <div class="vmodal__box vmodal__box--wide">
                        <div class="vmodal__head">
                            <div>
                                <h3>📋 {{ place.name }}</h3>
                                <div class="vmodal__sub">
                                    {{ items.length }} позиций
                                    <span v-if="place.external_id" class="vbadge vbadge--mypwa">⇅ mypwa</span>
                                </div>
                            </div>
                            <button class="vmodal__x" @click="close">✕</button>
                        </div>

                        <div class="vmodal__body">
                            <div class="pm-search">
                                <input
                                    v-model="query"
                                    class="inp"
                                    placeholder="Поиск по меню…"
                                >
                            </div>

                            <div v-if="loading" class="pm-state">
                                <div class="pm-spinner"></div>
                                <p>Загружаем меню…</p>
                            </div>

                            <div v-else-if="!items.length" class="pm-state">
                                <div class="pm-ico">📋</div>
                                <h4>Меню пока не добавлено</h4>
                                <p>Владелец заведения скоро его опубликует</p>
                            </div>

                            <div v-else-if="!filtered().length" class="pm-state">
                                <div class="pm-ico">🔍</div>
                                <h4>Ничего не найдено</h4>
                            </div>

                            <div v-else class="pm-groups">
                                <section
                                    v-for="(list, cat) in grouped()"
                                    :key="cat"
                                    class="pm-group"
                                >
                                    <h4 class="pm-group__title">{{ cat }}</h4>
                                    <div class="pm-grid">
                                        <article
                                            v-for="it in list"
                                            :key="it.id"
                                            class="pm-item"
                                        >
                                            <div v-if="it.image" class="pm-item__img">
                                                <img
                                                    :src="it.image"
                                                    :alt="it.name"
                                                    loading="lazy"
                                                    @error="$event.target.parentElement.style.display='none'"
                                                >
                                            </div>
                                            <div v-else class="pm-item__img pm-item__img--empty">🍽</div>

                                            <div class="pm-item__body">
                                                <h5 class="pm-item__name">{{ it.name }}</h5>
                                                <p v-if="cleanDesc(it.description)" class="pm-item__desc">
                                                    {{ cleanDesc(it.description) }}
                                                </p>
                                                <div class="pm-item__foot">
                                                    <span class="pm-item__price">{{ fmtPrice(it.price) }}</span>
                                                    <span v-if="it.weight" class="pm-item__weight">{{ it.weight }}</span>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </span>
</template>

<style scoped>
.vmodal {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.65); backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.vmodal__box {
    background: var(--panel, #141926);
    border: 1px solid var(--line, rgba(255,255,255,.1));
    border-radius: 18px; max-width: 960px; width: 100%;
    max-height: 90vh; overflow: hidden;
    display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,.4);
}
.vmodal__head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 24px; border-bottom: 1px solid var(--line);
}
.vmodal__head h3 { margin: 0 0 4px; font-size: 18px; font-weight: 700; }
.vmodal__sub { color: var(--mut); font-size: 13px; display: flex; align-items: center; gap: 8px; }
.vmodal__x {
    background: none; border: none; color: var(--mut);
    font-size: 20px; cursor: pointer; width: 32px; height: 32px;
    border-radius: 8px;
}
.vmodal__x:hover { background: rgba(255,255,255,.05); color: var(--rose, #ff5c7a); }

.vmodal__body { padding: 20px 24px; overflow-y: auto; }

.vbadge--mypwa {
    background: rgba(34,211,238,.15); color: #22d3ee;
    padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700;
    border: 1px solid rgba(34,211,238,.3);
}

.pm-search { margin-bottom: 16px; }

.pm-state { text-align: center; padding: 40px 20px; color: var(--mut); }
.pm-state h4 { margin: 10px 0 4px; color: var(--txt); font-size: 16px; }
.pm-ico { font-size: 42px; opacity: .6; }

.pm-spinner {
    width: 32px; height: 32px; margin: 0 auto 12px;
    border: 3px solid var(--line); border-top-color: var(--cyan);
    border-radius: 50%; animation: spin .9s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.pm-groups { display: flex; flex-direction: column; gap: 24px; }
.pm-group__title {
    font-family: var(--disp, system-ui);
    font-size: 12px; font-weight: 700; letter-spacing: .15em;
    text-transform: uppercase; color: var(--cyan);
    margin: 0 0 12px; padding-bottom: 6px;
    border-bottom: 1px dashed rgba(34,211,238,.25);
}

.pm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 12px;
}

.pm-item {
    display: flex; gap: 12px; padding: 12px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--line);
    border-radius: 12px; transition: .2s;
}
.pm-item:hover { border-color: var(--cyan); transform: translateY(-1px); }

.pm-item__img {
    width: 68px; height: 68px; flex-shrink: 0;
    border-radius: 10px; overflow: hidden;
    background: linear-gradient(135deg, rgba(34,211,238,.08), rgba(139,92,246,.08));
    display: flex; align-items: center; justify-content: center;
}
.pm-item__img img { width: 100%; height: 100%; object-fit: cover; }
.pm-item__img--empty { font-size: 26px; opacity: .4; }

.pm-item__body {
    flex: 1; display: flex; flex-direction: column; gap: 4px;
    min-width: 0;
}
.pm-item__name {
    font-size: 14px; font-weight: 700; margin: 0;
    color: var(--txt); line-height: 1.3;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden;
}
.pm-item__desc {
    color: var(--mut); font-size: 12px; line-height: 1.4; margin: 0;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden;
}
.pm-item__foot {
    display: flex; align-items: center; gap: 8px;
    margin-top: auto; padding-top: 6px;
}
.pm-item__price {
    font-size: 14px; font-weight: 800; color: var(--cyan);
    font-family: var(--disp, system-ui);
}
.pm-item__weight {
    font-size: 10px; color: var(--mut);
    padding: 2px 7px; background: rgba(34,211,238,.08);
    border-radius: 5px; border: 1px solid rgba(34,211,238,.15);
}

/* анимация */
.mfade-enter-active, .mfade-leave-active { transition: opacity .2s; }
.mfade-enter-from, .mfade-leave-to { opacity: 0; }
</style>
