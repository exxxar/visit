<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import VModal from '@/Components/ui/VModal.vue'
import { useModeration } from '@/composables/useModeration'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['tab', 'items', 'counts'])
const { modal, comment, processing, approve, openModal, submitModal, closeModal } = useModeration()
const { L, badge } = useLabels()

const TABS = [
    ['places', 'Заведения'], ['news', 'Новости'], ['events', 'Афиша'],
    ['reviews', 'Отзывы'], ['applications', 'Заявки'],
]

const title = (i) => i.name ?? i.title ?? i.org_name

const sub = (i) => {
    if (props.tab === 'places') return `${i.category?.name} · ${i.district?.name}`
    if (props.tab === 'reviews') return `${i.place?.name} · ★${i.rating}`
    if (props.tab === 'applications') {
        const parts = []
        if (i.contact_name) parts.push(i.contact_name)
        if (i.contact_phone) parts.push(i.contact_phone)
        if (i.address) parts.push(i.address)
        return parts.join(' · ') || 'Нет контактов'
    }
    return i.place?.name ?? ''
}

/* источник заявки */
const isMypwa = (i) => i.external_source === 'mypwa.ru'

/* обложка для превью */
const cover = (i) => {
    if (props.tab === 'places' && i.cover_url) return '/' + i.cover_url
    if (props.tab === 'news' && i.cover) return i.cover
    if (props.tab === 'events' && i.image) return i.image
    if (props.tab === 'applications' && i.media?.[0]) return '/storage/' + i.media[0]
    return null
}

/* ссылка на Яндекс.Карты для заявок с координатами */
const yandexUrl = (i) => {
    if (!i.lat || !i.lng) return null
    return `https://yandex.ru/maps/?ll=${i.lng},${i.lat}&z=17&text=${encodeURIComponent(i.address || '')}`
}

/* импорт с mypwa */
const importing = ref(false)
const importMypwa = () => {
    if (!confirm('Загрузить новые заведения с mypwa.ru?')) return
    importing.value = true
    router.post('/admin/moderation/import/mypwa', {}, {
        preserveScroll: true,
        onFinish: () => importing.value = false,
    })
}

/* дни в очереди */
const daysInQueue = (i) => {
    const created = new Date(i.created_at)
    const diff = Math.floor((Date.now() - created.getTime()) / 86400000)
    return diff
}

/* форматирование часов работы */
const DAYS_RU = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
const formatHours = (hours) => {
    if (!hours || !Array.isArray(hours)) return null
    return hours.map(h => `${h.d}: ${h.from}–${h.to}`).join(', ')
}

/* детали заявки в модалке */
const detailsModal = ref(null)
const openDetails = (i) => { detailsModal.value = i }
const closeDetails = () => { detailsModal.value = null }
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Модерация</h1>
            <div style="margin-left:auto;display:flex;gap:10px">
                <VButton
                    v-if="tab === 'applications'"
                    :busy="importing"
                    @click="importMypwa"
                    style="background:linear-gradient(135deg,#a3e635,#22d3ee)"
                >
                    ⇅ Загрузить с mypwa.ru
                </VButton>
            </div>
        </div>

        <div class="tabs">
            <Link v-for="[key, label] in TABS" :key="key" :href="`/admin/moderation?tab=${key}`"
                  class="tab" :class="{ on: tab === key }">
                {{ label }}
                <span v-if="counts[key]" class="cnt">{{ counts[key] }}</span>
            </Link>
        </div>

        <div v-if="!items.data.length" class="vcard" style="text-align:center;color:var(--mut);padding:40px">
            ✨ Очередь пуста — всё отмодерировано
        </div>

        <div v-for="i in items.data" :key="i.id" class="vcard mrow">
            <!-- Превью (обложка) -->
            <div v-if="cover(i)" class="mrow__cover">
                <img :src="cover(i)" :alt="title(i)">
            </div>

            <!-- Основная информация -->
            <div class="mrow__main">
                <div class="mrow__head">
                    <b>{{ title(i) }}</b>
                    <span v-if="isMypwa(i)" class="vbadge vbadge--mypwa" title="Импортировано с mypwa.ru">⇅ mypwa</span>
                    <span v-if="daysInQueue(i) > 1" class="mrow__wait" :title="`В очереди ${daysInQueue(i)} дн.`">
                        ⏱ {{ daysInQueue(i) }}д
                    </span>
                </div>

                <span class="mrow__sub">{{ sub(i) }}</span>

                <!-- Рейтинг для отзывов -->
                <div v-if="tab === 'reviews' && i.rating" class="mrow__rate">
                    <span v-for="s in 5" :key="s" :class="{ on: s <= i.rating }">★</span>
                </div>

                <!-- Текст отзыва / описания заявки -->
                <p v-if="tab === 'reviews' && i.text" class="mrow__text">{{ i.text }}</p>
                <p v-if="tab === 'applications' && i.description" class="mrow__text">{{ i.description }}</p>

                <!-- Координаты для заявок -->
                <div v-if="tab === 'applications' && i.lat && i.lng" class="mrow__coords">
                    📍 {{ Number(i.lat).toFixed(4) }}, {{ Number(i.lng).toFixed(4) }}
                    <a v-if="yandexUrl(i)" :href="yandexUrl(i)" target="_blank" rel="nofollow noopener" class="tlink">
                        Открыть на карте →
                    </a>
                </div>

                <!-- Часы работы для заявок -->
                <div v-if="tab === 'applications' && i.working_hours?.length" class="mrow__hours">
                    🕒 {{ formatHours(i.working_hours.slice(0, 3)) }}
                    <span v-if="i.working_hours.length > 3" class="mut"> +ещё {{ i.working_hours.length - 3 }}</span>
                </div>

                <!-- Бейджи статуса -->
                <div class="mrow__badges">
                    <VBadge v-if="tab === 'applications'" v-bind="badge(L.application, i.status)" />
                    <VBadge v-else-if="tab === 'reviews'" v-bind="badge(L.review, i.status)" />
                    <VBadge v-else v-bind="badge(L.moderation, i.status)" />
                </div>
            </div>

            <!-- Действия -->
            <div class="mrow__act">
                <template v-if="!i.place_id">
                    <VButton v-if="tab === 'applications'" size="sm" variant="ghost" @click="openDetails(i)">👁 Детали</VButton>
                    <VButton size="sm" :busy="processing" @click="approve(tab, i.id)">✓ Одобрить</VButton>
                    <VButton size="sm" variant="ghost" @click="openModal(i, tab, 'returned')">↩ На правки</VButton>
                    <VButton size="sm" variant="danger" @click="openModal(i, tab, 'rejected')">✕ Отклонить</VButton>
                </template>
                <template v-else>
                    <Link :href="`/place/${i.place?.slug ?? i.place_id}`" class="tlink">
                        ✓ Заведение опубликовано
                    </Link>
                </template>
            </div>
        </div>

        <div v-if="items.links.length > 3" class="pager">
            <Link v-for="l in items.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>

        <!-- Модалка решения -->
        <VModal :show="!!modal" :title="modal?.action === 'rejected' ? 'Отклонить' : 'Вернуть на правки'" @close="closeModal">
            <p style="margin-top:0;color:var(--mut)">{{ modal?.title }}</p>
            <label class="lbl">Комментарий для владельца</label>
            <textarea v-model="comment" class="inp" rows="4" placeholder="Что нужно исправить…"></textarea>
            <div style="display:flex;gap:10px;margin-top:16px">
                <VButton :busy="processing" @click="submitModal">Сохранить решение</VButton>
                <VButton variant="ghost" @click="closeModal">Отмена</VButton>
            </div>
        </VModal>

        <!-- Модалка деталей заявки -->
        <VModal v-if="detailsModal" :show="true" :title="'Заявка: ' + detailsModal.org_name" @close="closeDetails">
            <div class="det-grid">
                <div class="det-row"><span>Контактное лицо</span><b>{{ detailsModal.contact_name || '—' }}</b></div>
                <div class="det-row"><span>Телефон</span><b>{{ detailsModal.contact_phone || detailsModal.phone || '—' }}</b></div>
                <div class="det-row"><span>E-mail</span><b>{{ detailsModal.contact_email || detailsModal.email || '—' }}</b></div>
                <div class="det-row"><span>Адрес</span><b>{{ detailsModal.address || '—' }}</b></div>
                <div class="det-row"><span>Сайт</span><b>{{ detailsModal.site || '—' }}</b></div>
                <div v-if="detailsModal.lat && detailsModal.lng" class="det-row">
                    <span>Координаты</span>
                    <b>
                        {{ detailsModal.lat }}, {{ detailsModal.lng }}
                        <a v-if="yandexUrl(detailsModal)" :href="yandexUrl(detailsModal)" target="_blank" class="tlink" style="margin-left:8px">карта →</a>
                    </b>
                </div>
                <div class="det-row" style="grid-column:1/-1">
                    <span>Описание</span>
                    <p style="margin:6px 0 0">{{ detailsModal.description || '—' }}</p>
                </div>
                <div v-if="detailsModal.working_hours?.length" class="det-row" style="grid-column:1/-1">
                    <span>Часы работы</span>
                    <div class="det-hours">
                        <div v-for="h in detailsModal.working_hours" :key="h.d" class="det-hour">
                            <b>{{ h.d }}</b><span>{{ h.from }} – {{ h.to }}</span>
                        </div>
                    </div>
                </div>
                <div v-if="detailsModal.socials" class="det-row" style="grid-column:1/-1">
                    <span>Соцсети</span>
                    <pre style="margin:6px 0 0;font-size:12px;color:var(--mut)">{{ JSON.stringify(detailsModal.socials, null, 2) }}</pre>
                </div>
                <div v-if="isMypwa(detailsModal)" class="det-row" style="grid-column:1/-1">
                    <span>Источник</span>
                    <b><span class="vbadge vbadge--mypwa">⇅ mypwa.ru · {{ detailsModal.external_id }}</span></b>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap">
                <VButton :busy="processing" @click="approve('applications', detailsModal.id); closeDetails()">✓ Одобрить</VButton>
                <VButton variant="ghost" @click="openModal(detailsModal, 'applications', 'returned'); closeDetails()">↩ На правки</VButton>
                <VButton variant="danger" @click="openModal(detailsModal, 'applications', 'rejected'); closeDetails()">✕ Отклонить</VButton>
            </div>
        </VModal>
    </div>
</template>

<style scoped>
.mrow{display:flex;gap:16px;align-items:flex-start;margin-bottom:12px;padding:16px 20px;flex-wrap:wrap}
.mrow__cover{width:90px;height:90px;flex-shrink:0;border-radius:14px;overflow:hidden;background:var(--panel);border:1px solid var(--line)}
.mrow__cover img{width:100%;height:100%;object-fit:cover}

.mrow__main{flex:1;min-width:260px}
.mrow__head{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px}
.mrow__head b{font-size:15px}
.mrow__sub{color:var(--mut);font-size:13px;display:block;margin-bottom:6px}
.mrow__wait{font-size:11px;color:var(--orange);font-weight:700;padding:2px 8px;background:rgba(255,138,60,.12);border-radius:6px}

.mrow__rate{display:flex;gap:2px;margin:6px 0;color:#3a3f52;font-size:14px}
.mrow__rate .on{color:#facc15}

.mrow__text{color:var(--mut);font-size:13px;margin:6px 0;line-height:1.5;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}

.mrow__coords{font-size:12.5px;color:var(--mut);margin:6px 0;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.mrow__coords .tlink{font-size:12px}

.mrow__hours{font-size:12.5px;color:var(--mut);margin:4px 0}
.mrow__hours .mut{color:var(--mut);font-size:11px;opacity:.7}

.mrow__badges{margin-top:8px;display:flex;gap:6px;flex-wrap:wrap}

.mrow__act{display:flex;gap:8px;flex-wrap:wrap;align-items:center}

.vbadge--mypwa{
    background:rgba(34,211,238,.15);
    color:#22d3ee;
    padding:2px 8px;
    border-radius:6px;
    font-size:11px;
    font-weight:700;
    border:1px solid rgba(34,211,238,.3);
}

/* модалка деталей заявки */
.det-grid{display:grid;grid-template-columns:130px 1fr;gap:10px 16px;font-size:14px}
.det-row{display:flex;flex-direction:column;gap:2px}
.det-row span{color:var(--mut);font-size:12px}
.det-row b{font-weight:600;color:var(--txt);word-break:break-word}
.det-hours{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:6px;margin-top:6px}
.det-hour{background:var(--panel);border:1px solid var(--line);padding:6px 10px;border-radius:8px;font-size:13px;
    display:flex;justify-content:space-between;gap:8px}
.det-hour b{color:var(--cyan);font-weight:700}

@media(max-width:640px){
    .mrow__cover{width:70px;height:70px}
    .det-grid{grid-template-columns:1fr}
    .mrow__act{width:100%}
    .mrow__act .btn{flex:1}
}
</style>
