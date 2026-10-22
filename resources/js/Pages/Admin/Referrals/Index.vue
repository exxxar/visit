<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'

const props = defineProps(['invites', 'counts'])

const f = ref({ q: '', role: '', status: '' })
const apply = () => router.get('/admin/referrals', { ...f.value }, { preserveScroll: true })

const roleBadge = {
    client:       { label: '🏢 Клиент',       color: 'cyan' },
    tech_partner: { label: '💻 Тех. партнёр', color: 'violet' },
    ad_partner:   { label: '📣 Рекламный',    color: 'magenta' },
}

const statusBadge = {
    pending:   { label: '⏳ Ожидает', color: 'yellow' },
    active:    { label: '✓ Активен',  color: 'lime' },
    completed: { label: '✓ Завершён', color: 'cyan' },
}

const fmtDate = (d) => new Date(d).toLocaleString('ru-RU', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
})

const fmtPhone = (p) => {
    if (!p) return '—'
    const d = p.replace(/\D/g, '')
    if (d.length === 11) return `+${d[0]} (${d.slice(1,4)}) ${d.slice(4,7)}-${d.slice(7,9)}-${d.slice(9)}`
    return p
}
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>🤝 Реферальная система</h1>
        </div>

        <!-- сводка -->
        <div class="stats-grid">
            <div class="stat-card">
                <b style="color:var(--cyan)">{{ counts.total }}</b>
                <span>всего приглашений</span>
            </div>
            <div class="stat-card">
                <b style="color:var(--yellow)">{{ counts.pending }}</b>
                <span>ожидают регистрации</span>
            </div>
            <div class="stat-card">
                <b style="color:var(--lime)">{{ counts.active }}</b>
                <span>активных</span>
            </div>
            <div class="stat-card">
                <b style="color:var(--magenta,#f050e0)">{{ counts.week }}</b>
                <span>за 7 дней</span>
            </div>
        </div>

        <div class="stats-grid" style="margin-top:12px">
            <div class="stat-card stat-card--role">
                <b>🏢 {{ counts.clients }}</b>
                <span>клиентов</span>
            </div>
            <div class="stat-card stat-card--role">
                <b>💻 {{ counts.tech }}</b>
                <span>тех. партнёров</span>
            </div>
            <div class="stat-card stat-card--role">
                <b>📣 {{ counts.ad }}</b>
                <span>рекламных</span>
            </div>
        </div>

        <!-- фильтры -->
        <div class="vcard" style="margin:18px 0;display:flex;gap:12px;flex-wrap:wrap">
            <input v-model="f.q" class="inp" style="max-width:300px" placeholder="Имя, email, телефон…" @keyup.enter="apply">
            <select v-model="f.role" class="inp" style="max-width:200px" @change="apply">
                <option value="">Все роли</option>
                <option value="client">Клиент</option>
                <option value="tech_partner">Тех. партнёр</option>
                <option value="ad_partner">Рекламный партнёр</option>
            </select>
            <select v-model="f.status" class="inp" style="max-width:200px" @change="apply">
                <option value="">Все статусы</option>
                <option value="pending">Ожидает</option>
                <option value="active">Активен</option>
                <option value="completed">Завершён</option>
            </select>
            <VButton variant="ghost" @click="apply">Применить</VButton>
        </div>

        <!-- таблица -->
        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead>
                <tr>
                    <th>Пригласил</th>
                    <th>Реферал</th>
                    <th>Роль</th>
                    <th>Заведение</th>
                    <th>Статус</th>
                    <th>Дата</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="inv in invites.data" :key="inv.id">
                    <!-- пригласивший -->
                    <td>
                        <div v-if="inv.referrer" class="user-cell">
                            <div class="user-cell__avatar">
                                {{ (inv.referrer.name || '?')[0].toUpperCase() }}
                            </div>
                            <div>
                                <b>{{ inv.referrer.name }}</b>
                                <small>{{ inv.referrer.email }}</small>
                                <small class="phone">{{ fmtPhone(inv.referrer_phone) }}</small>
                            </div>
                        </div>
                        <div v-else class="user-cell user-cell--pending">
                            <div class="user-cell__avatar user-cell__avatar--ghost">?</div>
                            <div>
                                <b>Не зарегистрирован</b>
                                <small class="phone">{{ fmtPhone(inv.referrer_phone) }}</small>
                            </div>
                        </div>
                    </td>

                    <!-- реферал -->
                    <td>
                        <div v-if="inv.referred" class="user-cell">
                            <div class="user-cell__avatar user-cell__avatar--ref">
                                {{ (inv.referred.name || '?')[0].toUpperCase() }}
                            </div>
                            <div>
                                <b>{{ inv.referred.name }}</b>
                                <small>{{ inv.referred.email }}</small>
                            </div>
                        </div>
                        <span v-else style="color:var(--mut)">—</span>
                    </td>

                    <!-- роль -->
                    <td>
                        <VBadge v-bind="roleBadge[inv.role] ?? { label: inv.role, color: 'cyan' }" />
                    </td>

                    <!-- заведение -->
                    <td>
                        <Link v-if="inv.place" :href="`/place/${inv.place.slug}`" class="place-link">
                            🏢 {{ inv.place.name }}
                        </Link>
                        <span v-else style="color:var(--mut)">—</span>
                    </td>

                    <!-- статус -->
                    <td>
                        <VBadge v-bind="statusBadge[inv.status] ?? { label: inv.status, color: 'cyan' }" />
                    </td>

                    <!-- дата -->
                    <td style="color:var(--mut);font-size:12px;white-space:nowrap">{{ fmtDate(inv.created_at) }}</td>
                </tr>
                <tr v-if="!invites.data.length">
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--mut)">
                        Приглашений пока нет
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- пагинация -->
        <div class="pager">
            <Link v-for="l in invites.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                  :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>
    </div>
</template>

<style scoped>
/* сводка */
.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
    gap:12px;
}
.stat-card{
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:14px;
    padding:16px 18px;
    display:flex;flex-direction:column;gap:4px;
}
.stat-card b{
    font-family:var(--disp);
    font-size:26px;font-weight:800;
}
.stat-card span{
    color:var(--mut);font-size:12.5px;
}
.stat-card--role b{font-size:20px}

/* ячейка пользователя */
.user-cell{
    display:flex;align-items:center;gap:10px;
}
.user-cell__avatar{
    width:36px;height:36px;border-radius:50%;
    display:grid;place-items:center;flex-shrink:0;
    background:linear-gradient(135deg,var(--cyan),var(--violet));
    color:#fff;font-weight:800;font-size:14px;
}
.user-cell__avatar--ref{
    background:linear-gradient(135deg,var(--lime),var(--cyan));
    color:#0b101d;
}
.user-cell__avatar--ghost{
    background:var(--panel);
    border:1px dashed var(--line);
    color:var(--mut);
}
.user-cell--pending{opacity:.7}
.user-cell b{display:block;font-size:13px}
.user-cell small{display:block;color:var(--mut);font-size:11px;line-height:1.4}
.user-cell .phone{color:var(--cyan,#22d3ee);font-weight:600}

.place-link{
    color:var(--cyan);font-size:13px;font-weight:600;
    text-decoration:none;transition:.2s;
}
.place-link:hover{text-decoration:underline}
</style>
