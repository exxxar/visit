<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import VBadge from '@/Components/ui/VBadge.vue'

const props = defineProps(['promotions', 'developments', 'activeType'])

const changeStatus = (type, id, status) => {
    router.post(`/admin/partner/requests/${id}/status`, { type, status }, { preserveScroll: true })
}

const statusBadge = {
    new: { label: '🆕 Новая', color: 'cyan' },
    claimed: { label: '👋 Взята', color: 'yellow' },
    in_progress: { label: '⚙️ В работе', color: 'violet' },
    completed: { label: '✅ Завершена', color: 'lime' },
    cancelled: { label: '❌ Отменена', color: 'rose' },
}
</script>

<template>
    <div>
        <div class="pg-head"><h1>📦 Заявки партнеров</h1></div>

        <div class="tabs" style="margin-bottom:20px">
            <a :href="`/admin/partner/requests?type=all`" class="tab" :class="{ on: activeType === 'all' }">Все</a>
            <a :href="`/admin/partner/requests?type=promotion`" class="tab" :class="{ on: activeType === 'promotion' }">Продвижение</a>
            <a :href="`/admin/partner/requests?type=development`" class="tab" :class="{ on: activeType === 'development' }">Разработка</a>
        </div>

        <div v-if="activeType !== 'development'" class="vcard" style="margin-bottom:24px;padding:0;overflow:hidden">
            <div style="padding:14px 20px;background:var(--panel);border-bottom:1px solid var(--line);font-weight:700">📣 Заявки на продвижение</div>
            <table class="vtable">
                <thead><tr><th>Клиент / Заведение</th><th>Услуга</th><th>Партнер</th><th>Бюджет</th><th>Статус</th><th>Управление</th></tr></thead>
                <tbody>
                <tr v-for="r in promotions" :key="r.id">
                    <td><b>{{ r.client?.name }}</b><br><small style="color:var(--mut)">{{ r.place?.name || 'Без заведения' }}</small></td>
                    <td>{{ r.title }}</td>
                    <td>{{ r.partner?.name || 'Не назначен' }}</td>
                    <td><b>{{ new Intl.NumberFormat('ru-RU').format(r.amount) }} ₽</b></td>
                    <td><VBadge v-bind="statusBadge[r.status]" /></td>
                    <td>
                        <select :value="r.status" class="inp" style="width:130px;padding:6px" @change="changeStatus('promotion', r.id, $event.target.value)">
                            <option value="new">Новая</option>
                            <option value="claimed">Взята</option>
                            <option value="in_progress">В работе</option>
                            <option value="completed">Завершена</option>
                            <option value="cancelled">Отменена</option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div v-if="activeType !== 'promotion'" class="vcard" style="padding:0;overflow:hidden">
            <div style="padding:14px 20px;background:var(--panel);border-bottom:1px solid var(--line);font-weight:700">💻 Заявки на разработку</div>
            <table class="vtable">
                <thead><tr><th>Клиент / Заведение</th><th>Услуга</th><th>Партнер</th><th>Бюджет</th><th>Статус</th><th>Управление</th></tr></thead>
                <tbody>
                <tr v-for="r in developments" :key="r.id">
                    <td><b>{{ r.client?.name }}</b><br><small style="color:var(--mut)">{{ r.place?.name || 'Без заведения' }}</small></td>
                    <td>{{ r.title }}</td>
                    <td>{{ r.techPartner?.name || 'Не назначен' }}</td>
                    <td><b>{{ new Intl.NumberFormat('ru-RU').format(r.amount) }} ₽</b></td>
                    <td><VBadge v-bind="statusBadge[r.status]" /></td>
                    <td>
                        <select :value="r.status" class="inp" style="width:130px;padding:6px" @change="changeStatus('development', r.id, $event.target.value)">
                            <option value="new">Новая</option>
                            <option value="claimed">Взята</option>
                            <option value="in_progress">В работе</option>
                            <option value="completed">Завершена</option>
                            <option value="cancelled">Отменена</option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
