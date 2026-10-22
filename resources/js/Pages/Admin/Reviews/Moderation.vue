<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'

const props = defineProps(['reviews', 'stats', 'filters'])

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || 'pending')
const showRejectModal = ref(false)
const currentReview = ref(null)
const rejectComment = ref('')

const applyFilters = () => {
    router.get('/admin/reviews/moderation', { status: status.value, search: search.value }, { preserveScroll: true })
}

const approve = (id) => {
    router.post(`/admin/reviews/${id}/approve`, {}, { preserveScroll: true })
}

const openReject = (review) => {
    currentReview.value = review
    rejectComment.value = ''
    showRejectModal.value = true
}

const submitReject = () => {
    router.post(`/admin/reviews/${currentReview.value.id}/reject`, { comment: rejectComment.value }, {
        preserveScroll: true,
        onSuccess: () => showRejectModal.value = false
    })
}

const remove = (id) => {
    if (!confirm('Удалить этот отзыв навсегда? Это действие нельзя отменить.')) return
    router.delete(`/admin/reviews/${id}`, { preserveScroll: true })
}

const statusBadge = {
    pending: { label: '⏳ На модерации', color: 'yellow' },
    approved: { label: '✅ Одобрен', color: 'lime' },
    rejected: { label: '❌ Отклонен', color: 'rose' },
}

const fmtDate = (d) => new Date(d).toLocaleDateString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
})
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>⭐ Модерация отзывов</h1>
        </div>

        <!-- Статистика -->
        <div class="grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
            <div class="vcard" style="text-align:center">
                <b style="color:var(--yellow);font-size:28px;font-family:var(--disp)">{{ stats.pending }}</b>
                <span style="color:var(--mut);font-size:13px">Ожидают проверки</span>
            </div>
            <div class="vcard" style="text-align:center">
                <b style="color:var(--lime);font-size:28px;font-family:var(--disp)">{{ stats.approved }}</b>
                <span style="color:var(--mut);font-size:13px">Опубликовано</span>
            </div>
            <div class="vcard" style="text-align:center">
                <b style="color:var(--rose);font-size:28px;font-family:var(--disp)">{{ stats.rejected }}</b>
                <span style="color:var(--mut);font-size:13px">Отклонено</span>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="vcard" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
            <input v-model="search" @keyup.enter="applyFilters" class="inp" placeholder="Поиск по тексту, имени или заведению..." style="max-width:320px">
            <select v-model="status" @change="applyFilters" class="inp" style="max-width:200px">
                <option value="pending">⏳ На модерации</option>
                <option value="approved">✅ Одобренные</option>
                <option value="rejected">❌ Отклоненные</option>
            </select>
            <VButton variant="ghost" @click="applyFilters">Применить</VButton>
        </div>

        <!-- Таблица -->
        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead>
                <tr>
                    <th style="width:180px">Заведение</th>
                    <th style="width:140px">Автор</th>
                    <th style="width:80px">Оценка</th>
                    <th>Текст отзыва</th>
                    <th style="width:120px">Дата</th>
                    <th style="width:160px">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="r in reviews.data" :key="r.id">
                    <td>
                        <b style="font-size:14px">{{ r.place?.name || 'Удалено' }}</b>
                        <div v-if="r.reports?.length > 0" style="margin-top:4px">
                                <span style="font-size:11px;color:var(--rose);background:rgba(255,92,122,0.1);padding:2px 6px;border-radius:4px">
                                    ⚠️ {{ r.reports.length }} жалоб(ы)
                                </span>
                        </div>
                    </td>
                    <td>
                        <b>{{ r.user?.name || 'Аноним' }}</b>
                    </td>
                    <td>
                            <span style="color:var(--yellow);font-size:16px;letter-spacing:1px">
                                {{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}
                            </span>
                    </td>
                    <td>
                        <div style="font-size:14px;line-height:1.4;max-width:300px">
                            {{ r.content }}
                        </div>
                        <div v-if="r.status === 'rejected' && r.moderator_comment" style="margin-top:6px;font-size:12px;color:var(--rose)">
                            Причина: {{ r.moderator_comment }}
                        </div>
                    </td>
                    <td style="font-size:12px;color:var(--mut);white-space:nowrap">
                        {{ fmtDate(r.created_at) }}
                    </td>
                    <td>
                        <div style="display:flex;flex-direction:column;gap:6px">
                            <div v-if="r.status === 'pending'" style="display:flex;gap:6px">
                                <VButton size="sm" style="background:var(--lime);color:#000" @click="approve(r.id)">✓</VButton>
                                <VButton size="sm" variant="ghost" style="color:var(--yellow);border-color:var(--yellow)" @click="openReject(r)">✕</VButton>
                            </div>
                            <VButton v-if="r.status !== 'pending'" size="sm" variant="ghost" style="color:var(--rose);border-color:var(--rose)" @click="remove(r.id)">
                                🗑 Удалить
                            </VButton>
                        </div>
                    </td>
                </tr>
                <tr v-if="!reviews.data.length">
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--mut)">
                        Отзывов с таким статусом не найдено
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

        <!-- Модалка отклонения -->
        <div v-if="showRejectModal" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center" @click.self="showRejectModal=false">
            <div class="vcard" style="width:420px">
                <h3 style="margin-top:0">Отклонить отзыв</h3>
                <p style="color:var(--mut);font-size:14px;margin:10px 0 20px">
                    Укажите причину, чтобы автор понимал, почему отзыв не опубликован.
                </p>
                <textarea v-model="rejectComment" class="inp" rows="3" placeholder="Например: Содержит нецензурную лексику или недостоверную информацию" style="margin-bottom:16px"></textarea>
                <div style="display:flex;gap:10px">
                    <VButton variant="ghost" @click="showRejectModal=false" style="flex:1">Отмена</VButton>
                    <VButton style="flex:1;background:var(--rose)" @click="submitReject" :disabled="!rejectComment.trim()">Отклонить</VButton>
                </div>
            </div>
        </div>
    </div>
</template>
