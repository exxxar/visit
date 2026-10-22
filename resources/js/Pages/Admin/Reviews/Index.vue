<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'

const props = defineProps(['reviews', 'stats', 'filters'])

const filters = ref({
    rating: props.filters.rating || '',
    public: props.filters.public || '',
    search: props.filters.search || '',
})

const applyFilters = () => {
    router.get('/admin/reviews', filters.value, { preserveScroll: true })
}

const togglePublic = (id) => {
    router.post(`/admin/reviews/${id}/toggle`, {}, { preserveScroll: true })
}

const verify = (id) => {
    router.post(`/admin/reviews/${id}/verify`, {}, { preserveScroll: true })
}

const remove = (id) => {
    if (!confirm('Удалить отзыв навсегда? Это действие необратимо.')) return
    router.delete(`/admin/reviews/${id}`, { preserveScroll: true })
}

const stars = (rating) => '★'.repeat(rating) + '☆'.repeat(5 - rating)

const fmtDate = (d) => new Date(d).toLocaleDateString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric'
})
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>⭐ Отзывы партнёров</h1>
        </div>

        <!-- Статистика -->
        <div class="grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
            <div class="vcard" style="text-align:center">
                <b style="color:var(--cyan);font-size:28px;font-family:var(--disp)">{{ stats.total }}</b>
                <span style="color:var(--mut);font-size:13px">всего отзывов</span>
            </div>
            <div class="vcard" style="text-align:center">
                <b style="color:var(--lime);font-size:28px;font-family:var(--disp)">{{ stats.public }}</b>
                <span style="color:var(--mut);font-size:13px">опубликовано</span>
            </div>
            <div class="vcard" style="text-align:center">
                <b style="color:var(--yellow);font-size:28px;font-family:var(--disp)">{{ stats.hidden }}</b>
                <span style="color:var(--mut);font-size:13px">скрыто</span>
            </div>
            <div class="vcard" style="text-align:center">
                <b style="color:var(--magenta);font-size:28px;font-family:var(--disp)">{{ stats.avg }}</b>
                <span style="color:var(--mut);font-size:13px">средний рейтинг</span>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="vcard" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
            <input v-model="filters.search" @keyup.enter="applyFilters" class="inp" placeholder="Поиск по имени или комментарию..." style="max-width:300px">
            <select v-model="filters.rating" @change="applyFilters" class="inp" style="max-width:150px">
                <option value="">Все оценки</option>
                <option value="5">★★★★★ (5)</option>
                <option value="4">★★★★☆ (4)</option>
                <option value="3">★★★☆☆ (3)</option>
                <option value="2">★★☆☆☆ (2)</option>
                <option value="1">★☆☆☆☆ (1)</option>
            </select>
            <select v-model="filters.public" @change="applyFilters" class="inp" style="max-width:180px">
                <option value="">Все статусы</option>
                <option value="yes">Опубликованные</option>
                <option value="no">Скрытые</option>
            </select>
            <VButton variant="ghost" @click="applyFilters">Применить</VButton>
        </div>

        <!-- Таблица отзывов -->
        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead>
                <tr>
                    <th>Клиент</th>
                    <th>Партнёр</th>
                    <th>Заявка</th>
                    <th>Оценка</th>
                    <th>Отзыв</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="r in reviews.data" :key="r.id">
                    <td>
                        <b>{{ r.client?.name }}</b><br>
                        <small style="color:var(--mut)">{{ r.client?.email }}</small>
                    </td>
                    <td>
                        <b>{{ r.partner?.name }}</b><br>
                        <small style="color:var(--mut)">Рейтинг: {{ r.partner?.partner_rating?.toFixed(1) }}</small>
                    </td>
                    <td style="font-size:13px">{{ r.reviewable?.title || '—' }}</td>
                    <td>
                            <span style="color:var(--yellow);font-size:16px;letter-spacing:2px">
                                {{ stars(r.rating) }}
                            </span>
                    </td>
                    <td style="max-width:250px">
                        <div style="font-size:13px;line-height:1.4">
                            {{ r.comment || '—' }}
                        </div>
                        <div v-if="r.is_verified" style="margin-top:4px">
                            <VBadge label="✓ Проверено" color="lime" />
                        </div>
                        <div v-if="r.admin_note" style="margin-top:6px;font-size:11px;color:var(--mut)">
                            💬 {{ r.admin_note }}
                        </div>
                    </td>
                    <td style="font-size:12px;color:var(--mut);white-space:nowrap">
                        {{ fmtDate(r.created_at) }}
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <VButton size="sm" variant="ghost" @click="togglePublic(r.id)">
                                {{ r.is_public ? '🙈 Скрыть' : '👁 Показать' }}
                            </VButton>
                            <VButton size="sm" variant="ghost" @click="verify(r.id)">
                                {{ r.is_verified ? '✓ Проверено' : '○ Проверить' }}
                            </VButton>
                            <VButton size="sm" variant="ghost" style="color:var(--rose)" @click="remove(r.id)">
                                🗑
                            </VButton>
                        </div>
                    </td>
                </tr>
                <tr v-if="!reviews.data.length">
                    <td colspan="7" style="text-align:center;padding:40px;color:var(--mut)">
                        Отзывов пока нет
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        <div class="pager" style="margin-top:20px">
            <Link v-for="l in reviews.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>
