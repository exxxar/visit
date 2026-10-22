<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps(['transactions', 'filters'])
const search = ref(props.filters.search || '')
const type = ref(props.filters.type || '')

const apply = () => router.get('/admin/partner/transactions', { search: search.value, type: type.value }, { preserveScroll: true })

const typeLabel = {
    earning: { text: '💰 Заработок', color: 'var(--lime)' },
    mlm: { text: '🌐 Сетевой бонус', color: 'var(--cyan)' },
    withdrawal: { text: '💸 Вывод', color: 'var(--rose)' },
    bonus: { text: '🎁 Бонус', color: 'var(--yellow)' },
}
const fmt = n => (n > 0 ? '+' : '') + new Intl.NumberFormat('ru-RU').format(n) + ' ₽'
</script>

<template>
    <div>
        <div class="pg-head"><h1>📊 Транзакции партнеров</h1></div>

        <div class="vcard" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
            <input v-model="search" @keyup.enter="apply" class="inp" placeholder="Поиск по имени или email..." style="max-width:300px">
            <select v-model="type" @change="apply" class="inp" style="max-width:200px">
                <option value="">Все типы</option>
                <option value="earning">Заработок</option>
                <option value="mlm">Сетевой бонус</option>
                <option value="withdrawal">Вывод</option>
                <option value="bonus">Бонус</option>
            </select>
            <button class="btn btn-ghost" @click="apply">Применить</button>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Пользователь</th><th>Тип</th><th>Сумма</th><th>Описание</th><th>Связанный заказ</th><th>Дата</th></tr></thead>
                <tbody>
                <tr v-for="t in transactions.data" :key="t.id">
                    <td>
                        <b>{{ t.user?.name }}</b><br>
                        <small style="color:var(--mut)">{{ t.user?.email }}</small>
                    </td>
                    <!-- ИСПРАВЛЕНО: fontSize: '13px' -->
                    <td>
                            <span :style="{ color: typeLabel[t.type]?.color, fontWeight: 700, fontSize: '13px' }">
                                {{ typeLabel[t.type]?.text }}
                            </span>
                    </td>
                    <td>
                        <b :style="{ color: t.type === 'withdrawal' ? 'var(--rose)' : 'var(--lime)' }">
                            {{ fmt(t.amount) }}
                        </b>
                    </td>
                    <td style="font-size:13px;max-width:250px">{{ t.description }}</td>
                    <td><small style="color:var(--mut)">{{ t.request?.title || '—' }}</small></td>
                    <td style="font-size:12px;color:var(--mut);white-space:nowrap">{{ new Date(t.created_at).toLocaleString('ru-RU') }}</td>
                </tr>
                <tr v-if="!transactions.data?.length">
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--mut)">
                        Транзакций не найдено
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="pager">
            <a v-for="l in transactions.links" :key="l.label" :href="l.url || '#'"
               :class="{ on: l.active, off: !l.url }" v-html="l.label"
               @click.prevent="l.url && router.get(l.url, {}, { preserveScroll: true })">
            </a>
        </div>
    </div>
</template>
