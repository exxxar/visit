<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps(['partners'])
const search = ref('')
const expanded = ref({})

const toggle = (id) => {
    expanded.value[id] = !expanded.value[id]
}

const fmt = n => new Intl.NumberFormat('ru-RU').format(n) + ' ₽'
</script>

<template>
    <div>
        <div class="pg-head"><h1>🌐 Сетевая структура партнеров</h1></div>

        <div class="vcard" style="display:flex;gap:12px;margin-bottom:20px">
            <input v-model="search" @keyup.enter="router.get('/admin/partner/network', { search })" class="inp" placeholder="Поиск по имени или телефону..." style="max-width:400px">
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Партнер</th><th>Пригласил</th><th>Баланс</th><th>Уровень</th><th>Прямые рефералы</th><th>Действия</th></tr></thead>
                <tbody>
                <template v-for="p in partners.data" :key="p.id">
                    <tr>
                        <td>
                            <b>{{ p.name }}</b><br>
                            <small style="color:var(--mut)">{{ p.phone || p.email }}</small>
                        </td>
                        <td><small>{{ p.referrer?.name || '—' }}</small></td>
                        <td><b style="color:var(--cyan)">{{ fmt(p.partner_balance) }}</b></td>
                        <td><span style="font-size:12px;padding:4px 8px;background:var(--panel);border-radius:6px">Ур. {{ p.partner_level }}</span></td>
                        <td><b>{{ p.direct_referrals_count }}</b></td>
                        <td>
                            <button v-if="p.direct_referrals_count > 0" class="btn btn-ghost btn-sm" @click="toggle(p.id)">
                                {{ expanded[p.id] ? 'Свернуть' : 'Показать структуру' }}
                            </button>
                        </td>
                    </tr>
                    <!-- Раскрывающаяся строка с прямыми рефералами -->
                    <tr v-if="expanded[p.id]" style="background:rgba(34,211,238,.03)">
                        <td colspan="6" style="padding:0">
                            <div style="padding:16px 20px 16px 60px;border-left:3px solid var(--cyan);margin:10px 20px">
                                <h4 style="font-size:13px;color:var(--mut);margin:0 0 12px;text-transform:uppercase;letter-spacing:.1em">Прямые рефералы:</h4>
                                <!-- Здесь можно сделать рекурсивный компонент или просто вывести список -->
                                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px">
                                    <!-- Для полноценной вложенности нужен отдельный запрос или рекурсивный компонент.
                                         Здесь упрощенный вариант: показываем, что структура есть.
                                         В реальном проекте здесь делается отдельный API-запрос за детьми при раскрытии -->
                                    <div style="padding:10px;background:var(--panel);border-radius:8px;font-size:13px">
                                        Загрузка структуры для {{ p.name }}... <br>
                                        <small style="color:var(--mut)">(Всего прямых: {{ p.direct_referrals_count }})</small>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
