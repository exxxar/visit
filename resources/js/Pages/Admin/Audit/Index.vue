<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'

const props = defineProps(['logs', 'admins', 'actions', 'filters'])

const search = ref(props.filters.search || '')
const admin_id = ref(props.filters.admin_id || '')
const action = ref(props.filters.action || '')

const applyFilters = () => {
    router.get('/admin/audit', {
        search: search.value,
        admin_id: admin_id.value,
        action: action.value
    }, { preserveScroll: true })
}

const formatJson = (val) => {
    if (!val) return '—'
    try {
        return JSON.stringify(val, null, 2)
    } catch {
        return String(val)
    }
}

const actionLabel = (key) => props.actions[key] || key

const modelName = (type) => {
    if (!type) return 'Неизвестно'
    return type.split('\\').pop()
}
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>🛡️ Журнал аудита</h1>
        </div>

        <!-- Фильтры -->
        <div class="vcard" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
            <input
                v-model="search"
                @keyup.enter="applyFilters"
                class="inp"
                placeholder="Поиск по модели, комментарию или админу..."
                style="max-width:320px"
            >
            <select v-model="admin_id" @change="applyFilters" class="inp" style="max-width:200px">
                <option value="">Все админы</option>
                <option v-for="admin in admins" :key="admin.id" :value="admin.id">
                    {{ admin.name }}
                </option>
            </select>
            <select v-model="action" @change="applyFilters" class="inp" style="max-width:240px">
                <option value="">Все действия</option>
                <option v-for="(label, key) in actions" :key="key" :value="key">
                    {{ label }}
                </option>
            </select>
            <VButton variant="ghost" @click="applyFilters">Применить</VButton>
        </div>

        <!-- Таблица -->
        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead>
                <tr>
                    <th style="width:140px">Дата и время</th>
                    <th style="width:180px">Администратор</th>
                    <th style="width:160px">Действие</th>
                    <th style="width:140px">Объект</th>
                    <th>Изменения</th>
                    <th style="width:200px">Комментарий</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="log in logs.data" :key="log.id">
                    <td style="white-space:nowrap;font-size:12px;color:var(--mut)">
                        {{ new Date(log.created_at).toLocaleString('ru-RU') }}
                    </td>
                    <td>
                        <b>{{ log.admin?.name || 'Система' }}</b>
                        <br><small style="color:var(--mut)">{{ log.admin?.email }}</small>
                    </td>
                    <td>
                            <span style="font-size:13px;font-weight:600;color:var(--cyan)">
                                {{ actionLabel(log.action) }}
                            </span>
                    </td>
                    <td style="font-size:13px">
                        <b>{{ modelName(log.auditable_type) }}</b>
                        <br><small style="color:var(--mut)">ID: {{ log.auditable_id }}</small>
                    </td>
                    <td>
                        <details style="font-size:12px;cursor:pointer">
                            <summary style="color:var(--cyan);font-weight:600;margin-bottom:6px;list-style:none">
                                ▶ Показать детали
                            </summary>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px">
                                <div>
                                    <div style="color:var(--rose);font-weight:700;margin-bottom:4px;font-size:11px;text-transform:uppercase">Было:</div>
                                    <pre style="margin:0;background:var(--bg2);padding:10px;border-radius:8px;overflow-x:auto;color:var(--mut);font-size:11px;border:1px solid var(--line)">{{ formatJson(log.old_values) }}</pre>
                                </div>
                                <div>
                                    <div style="color:var(--lime);font-weight:700;margin-bottom:4px;font-size:11px;text-transform:uppercase">Стало:</div>
                                    <pre style="margin:0;background:var(--bg2);padding:10px;border-radius:8px;overflow-x:auto;color:var(--txt);font-size:11px;border:1px solid var(--line)">{{ formatJson(log.new_values) }}</pre>
                                </div>
                            </div>
                        </details>
                    </td>
                    <td style="font-size:13px;color:var(--mut);line-height:1.4">
                        {{ log.comment || '—' }}
                    </td>
                </tr>
                <tr v-if="!logs.data.length">
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--mut)">
                        <div style="font-size:32px;margin-bottom:10px">📭</div>
                        Записей в журнале не найдено
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        <div class="pager" style="margin-top:20px">
            <Link
                v-for="l in logs.links"
                :key="l.label"
                :href="l.url ?? '#'"
                preserve-scroll
                :class="{ on: l.active, off: !l.url }"
                v-html="l.label"
            />
        </div>
    </div>
</template>

<style scoped>
/* Небольшой твик для details/summary, чтобы стрелка выглядела аккуратно */
details summary::-webkit-details-marker {
    display: none;
}
details[open] summary {
    color: var(--lime) !important;
}
details[open] summary::before {
    content: '▼ ';
}
details:not([open]) summary::before {
    content: '▶ ';
}
</style>
