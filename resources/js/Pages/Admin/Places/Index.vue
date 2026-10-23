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

/* ---------- создание тенанта ---------- */
const tenantModalPlace = ref(null)
// Используем обычный ref для формы, чтобы легко сбрасывать, или useForm
const tenantForm = useForm({ slug: '', name: '' })
const tenantResult = ref(null) // Для хранения данных админа после успеха


const openTenantModal = (p) => {
    tenantModalPlace.value = p
    tenantResult.value = null

    const baseSlug = p.slug ? p.slug.replace(/[^a-z0-9-]/gi, '') : p.name.toLowerCase().replace(/[^a-z0-9-]/gi, '-')
    tenantForm.slug = baseSlug || 'new-place'
    tenantForm.name = p.name
}

const submitCreateTenantAxios = async () => {
    try {
        tenantForm.processing = true

        // 🚀 Собираем все данные места для отправки на бэкенд
        const p = tenantModalPlace.value
        const payload = {
            slug: tenantForm.slug,
            name: tenantForm.name,
            description: p.description,
            short_description: p.short_description,
            address: p.address,
            phone: p.phone,
            site: p.site,
            lat: p.lat,
            lng: p.lng,
            working_hours: p.working_hours,
        }

        const response = await axios.post(`/admin/places/${p.id}/create-tenant`, payload)

        if (response.data.success) {
            tenantResult.value = response.data
            router.reload({ only: ['places'], preserveScroll: true })
        }
    } catch (error) {
        console.error('Ошибка создания:', error)
        tenantForm.errors.slug = error.response?.data?.message || 'Ошибка сервера'
    } finally {
        tenantForm.processing = false
    }
}

const closeTenantModal = () => {
    tenantModalPlace.value = null
    tenantResult.value = null
    tenantForm.reset()
    tenantForm.clearErrors()
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

        <!-- Фильтры (без изменений) -->
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
                <thead>
                <tr>
                    <th>Название</th>
                    <th>Категория</th>
                    <th>Район</th>
                    <th>★</th>
                    <th>Статус</th>
                    <th>Тенант</th> <!-- Новая колонка -->
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="p in places.data" :key="p.id">
                    <td>
                        <b>{{ p.name }}</b>{{ p.is_featured ? ' ✨' : '' }}
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

                    <!-- Новая колонка: Индикатор тенанта -->
                    <td>
                        <span
                            v-if="p.external_id"
                            class="tenant-badge"
                            :title="`✅ Тенант привязан\n🆔 ID: ${p.external_id}\n🔗 Slug: ${p.tenant?.slug || 'не загружен'}`"
                        >
                            ✅
                        </span>
                        <span
                            v-else
                            class="vbadge vbadge--warn"
                            style="background:rgba(251,191,36,.15); color:#f59e0b; border:1px solid rgba(251,191,36,.3); font-size: 11px;"
                        >
                            ⚠️ Нет
                        </span>
                    </td>

                    <td style="text-align:right;white-space:nowrap">
                        <AdminPlaceMenu :place="p" />
                        <Link class="tlink" :href="`/admin/places/${p.id}/analytics`">📈</Link>
                        <Link class="tlink" :href="`/admin/places/${p.id}/edit`">✏️</Link>
                        <button v-if="auth.can('manage ads')" class="tlink" title="Спецразмещение" @click="toggleFeatured(p)">✨</button>

                        <!-- Кнопка создания тенанта (показывается только если external_id пуст) -->
                        <button
                            v-if="!p.external_id"
                            class="tlink tlink--add"
                            title="Создать тенант для этого места"
                            @click="openTenantModal(p)"
                        >
                            🏢
                        </button>

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

        <!-- Модалка рейтинга (без изменений) -->
        <VModal :show="!!ratingModal" :title="`Рейтинг: ${ratingModal?.name ?? ''}`" @close="ratingModal = null">
            <!-- ... содержимое модалки рейтинга ... -->
        </VModal>

        <VModal
            :show="!!tenantModalPlace"
            :title="`Создание тенанта: ${tenantModalPlace?.name ?? ''}`"
            @close="closeTenantModal"
        >
            <div v-if="tenantModalPlace">

                <!-- Блок успешного создания (без изменений) -->
                <div v-if="tenantResult" class="success-box">

                </div>

                <!-- Форма создания -->
                <div v-else>
                    <p style="margin-top:0;color:var(--mut);font-size:13px">
                        Будет создан новый тенант с ролью <b>super_admin</b>.
                        Система автоматически перенесет данные из карточки заведения.
                    </p>

                    <!-- 🆕 Блок предпросмотра переносимых данных -->
                    <div class="migration-preview">
                        <div class="migration-preview__title">📦 Будет перенесено:</div>
                        <ul class="migration-preview__list">
                            <li v-if="tenantModalPlace.description">✅ Полное описание</li>
                            <li v-if="tenantModalPlace.short_description">✅ Краткое описание</li>
                            <li v-if="tenantModalPlace.address">✅ Адрес: <b>{{ tenantModalPlace.address }}</b></li>
                            <li v-if="tenantModalPlace.phone">✅ Телефон: <b>{{ tenantModalPlace.phone }}</b></li>
                            <li v-if="tenantModalPlace.site">✅ Сайт: <b>{{ tenantModalPlace.site }}</b></li>
                            <li v-if="tenantModalPlace.lat && tenantModalPlace.lng">✅ Координаты: <b>{{ tenantModalPlace.lat }}, {{ tenantModalPlace.lng }}</b></li>
                            <li v-if="tenantModalPlace.working_hours">✅ Часы работы</li>
                        </ul>
                        <p v-if="!tenantModalPlace.description && !tenantModalPlace.address && !tenantModalPlace.phone" style="color:var(--rose); font-size:12px; margin:8px 0 0">
                            ⚠️ В карточке заведения мало данных. Тенант будет создан с настройками по умолчанию.
                        </p>
                    </div>

                    <label class="lbl">Slug (латиница, цифры, дефис)</label>
                    <input
                        v-model="tenantForm.slug"
                        type="text"
                        class="inp"
                        :class="{ 'inp--error': tenantForm.errors.slug }"
                        placeholder="например: donmak-uj"
                    >
                    <p v-if="tenantForm.errors.slug" class="err">{{ tenantForm.errors.slug }}</p>

                    <label class="lbl" style="margin-top:14px">Название тенанта</label>
                    <input
                        v-model="tenantForm.name"
                        type="text"
                        class="inp"
                        :class="{ 'inp--error': tenantForm.errors.name }"
                    >
                    <p v-if="tenantForm.errors.name" class="err">{{ tenantForm.errors.name }}</p>

                    <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap">
                        <VButton :busy="tenantForm.processing" @click="submitCreateTenantAxios">
                            🚀 Создать и перенести данные
                        </VButton>
                        <VButton variant="ghost" @click="closeTenantModal">Отмена</VButton>
                    </div>
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


 .tlink--add {
     color: #22c55e; /* Зеленый цвет для кнопки создания */
 }
.tlink--add:hover {
    opacity: 1;
    transform: scale(1.1);
}

.inp--error {
    border-color: var(--rose, #fb7185) !important;
}

.success-box {
    background: rgba(34, 197, 94, 0.08);
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 12px;
    padding: 16px;
}

.creds-box {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 12px;
    margin: 12px 0;
    font-size: 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.creds-box code {
    background: var(--panel);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    color: var(--cyan);
    user-select: all; /* Позволяет легко выделить и скопировать */
}

.migration-preview {
    background: rgba(59, 130, 246, 0.06);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 18px;
}

.migration-preview__title {
    font-weight: 700;
    font-size: 13px;
    color: var(--cyan, #3b82f6);
    margin-bottom: 8px;
}

.migration-preview__list {
    margin: 0;
    padding-left: 20px;
    font-size: 13px;
    color: var(--text);
    line-height: 1.6;
}

.migration-preview__list li {
    margin-bottom: 4px;
}

/* ===== Индикатор привязки тенанта ===== */
.tenant-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 6px;
    font-size: 16px;
    cursor: help; /* Показывает курсор со знаком вопроса при наведении */
    transition: all 0.2s ease;
}

.tenant-badge:hover {
    background: rgba(34, 197, 94, 0.25);
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.2);
}
</style>

