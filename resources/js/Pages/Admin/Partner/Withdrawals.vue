<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'

const props = defineProps(['withdrawals', 'counts'])
const showRejectModal = ref(false)
const currentWithdrawal = ref(null)
const rejectComment = ref('')

const approve = (id) => router.post(`/admin/partner/withdrawals/${id}/approve`)

const openReject = (w) => {
    currentWithdrawal.value = w
    rejectComment.value = ''
    showRejectModal.value = true
}

const submitReject = () => {
    router.post(`/admin/partner/withdrawals/${currentWithdrawal.value.id}/reject`, { comment: rejectComment.value }, {
        onSuccess: () => showRejectModal.value = false
    })
}

const statusBadge = {
    pending: { label: '⏳ Ожидает', color: 'yellow' },
    approved: { label: '✅ Подтвержден', color: 'lime' },
    rejected: { label: '❌ Отклонен', color: 'rose' },
}
const fmt = n => new Intl.NumberFormat('ru-RU').format(n) + ' ₽'
</script>

<template>
    <div>
        <div class="pg-head"><h1>💸 Заявки на вывод</h1></div>

        <div class="grid grid--3" style="margin-bottom:20px">
            <div class="vcard stat"><b style="color:var(--yellow)">{{ counts.pending }}</b><span>Ожидают</span></div>
            <div class="vcard stat"><b style="color:var(--lime)">{{ counts.approved }}</b><span>Подтверждено</span></div>
            <div class="vcard stat"><b style="color:var(--rose)">{{ counts.rejected }}</b><span>Отклонено</span></div>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Партнер</th><th>Сумма</th><th>Реквизиты</th><th>Статус</th><th>Дата</th><th>Действия</th></tr></thead>
                <tbody>
                <tr v-for="w in withdrawals.data" :key="w.id">
                    <td>
                        <b>{{ w.user?.name }}</b><br><small style="color:var(--mut)">{{ w.user?.email }}</small>
                    </td>
                    <td><b style="color:var(--cyan)">{{ fmt(w.amount) }}</b></td>
                    <td style="max-width:200px;font-size:13px">{{ w.payment_details }}</td>
                    <td><VBadge v-bind="statusBadge[w.status]" /></td>
                    <td style="font-size:12px;color:var(--mut)">{{ new Date(w.created_at).toLocaleString('ru-RU') }}</td>
                    <td>
                        <div v-if="w.status === 'pending'" style="display:flex;gap:8px">
                            <VButton size="sm" @click="approve(w.id)">✓</VButton>
                            <VButton size="sm" variant="ghost" style="color:var(--rose);border-color:var(--rose)" @click="openReject(w)">✕</VButton>
                        </div>
                        <small v-else-if="w.admin_comment" style="color:var(--mut)">{{ w.admin_comment }}</small>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Модалка отклонения -->
        <div v-if="showRejectModal" class="modal open" @click.self="showRejectModal=false">
            <div class="m-box" style="max-width:400px">
                <h3>Отклонить вывод</h3>
                <p style="color:var(--mut);font-size:14px;margin:10px 0 20px">Средства будут возвращены на баланс партнера.</p>
                <textarea v-model="rejectComment" class="f-in" rows="3" placeholder="Причина отклонения (обязательно)"></textarea>
                <div style="display:flex;gap:10px;margin-top:20px">
                    <VButton variant="ghost" @click="showRejectModal=false">Отмена</VButton>
                    <VButton style="background:var(--rose)" @click="submitReject" :disabled="!rejectComment">Отклонить</VButton>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.stat { text-align:center; padding:20px; }
.stat b { font-family:var(--disp); font-size:28px; font-weight:800; display:block; margin-bottom:4px; }
.stat span { color:var(--mut); font-size:13px; }
</style>
