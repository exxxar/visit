<script setup>
import { ref, onMounted } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import VModal from '@/Components/ui/VModal.vue'
import { useLabels } from '@/composables/useLabels'
import { useDictionaryStore } from '@/stores/dictionary'
import { useAuth } from '@/composables/useAuth'
import AdminPlaceMenu from '@/Components/AdminPlaceMenu.vue'

const props = defineProps(['places'])
const { L, badge } = useLabels()
const dict = useDictionaryStore()
const auth = useAuth()

const f = ref({ q: '', status: '', district_id: '' })
onMounted(() => dict.load())

const apply = () => router.get('/admin/places', { ...f.value }, { preserveScroll: true })

const toggleFeatured = (p) =>
    router.post(`/admin/places/${p.id}/featured`, {}, { preserveScroll: true })

const destroy = (p) => {
    if (confirm(`Скрыть «${p.name}» с сайта?`)) {
        router.delete(`/admin/places/${p.id}`, { preserveScroll: true })
    }
}

/* ---------- рейтинг ---------- */
const ratingModal = ref(null)
const ratingForm = useForm({ rating: 0, reviews_count: 0 })

const openRating = (p) => {
    ratingModal.value = p
    ratingForm.rating = parseFloat(p.rating) || 0
    ratingForm.reviews_count = parseInt(p.reviews_count) || 0
}

const saveRating = () => {
    ratingForm.put(`/admin/places/${ratingModal.value.id}/rating`, {
        preserveScroll: true,
        onSuccess: () => (ratingModal.value = null),
    })
}

const recalculate = () => {
    router.post(`/admin/places/${ratingModal.value.id}/recalculate-rating`, {}, {
        preserveScroll: true,
        onSuccess: () => (ratingModal.value = null),
    })
}

const renderStars = (r) => {
    const value = Number(r ?? 0)
    const full = Math.floor(value)
    const half = value - full >= 0.5 ? 1 : 0
    const empty = 5 - full - half
    return '★'.repeat(full) + (half ? '⯨' : '') + '☆'.repeat(empty)
}
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Заведения</h1>
            <div style="margin-left:auto;display:flex;gap:10px">
                <Link href="/admin/places/create">
                    <VButton>+ Заведение</VButton>
                </Link>
            </div>
        </div>

        <div class="vcard" style="margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap">
            <input v-model="f.q" class="inp" style="max-width:260px" placeholder="Поиск по названию…" @keyup.enter="apply">
            <select v-model="f.status" class="inp" style="max-width:200px" @change="apply">
                <option value="">Все статусы</option>
                <option v-for="(m, k) in L.moderation" :key="k" :value="k">{{ m.label }}</option>
            </select>
            <select v-model="f.district_id" class="inp" style="max-width:200px" @change="apply">
                <option value="">Все районы</option>
                <option v-for="d in dict.districts ?? []" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <VButton variant="ghost" @click="apply">Применить</VButton>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Название</th><th>Категория</th><th>Район</th><th>★</th><th>Статус</th><th></th></tr></thead>
                <tbody>
                <tr v-for="p in places.data" :key="p.id">
                    <td>
                        <b>{{ p.name }}</b>{{ p.is_featured ? ' ✨' : '' }}
                        <span v-if="p.external_source === 'mypwa.ru'" class="vbadge vbadge--mypwa" style="margin-left:6px">⇅</span>
                    </td>
                    <td>{{ p.category?.icon }} {{ p.category?.name }}</td>
                    <td style="color:var(--mut)">{{ p.district?.name }}</td>
                    <td>
                        <button class="rating-cell" title="Изменить рейтинг" @click="openRating(p)">
                            <span class="rating-cell__stars">{{ renderStars(p.rating ?? 0) }}</span>
                            <b class="rating-cell__num">{{ Number(p.rating ?? 0).toFixed(1) }}</b>
                            <span class="rating-cell__cnt">({{ p.reviews_count ?? 0 }})</span>
                        </button>
                    </td>
                    <td><VBadge v-bind="badge(L.moderation, p.status)" /></td>
                    <td style="text-align:right;white-space:nowrap">
                        <AdminPlaceMenu :place="p" />
                        <Link class="tlink" :href="`/admin/places/${p.id}/analytics`">📈</Link>
                        <Link class="tlink" :href="`/admin/places/${p.id}/edit`">✏️</Link>
                        <button v-if="auth.can('manage ads')" class="tlink" title="Спецразмещение" @click="toggleFeatured(p)">✨</button>
                        <button class="tlink tlink--del" title="Удалить" @click="destroy(p)">🗑</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="pager">
            <Link v-for="l in places.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>

        <!-- модалка рейтинга -->
        <VModal
            :show="!!ratingModal"
            :title="`Рейтинг: ${ratingModal?.name ?? ''}`"
            @close="ratingModal = null"
        >
            <div v-if="ratingModal">
                <p style="margin-top:0;color:var(--mut);font-size:13px">
                    Текущий рейтинг рассчитывается из одобренных отзывов.
                    Можно задать вручную или пересчитать автоматически.
                </p>

                <div class="rating-preview">
                    <div class="rating-preview__stars">
                        {{ renderStars(ratingForm.rating) }}
                    </div>
                    <div class="rating-preview__num">{{ ratingForm.rating.toFixed(1) }}</div>
                </div>

                <label class="lbl">Рейтинг (0–5)</label>
                <input
                    v-model.number="ratingForm.rating"
                    type="range"
                    min="0"
                    max="5"
                    step="0.1"
                    class="rating-range"
                >
                <input
                    v-model.number="ratingForm.rating"
                    type="number"
                    min="0"
                    max="5"
                    step="0.1"
                    class="inp"
                    style="max-width:120px"
                >
                <p v-if="ratingForm.errors.rating" class="err">{{ ratingForm.errors.rating }}</p>

                <label class="lbl" style="margin-top:14px">Количество отзывов</label>
                <input v-model.number="ratingForm.reviews_count" type="number" min="0" class="inp" style="max-width:150px">
                <p v-if="ratingForm.errors.reviews_count" class="err">{{ ratingForm.errors.reviews_count }}</p>

                <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap">
                    <VButton :busy="ratingForm.processing" @click="saveRating">Сохранить</VButton>
                    <VButton variant="ghost" @click="recalculate">↻ Пересчитать из отзывов</VButton>
                    <VButton variant="ghost" @click="ratingModal = null">Отмена</VButton>
                </div>
            </div>
        </VModal>
    </div>
</template>

<style scoped>
.tlink{margin-left:8px;opacity:.75;transition:.2s;background:none;border:none;cursor:pointer;font-size:14px}
.tlink:hover{opacity:1}
.tlink--del:hover{filter:hue-rotate(90deg)}
.err{color:var(--rose,#fb7185);font-size:12px;margin-top:4px}

.vbadge--mypwa{
    background:rgba(34,211,238,.15);color:#22d3ee;
    padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;
    border:1px solid rgba(34,211,238,.3);
}

/* ===== ячейка рейтинга ===== */
.rating-cell{
    display:inline-flex;align-items:center;gap:4px;
    background:none;border:none;cursor:pointer;
    padding:4px 10px;border-radius:10px;
    transition:.2s;font-size:13px;
}
.rating-cell:hover{
    background:var(--panel);
    box-shadow:0 0 0 1px var(--line);
}
.rating-cell__stars{color:#facc15;letter-spacing:1px;font-size:14px}
.rating-cell__num{font-weight:800;color:var(--lime);font-size:14px;margin-left:4px}
.rating-cell__cnt{color:var(--mut);font-size:11px;margin-left:2px}

/* ===== модалка рейтинга ===== */
.rating-preview{
    display:flex;align-items:baseline;gap:14px;
    padding:18px 20px;
    background:linear-gradient(135deg,rgba(34,211,238,.08),rgba(250,204,21,.08));
    border:1px solid rgba(250,204,21,.2);
    border-radius:14px;margin-bottom:16px;
}
.rating-preview__stars{
    font-size:26px;color:#facc15;letter-spacing:2px;
}
.rating-preview__num{
    font-family:var(--disp);
    font-size:32px;font-weight:800;color:var(--lime);
}

.rating-range{
    width:100%;margin:8px 0 10px;
    accent-color:var(--cyan);
}
</style>
